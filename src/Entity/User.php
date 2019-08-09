<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 11:39 AM
 */

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sales", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
     */
    private $sale;

    public function __construct()
    {
        parent::__construct();
        $this->username = $this->getEmail();
        $this->usernameCanonical = $this->getEmail();
        $this->emailCanonical = $this->getEmail();
        $this->enabled = true;
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->addRole("ROLE_ADMINISTRATOR");
        $this->sale = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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

}