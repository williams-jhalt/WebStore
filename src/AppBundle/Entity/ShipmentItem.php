<?php

namespace AppBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ShipmentItem extends OrderItem {

    private $quantityShipped;

    public function getQuantityShipped() {
        return $this->quantityShipped;
    }

    public function setQuantityShipped($quantityShipped) {
        $this->quantityShipped = $quantityShipped;
        return $this;
    }

}
