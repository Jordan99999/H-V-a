<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message:"L'email ne peut pas être vide")]
    #[Assert\Email(message:"Veuillez entrer un email valide")]
    private ?string $email;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message:"Le nom d'utilisateur ne peut pas être vide")]
    
    private ?string $username;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Le mot de passe ne peut pas être vide")]
    #[Assert\Length(min:8, minMessage:"Votre mot de passe doit faire minimum 8 caractères")]
    #[Assert\EqualTo(propertyPath:"confirm_password", message:"Les mots de passe ne correspondent pas")]
    private ?string $password = null;

    #[Assert\NotBlank(message:"La confirmation du mot de passe ne peut pas être vide")]
    public ?string $confirm_password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        $this->confirm_password = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
