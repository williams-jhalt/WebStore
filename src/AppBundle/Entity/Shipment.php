<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @ORM\Entity()
 */
class Shipment extends BaseOrder {

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="shipments")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * */
    private $order;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="ShipmentItem", mappedBy="shipment")
     */
    private $items;

    /**
     * @var string
     *
     * @ORM\Column(name="manifest_id", type="string", length=255, nullable=true)
     */
    private $manifestId; // Manifest_id

    /**
     * @var string
     *
     * @ORM\Column(name="ship_date", type="date", nullable=true)
     */
    private $shipDate; // ship_date

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    public function getOrder() {
        return $this->order;
    }

    public function getItems() {
        return $this->items;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function setItems(ArrayCollection $items) {
        $this->items = $items;
        return $this;
    }

    public function getManifestId() {
        return $this->manifestId;
    }

    public function getShipDate() {
        return $this->shipDate;
    }

    public function setManifestId($manifestId) {
        $this->manifestId = $manifestId;
        return $this;
    }

    public function setShipDate($shipDate) {
        $this->shipDate = $shipDate;
        return $this;
    }

}
