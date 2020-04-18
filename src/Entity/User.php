<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="An account with this email already exist, please login")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="The {{value}} should not be blank")
     *  @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Your {{ value }} must be at least {{ limit }} characters long",
     *      maxMessage = "Your {{ value }} cannot be longer than {{ limit }} characters"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="The {{ value }} should not be blank")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Your {{ value }} must be at least {{ limit }} characters long",
     *      maxMessage = "Your {{ value }} cannot be longer than {{ limit }} characters"
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=320, unique=true)
     * 
     * @Assert\NotBlank(
     *      message="The {{ value }} should not be blank")
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.")
     * @Assert\Length(
     *      min = 3,
     *      max = 320,
     *      minMessage = "Your {{ value }} must be at least {{ limit }} characters long",
     *      maxMessage = "Your {{ value }} cannot be longer than {{ limit }} characters")
     */
    private $email;
    
    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $role;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    // IMPLEMENTATION //
    public function getRoles()
    {
        return array($this->role);
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        $username = $this->firstname . " " . $this->getLastname();
        return $username;
    }

    public function eraseCredentials()
    {
    }

    public function getPlainPassword(): ?string
    {
        return $this->plain_password;
    }

    public function setPlainPassword(string $plain_password): self
    {
        $this->plain_password = $plain_password;

        return $this;
    }
}
