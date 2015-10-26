<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="sales_order_item")
 * @ORM\Entity()
 */
class SalesOrderItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SalesOrder", inversedBy="items")
     * @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id")
     * */
    private $salesOrder;

    /**
     * @var integer
     *
     * @ORM\Column(name="line_number", type="integer")
     */
    protected $lineNumber; // q_ord

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
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price; // price

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_ordered", type="integer")
     */
    protected $quantityOrdered; // q_ord

    public function getId() {
        return $this->id;
    }

    public function getSalesOrder() {
        return $this->salesOrder;
    }

    public function getLineNumber() {
        return $this->lineNumber;
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

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setSalesOrder($salesOrder) {
        $this->salesOrder = $salesOrder;
        return $this;
    }

    public function setLineNumber($lineNumber) {
        $this->lineNumber = $lineNumber;
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

}
