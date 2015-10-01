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
class Invoice extends BaseOrder {

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="invoices")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * */
    private $order;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="InvoiceItem", mappedBy="invoice")
     */
    private $items;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=255, nullable=true)
     */
    private $invoiceNumber; // invoice

    /**
     * @var string
     *
     * @ORM\Column(name="freight_charge", type="decimal", nullable=true)
     */
    private $freightCharge;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_and_handling_charge", type="decimal", nullable=true)
     */
    private $shippingAndHandlingCharge;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_gross_amount", type="decimal", nullable=true)
     */
    private $invoiceGrossAmount; // c_tot_gross

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_net_amount", type="decimal", nullable=true)
     */
    private $invoiceNetAmount; // c_tot_net_ar

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_date", type="date", nullable=true)
     */
    private $invoiceDate; // invc_date

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

    public function getInvoiceDate() {
        return $this->invoiceDate;
    }

    public function setInvoiceDate($invoiceDate) {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    public function getFreightCharge() {
        return $this->freightCharge;
    }

    public function getShippingAndHandlingCharge() {
        return $this->shippingAndHandlingCharge;
    }

    public function getInvoiceGrossAmount() {
        return $this->invoiceGrossAmount;
    }

    public function getInvoiceNetAmount() {
        return $this->invoiceNetAmount;
    }

    public function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function setFreightCharge($freightCharge) {
        $this->freightCharge = $freightCharge;
        return $this;
    }

    public function setShippingAndHandlingCharge($shippingAndHandlingCharge) {
        $this->shippingAndHandlingCharge = $shippingAndHandlingCharge;
        return $this;
    }

    public function setInvoiceGrossAmount($invoiceGrossAmount) {
        $this->invoiceGrossAmount = $invoiceGrossAmount;
        return $this;
    }

    public function setInvoiceNetAmount($invoiceNetAmount) {
        $this->invoiceNetAmount = $invoiceNetAmount;
        return $this;
    }

}
