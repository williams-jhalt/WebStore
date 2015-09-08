<?php

namespace AppBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class InvoiceItem extends OrderItem {

    private $price;
    private $quantityBilled;

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
