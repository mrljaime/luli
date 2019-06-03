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
class Movement implements \JsonSerializable
{
    const DELAYED_PAYMENT = 1;

    const TYPES = [
        'delayedPayment'    => self::DELAYED_PAYMENT,
    ];

    const REVERSE_TYPES = [
        self::DELAYED_PAYMENT   => 'delayedPayment',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", columnDefinition="TINYINT(1)", nullable=false)
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
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=500)
     */
    private $info;

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
        $this->info = "{}";
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

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param $info
     * @return Movement
     */
    public function setInfo($info): self
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id'            => $this->id,
            'parentClass'   => $this->parentClass,
            'parentId'      => $this->parentId,
            'type'          => $this->type,
            'info'          => $this->info
        ];
    }
}
