<?php

namespace App\Notification;

use App\Entity\Contact;
use Twig\Environment;

class ContactNotification
{
    private Environment $renderer;

    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function notify(Contact $contact)
    {
        // Rendu du contenu HTML du mail à partir du template Twig
        $htmlContent = $this->renderer->render('emails/contact.html.twig', ['contact' => $contact]);

        // Échappement des arguments pour la sécurité de la commande shell
        $from = escapeshellarg($contact->getEmail());
        $to = escapeshellarg('jordan1511@outlook.fr');
        $subject = escapeshellarg('Message : ' . $contact->getMessage());
        $htmlContentEscaped = escapeshellarg($htmlContent);

        // Préparation de la commande pour mailpit.exe
        $command = ".\\mailpit.exe send-email --from={$from} --to={$to} --subject={$subject} --html-content={$htmlContentEscaped}";

        // Exécution de la commande
        exec($command, $output, $returnVar);

        // Journalisation des résultats
        if ($returnVar !== 0) {
            $errorMessage = 'Erreur lors de l\'envoi de l\'email: ' . implode("\n", $output);
            error_log($errorMessage);
            throw new \Exception($errorMessage);
        }
    }
}
