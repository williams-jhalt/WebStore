<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PackageItem
 *
 * @ORM\Table(name="package_item")
 * @ORM\Entity
 */
class PackageItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Package")
     * @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     * */
    private $package;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    private $sku;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function getPackage() {
        return $this->package;
    }

    public function getSku() {
        return $this->sku;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setPackage($package) {
        $this->package = $package;
        return $this;
    }

    public function setSku($sku) {
        $this->sku = $sku;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

}
