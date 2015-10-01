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
class InvoiceItem extends BaseItem {

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="items")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     * */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price; // price

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_billed", type="integer")
     */
    private $quantityBilled; // q_itd

    public function getInvoice() {
        return $this->invoice;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
        return $this;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getQuantityBilled() {
        return $this->quantityBilled;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    public function setQuantityBilled($quantityBilled) {
        $this->quantityBilled = $quantityBilled;
        return $this;
    }

}
