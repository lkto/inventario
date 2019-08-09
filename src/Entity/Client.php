<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 11:41 AM
 */

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="client")
 */
class Client
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
     * @ORM\Column(name="first_name", type="string")
     *
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string")
     *
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $identify;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sales", mappedBy="client", cascade={"persist"}, orphanRemoval=true)
     */
    private $sale;

    public function __construct()
    {
        $this->sale = new ArrayCollection();
        $this->enabled = true;
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getIdentify()
    {
        return $this->identify;
    }

    /**
     * @param string $identify
     */
    public function setIdentify(string $identify)
    {
        $this->identify = $identify;
    }

    public function getSale()
    {
        return $this->sale;
    }

    public function addSale(Sales $sale = null)
    {
        if (!$this->sale->contains($sale)) {
            $this->sale[] = $sale;
            $sale->setUser($this);
        }
        return $this;
    }

    public function removeSale(Sales $sale = null)
    {
        if ($this->sale->contains($sale)) {
            $this->sale->removeElement($sale);
            // set the owning side to null (unless already changed)
            if ($sale->getUser() === $this) {
                $sale->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

}