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
class Order extends BaseOrder {

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order")
     */
    private $items;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="order")
     */
    private $invoices;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Shipment", mappedBy="order")
     */
    private $shipments;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Package", mappedBy="order")
     */
    private $packages;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Credit", mappedBy="order")
     */
    private $credits;

    public function __construct() {
        $this->items = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->packages = new ArrayCollection();
        $this->credits = new ArrayCollection();
    }

    public function getCredits() {
        return $this->credits;
    }

    public function setCredits(ArrayCollection $credits) {
        $this->credits = $credits;
        return $this;
    }

    public function getItems() {
        return $this->items;
    }

    public function getInvoices() {
        return $this->invoices;
    }

    public function getShipments() {
        return $this->shipments;
    }

    public function getPackages() {
        return $this->packages;
    }

    public function setItems(ArrayCollection $items) {
        $this->items = $items;
        return $this;
    }

    public function setInvoices(ArrayCollection $invoices) {
        $this->invoices = $invoices;
        return $this;
    }

    public function setShipments(ArrayCollection $shipments) {
        $this->shipments = $shipments;
        return $this;
    }

    public function setPackages(ArrayCollection $packages) {
        $this->packages = $packages;
        return $this;
    }

}
