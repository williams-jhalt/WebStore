<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Category
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Table(name="customer")
 * @ORM\Entity
 */
class Customer {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var string
     * 
     * @ORM\Column(name="customer_number", type="string", length=255, unique=true)
     */
    private $customerNumber;

    public function getId() {
        return $this->id;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

}
