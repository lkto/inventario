<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 11:44 AM
 */

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="product")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="stock", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $stock;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string")
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var integer
     *
     * @ORM\Column(name="value", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SaleDetails", mappedBy="product", cascade={"persist"}, orphanRemoval=true)
     */
    private $saleDetails;

    public function __construct()
    {
        $this->saleDetails = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock(int $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value)
    {
        $this->value = $value;
    }

    public function getSaleDetails()
    {
        return $this->saleDetails;
    }

    public function addSaleDetails(SaleDetails $saleDetails = null)
    {
        if (!$this->saleDetails->contains($saleDetails)) {
            $this->saleDetails[] = $saleDetails;
            $saleDetails->setProduct($this);
        }

        return $this;
    }

    public function removeSaleDetails(SaleDetails $saleDetails = null)
    {
        if ($this->saleDetails->contains($saleDetails)) {
            $this->saleDetails->removeElement($saleDetails);
            // set the owning side to null (unless already changed)
            if ($saleDetails->getProduct() === $this) {
                $saleDetails->setProduct(null);
            }
        }

        return $this;
    }

}