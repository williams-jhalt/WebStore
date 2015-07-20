<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvoiceItem
 *
 * @ORM\Table(name="invoice_item")
 * @ORM\Entity
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
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     * */
    private $invoice;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    private $sku;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function getInvoice() {
        return $this->invoice;
    }

    public function getSku() {
        return $this->sku;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
        return $this;
    }

    public function setSku($sku) {
        $this->sku = $sku;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

}
