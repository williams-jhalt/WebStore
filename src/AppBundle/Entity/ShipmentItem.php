<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @ORM\Entity()
 */
class ShipmentItem extends BaseItem {

    /**
     * @ORM\ManyToOne(targetEntity="Shipment", inversedBy="items")
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id")
     * */
    private $shipment;

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_shipped", type="integer")
     */
    private $quantityShipped; // q_comm

    public function getShipment() {
        return $this->shipment;
    }

    public function setShipment($shipment) {
        $this->shipment = $shipment;
        return $this;
    }

    public function getQuantityShipped() {
        return $this->quantityShipped;
    }

    public function setQuantityShipped($quantityShipped) {
        $this->quantityShipped = $quantityShipped;
        return $this;
    }

}
