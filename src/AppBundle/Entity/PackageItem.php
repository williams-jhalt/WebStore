<?php

namespace AppBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PackageItem {

    private $itemNumber;
    private $quantity;

    public function getItemNumber() {
        return $this->itemNumber;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setItemNumber($itemNumber) {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

}
