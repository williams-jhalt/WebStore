<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Weborder
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(name="invoice_date", type="datetime", nullable=true)
     */
    private $invoiceDate;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_number", type="string", length=255)
     */
    private $orderNumber;

    /**
     * @ORM\Column(name="customer_number", type="string", length=255)
     * */
    private $customerNumber;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceItem", mappedBy="invoice")
     * */
    private $items;

    /**
     * @ORM\OneToOne(targetEntity="Weborder", inversedBy="shipment")
     * @ORM\JoinColumn(name="weborder_id", referencedColumnName="id")
     */
    private $weborder;

    /**
     * @ORM\Column(name="created_on", type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\Column(name="updated_on", type="datetime")
     */
    private $updatedOn;

    public function __construct($data = null) {
        $this->items = new ArrayCollection();
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getItems() {
        return $this->items;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    public function getInvoiceDate() {
        return $this->invoiceDate;
    }

    public function setInvoiceDate($invoiceDate) {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getFriendlyStatus() {
        switch ($this->status) {
            case "IV":
                return "Processing";
            case "IP":
                return "Printed";
            case "IJ":
                return "Posted";
            default:
                return $this->status;
        }
    }

    public function getWeborder() {
        return $this->weborder;
    }

    public function setWeborder($weborder) {
        $this->weborder = $weborder;
        return $this;
    }

    public function getCreatedOn() {
        return $this->createdOn;
    }

    public function getUpdatedOn() {
        return $this->updatedOn;
    }

    public function setCreatedOn($createdOn) {
        $this->createdOn = $createdOn;
        return $this;
    }

    public function setUpdatedOn($updatedOn) {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->createdOn = new DateTime();
        $this->updatedOn = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->updatedOn = new DateTime();
    }

}
