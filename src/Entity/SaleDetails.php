<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 12:10 PM
 */

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="salesDetails")
 */
class SaleDetails
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales", inversedBy="sale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var integer
     *
     * @ORM\Column(name="value_unit", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $valueUnit;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $count;

    /**
     * @var integer
     *
     * @ORM\Column(name="value_total", type="integer")
     *
     * @Assert\NotBlank()
     */
    private $valueTotal;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * @param mixed $sale
     */
    public function setSale($sale)
    {
        $this->sale = $sale;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getValueUnit(): int
    {
        return $this->valueUnit;
    }

    /**
     * @param int $valueUnit
     */
    public function setValueUnit(int $valueUnit)
    {
        $this->valueUnit = $valueUnit;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getValueTotal(): int
    {
        return $this->valueTotal;
    }

    /**
     * @param int $valueTotal
     */
    public function setValueTotal(int $valueTotal)
    {
        $this->valueTotal = $valueTotal;
    }



}