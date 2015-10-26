<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="invoice_item")
 * @ORM\Entity()
 */
class InvoiceItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="items")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     * */
    private $invoice;

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

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_billed", type="integer")
     */
    protected $quantityBilled; // q_ord

    public function getId() {
        return $this->id;
    }

    public function getInvoice() {
        return $this->invoice;
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

    public function getQuantityBilled() {
        return $this->quantityBilled;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
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

    public function setQuantityBilled($quantityBilled) {
        $this->quantityBilled = $quantityBilled;
        return $this;
    }
    public function getQuantityOrdered() {
        return $this->quantityOrdered;
    }

    public function getQuantityShipped() {
        return $this->quantityShipped;
    }

    public function setQuantityOrdered($quantityOrdered) {
        $this->quantityOrdered = $quantityOrdered;
        return $this;
    }

    public function setQuantityShipped($quantityShipped) {
        $this->quantityShipped = $quantityShipped;
        return $this;
    }



}
