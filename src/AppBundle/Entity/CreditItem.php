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
class CreditItem extends BaseItem {

    /**
     * @ORM\ManyToOne(targetEntity="Credit", inversedBy="items")
     * @ORM\JoinColumn(name="credit_id", referencedColumnName="id")
     * */
    private $credit;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price; // price

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_credited", type="integer")
     */
    private $quantityCredited; // q_comm

    public function getCredit() {
        return $this->credit;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getQuantityCredited() {
        return $this->quantityCredited;
    }

    public function setCredit($credit) {
        $this->credit = $credit;
        return $this;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    public function setQuantityCredited($quantityCredited) {
        $this->quantityCredited = $quantityCredited;
        return $this;
    }

}
