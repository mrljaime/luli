<?php

namespace App\Entity;

use App\Util\DateTimeUtil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @ORM\Table(name="aa_products")
 */
class Product implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var $name
     *
     * @ORM\Column(name="name", type="string", length=75)
     * @Assert\NotBlank(message="El nombre es obligatorio")
     */
    private $name;

    /**
     * @var $description
     *
     * @ORM\Column(name="description", type="string", length=500)
     * @Assert\NotBlank(message="La descripción es obligatoria")
     */
    private $description;

    /**
     * @var $price float
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     * @Assert\Currency(message="El precio no es válido")
     */
    private $price;

    /**
     * @var $qty integer
     *
     * @ORM\Column(name="qty", type="smallint")
     * @Assert\NotBlank(message="La cantidad es obligatoria")
     */
    private $qty;

    /**
     * @var $category Category
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     */
    private $category;

    /**
     * @var $subCategory SubCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory")
     */
    private $subCategory;

    /**
     * @var $provider Provider
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Provider")
     */
    private $provider;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var $active bool
     *
     * @ORM\Column(name="active", type="boolean", options={"default": FALSE})
     * @Assert\NotBlank(message="Saber si el producto está activo o no es obligatorio")
     */
    private $active;

    public function __construct()
    {
        $this->createdAt = DateTimeUtil::getDateTime();
        $this->updatedAt = DateTimeUtil::getDateTime();
        $this->active = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQty(): ?int
    {
        return $this->qty;
    }

    public function setQty(int $qty): self
    {
        $this->qty = $qty;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function setProvider(Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
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

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
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
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'price'         => $this->price,
            'qty'           => $this->qty,
            'category'      => [
                'id'    => $this->category->getId(),
                'name'  => $this->category->getName(),
            ],
            'subCategory'   => is_null($this->subCategory) ?: [
                'id'        => $this->subCategory->getId(),
                'name'      => $this->subCategory->getName(),
                'category'  => [
                    'id'    => $this->subCategory->getCategory()->getId(),
                    'name'  => $this->subCategory->getCategory()->getName(),
                ],
            ],
            'provider'      => [
                'id'    => $this->provider->getId(),
                'name'  => $this->provider->getName(),
            ],
            'createdAt'     => DateTimeUtil::formatForJsonResponse($this->createdAt),
            'active'        => $this->active,
        ];
    }
}
