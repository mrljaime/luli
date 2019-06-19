<?php

namespace App\Entity;

use App\Util\DateTimeUtil;
use App\Util\StatusUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="aa_orders")
 */
class Order implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="decimal", precision=10, scale=2, options={"default": 0.00})
     */
    private $total;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="decimal", precision=10, scale=2, options={"default": 0.00})
     */
    private $discount;

    /**
     * @var float
     *
     * @ORM\Column(name="interest", type="decimal", precision=10, scale=2, options={"default": 0.00})
     */
    private $interest;

    /**
     * @var int
     *
     * @ORM\Column(name="status", columnDefinition="TINYINT")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OrderElement", mappedBy="order")
     */
    private $elements;

    /**
     * Order constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->status = StatusUtil::PENDING;
        $this->total = 0;
        $this->elements = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return Order
     */
    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     * @return Order
     */
    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getInterest(): float
    {
        return $this->interest;
    }

    /**
     * @param float $interest
     * @return Order
     */
    public function setInterest(float $interest): self
    {
        $this->interest = $interest;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Order
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

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
     * @param \DateTime $createdAt
     * @return Order
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return Order
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param ArrayCollection $elements
     * @return Order
     */
    public function setElements(ArrayCollection $elements): self
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * @param OrderElement $element
     * @return Order
     */
    public function addElement(OrderElement $element): self
    {
        $element->setOrder($this);
        $this->elements->add($element);
        $this->total += $element->getAmount();

        return $this;
    }

    /**
     * @param $status
     * @return Order
     */
    public function addStatus($status): self
    {
        if (0 == ($this->status & $status)) {
            $this->status += $status;
        }

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
        $data = [
            'id'        => $this->id,
            'total'     => $this->total,
            'discount'  => $this->discount,
            'interest'  => $this->interest,
            'pending'   => StatusUtil::isPending($this->status),
            'paid'      => StatusUtil::isPaid($this->status),
            'sent'      => StatusUtil::isSent($this->status),
            'createdAt' => DateTimeUtil::formatForJsonResponse($this->createdAt),
            'updatedAt' => DateTimeUtil::formatForJsonResponse($this->updatedAt),
            'elements'  => $this->elements->count(),
        ];

        return $data;
    }
}
    