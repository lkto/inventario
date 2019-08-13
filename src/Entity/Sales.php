<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 12:02 PM
 */

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SaleRepository")
 * @ORM\Table(name="sales")
 */
class Sales
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="sale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SaleDetails", mappedBy="sale", cascade={"persist"}, orphanRemoval=true)
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
    public function getCode()
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getSaleDetails()
    {
        return $this->saleDetails;
    }

    public function addSaleDetails(SaleDetails $saleDetails = null)
    {
        if (!$this->saleDetails->contains($saleDetails)) {
            $this->saleDetails[] = $saleDetails;
            $saleDetails->setSale($this);
        }

        return $this;
    }

    public function removeSaleDetails(SaleDetails $saleDetails = null)
    {
        if ($this->saleDetails->contains($saleDetails)) {
            $this->saleDetails->removeElement($saleDetails);
            // set the owning side to null (unless already changed)
            if ($saleDetails->getSale() === $this) {
                $saleDetails->setSale(null);
            }
        }

        return $this;
    }




}