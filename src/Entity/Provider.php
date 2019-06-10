<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Provider
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProviderRepository")
 * @ORM\Table(name="aa_providers")
 */
class Provider
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=125)
     * @Assert\NotBlank(message="El nombre es obligatorio")
     */
    private $name;

    /**
     * @ORM\Column(name="status", columnDefinition="SMALLINT(1)")
     */
    private $status;

    /**
     * @ORM\Column(name="email", type="string", length=125, unique=true)
     * @Assert\NotBlank(message="El email es obligatorio")
     */
    private $email;

    /**
     * @ORM\Column(name="phone_number", type="string", length=18)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(name="unique_identifier", type="string", length=4, unique=true)
     * @Assert\NotBlank(message="El identificador interno es obligatorio")
     * @Assert\Length(
     *     max="4", min="4",
     *     maxMessage="La longitud del identificador es de 4 caractéres",
     *     minMessage="La longitud del identificador es de 4 caractéres")
     */
    private $uniqueIdentifier;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->status = 1;
        $this->createdAt = new \DateTime("now", new \DateTimeZone("America/Mexico_City"));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getUniqueIdentifier(): ?string
    {
        return $this->uniqueIdentifier;
    }

    public function setUniqueIdentifier($uniqueIdentifier): self
    {
        $this->uniqueIdentifier = $uniqueIdentifier;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
