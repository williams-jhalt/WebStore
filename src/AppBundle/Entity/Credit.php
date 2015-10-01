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
class Credit extends BaseOrder {

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="credits")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * */
    private $order;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="CreditItem", mappedBy="credit")
     */
    private $items;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_date", type="date", nullable=true)
     */
    private $creditDate; // invc_date

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    public function getOrder() {
        return $this->order;
    }

    public function getItems() {
        return $this->items;
    }

    public function getCreditDate() {
        return $this->creditDate;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function setItems(ArrayCollection $items) {
        $this->items = $items;
        return $this;
    }

    public function setCreditDate($creditDate) {
        $this->creditDate = $creditDate;
        return $this;
    }

}
