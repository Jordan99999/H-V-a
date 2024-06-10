<?php


namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\ContactType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Notification\ContactNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }

    #[Route('/blog', name: 'blog_index')]
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->createQueryBuilder('a')
            ->orderBy('a.updated_at', 'DESC')
            ->getQuery()
            ->getResult();
    
        return $this->render('blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route("/blog/new", name: "blog_create")]
    #[Route("/blog/{id}/edit", name: "blog_edit")]
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$article) {
            $article = new Article();
        }
    
        $form = $this->createForm(ArticleType::class, $article);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }
            $file = $form->get('imageFile')->getData();
            $filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/images/produits', $filename);
            
            $article->setImage($filename);
            $manager->persist($article);
            $manager->flush();
    
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }
    
        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }
    
    #[Route('/blog/{id}', name: 'blog_show')]
    public function show(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article);
    
            $manager->persist($comment);
            $manager->flush();
    
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }
    
        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }
    
    #[Route('/blog/contact', name: 'blog_contact')]
    public function contact(Request $request, EntityManagerInterface $manager, ContactNotification $notification)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $notification->notify($contact);
            $this->addFlash('success', 'Votre message a bien été envoyé!');
            $manager->persist($contact);
            $manager->flush();
    
            return $this->redirectToRoute('blog_contact');
        }
    
        return $this->render('blog/contact.html.twig', [
            'formContact' => $form->createView(),
        ]);
    }
}
