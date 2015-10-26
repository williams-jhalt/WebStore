<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="shipment_item")
 * @ORM\Entity()
 */
class ShipmentItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shipment", inversedBy="items")
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id")
     * */
    private $shipment;

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

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_shipped", type="integer")
     */
    protected $quantityShipped; // q_ord

    public function getId() {
        return $this->id;
    }

    public function getShipment() {
        return $this->shipment;
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

    public function getQuantityShipped() {
        return $this->quantityShipped;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setShipment($shipment) {
        $this->shipment = $shipment;
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

    public function setQuantityShipped($quantityShipped) {
        $this->quantityShipped = $quantityShipped;
        return $this;
    }

    public function getQuantityOrdered() {
        return $this->quantityOrdered;
    }

    public function setQuantityOrdered($quantityOrdered) {
        $this->quantityOrdered = $quantityOrdered;
        return $this;
    }

}
