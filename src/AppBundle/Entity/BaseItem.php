<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @ORM\Table(name="erp_order_line")
 * @ORM\Entity() 
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="recordType", type="string")
 * @ORM\DiscriminatorMap({"O" = "OrderItem", "I" = "InvoiceItem", "S" = "ShipmentItem", "C" = "CreditItem"})
 * @ORM\HasLifecycleCallbacks()
 */
abstract class BaseItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=255)
     */
    protected $orderNumber; // order

    /**
     * @var string
     *
     * @ORM\Column(name="record_sequence", type="string", length=255)
     */
    protected $recordSequence; // rec_seq

    /**
     * @var string
     *
     * @ORM\Column(name="line_number", type="integer")
     */
    protected $lineNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="item_number", type="string", length=255)
     */
    protected $itemNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="ordered_quantity", type="integer")
     */
    protected $orderedQuantity;

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getLineNumber() {
        return $this->lineNumber;
    }

    public function getItemNumber() {
        return $this->itemNumber;
    }

    public function getOrderedQuantity() {
        return $this->orderedQuantity;
    }

    public function setLineNumber($lineNumber) {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    public function setItemNumber($itemNumber) {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setOrderedQuantity($orderedQuantity) {
        $this->orderedQuantity = $orderedQuantity;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

}
