<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipmentItem
 *
 * @ORM\Table(name="shipment_item")
 * @ORM\Entity
 */
class ShipmentItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shipment")
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id")
     * */
    private $shipment;

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

    public function getShipment() {
        return $this->shipment;
    }

    public function getSku() {
        return $this->sku;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setShipment($shipment) {
        $this->shipment = $shipment;
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
