<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Balance
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\BalanceRepository")
 * @ORM\Table(name="aa_balances", indexes={@ORM\Index(name="search_idx", columns={"parent_class", "parent_id"})})
 */
class Balance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(name="parent_class", type="string", length=125)
     */
    private $parentClass;

    /**
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Balance constructor.
     * @throws \Exceptionç
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     * @return Balance
     */
    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentClass(): ?string
    {
        return $this->parentClass;
    }

    /**
     * @param string $parentClass
     * @return Balance
     */
    public function setParentClass(string $parentClass): self
    {
        $this->parentClass = $parentClass;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     * @return Balance
     */
    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     * @return Balance
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface $updatedAt
     * @return Balance
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
