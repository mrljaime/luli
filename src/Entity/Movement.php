<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Movement
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\MovementRepository")
 * @ORM\Table(name="aa_movements", indexes={@ORM\Index(name="search_idx", columns={"parent_class", "parent_id"})})
 */
class Movement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MovementType")
     * @ORM\JoinColumn(name="movement_type_id", referencedColumnName="id")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="parent_class", type="string", length=125)
     */
    private $parentClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Movement constructor.
     * @throws \Exception
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
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Movement
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param $parentClass
     * @return Movement
     */
    public function setParentClass($parentClass): self
    {
        $this->parentClass = $parentClass;

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
     * @param $parentId
     * @return Movement
     */
    public function setParentId($parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }
}
