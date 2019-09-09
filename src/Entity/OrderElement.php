<?php

namespace App\Entity;

use App\Util\DateTimeUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OrderElement
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\OrderElementRepository")
 * @ORM\Table(name="aa_order_elements")
 */
class OrderElement implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="elements")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, options={"default": 0.00})
     */
    private $amount;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", columnDefinition="TINYINT", options={"default": 0})
     */
    private $qty;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=75)
     */
    private $label;

    /**
     * @ORM\Column(name="parent_class", type="string", length=125)
     */
    private $parentClass;

    /**
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
     * @param Order $order
     * @return OrderElement
     */
    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $amount
     * @return OrderElement
     */
    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param $qty
     * @return OrderElement
     */
    public function setQty($qty): self
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * @return int
     */
    public function getQty(): ?int
    {
        return $this->qty;
    }

    /**
     * @param $label
     * @return OrderElement
     */
    public function setLabel($label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
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
     * @return OrderElement
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
     * @return OrderElement
     */
    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param $qty
     * @return OrderElement
     */
    public function addQty($qty): self
    {
        $this->qty += $qty;

        return $this;
    }

    /**
     * @param $amount
     * @return OrderElement
     */
    public function addAmount($amount): self
    {
        $this->amount += $amount;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id'    => $this->id,
            'label' => $this->label,
            'amount'    => $this->amount,
            'order'     => [
                'id'    => $this->order->getId(),
            ],
            'createdAt' => DateTimeUtil::formatForJsonResponse($this->createdAt),
        ];
    }
}
