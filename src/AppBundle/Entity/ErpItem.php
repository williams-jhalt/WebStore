<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="erp_item")
 * @ORM\Entity()
 */
class ErpItem {

    /**
     * @ORM\Id @ORM\Column(name="order_number", type="string")
     */
    private $orderNumber;

    /**
     * @ORM\Id @ORM\Column(name="record_sequence", type="integer")
     */
    private $recordSequence;

    /**
     * @ORM\Id @ORM\Column(name="line_number", type="integer")
     */
    private $lineNumber;

    /**
     * @ORM\Id @ORM\Column(name="record_type", type="string")
     */
    private $recordType;

    /**
     * @var string
     *
     * @ORM\Column(name="item_number", type="string", length=255)
     */
    protected $itemNumber; // item

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name; // descr

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price; // price

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_ordered", type="integer")
     */
    protected $quantityOrdered; // q_ord

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_billed", type="integer")
     */
    private $quantityBilled; // q_itd

    /**
     * @var string
     *
     * @ORM\Column(name="quantity_shipped", type="integer")
     */
    private $quantityShipped; // q_comm

    public function __construct($orderNumber, $recordSequence, $lineNumber, $recordType) {
        $this->orderNumber = $orderNumber;
        $this->recordSequence = $recordSequence;
        $this->lineNumber = $lineNumber;
        $this->recordType = $recordType;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getLineNumber() {
        return $this->lineNumber;
    }

    public function getRecordType() {
        return $this->recordType;
    }

    public function getItemNumber() {
        return $this->itemNumber;
    }

    public function getName() {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getQuantityOrdered() {
        return $this->quantityOrdered;
    }

    public function getQuantityBilled() {
        return $this->quantityBilled;
    }

    public function getQuantityShipped() {
        return $this->quantityShipped;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setLineNumber($lineNumber) {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    public function setRecordType($recordType) {
        $this->recordType = $recordType;
        return $this;
    }

    public function setItemNumber($itemNumber) {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    public function setQuantityOrdered($quantityOrdered) {
        $this->quantityOrdered = $quantityOrdered;
        return $this;
    }

    public function setQuantityBilled($quantityBilled) {
        $this->quantityBilled = $quantityBilled;
        return $this;
    }

    public function setQuantityShipped($quantityShipped) {
        $this->quantityShipped = $quantityShipped;
        return $this;
    }

}
