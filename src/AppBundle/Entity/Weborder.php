<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Weborder
 *
 * @ORM\Table(name="weborder")
 * @ORM\Entity(repositoryClass="WeborderRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Weborder {

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
     * @ORM\Column(name="ship_to_company", type="string", length=255, nullable=true)
     */
    private $shipToCompany;

    /**
     * @var string
     * 
     * @ORM\Column(name="ship_to_attention", type="string", length=255, nullable=true)
     */
    private $shipToAttention;

    /**
     * @var string
     * 
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_number", type="string", length=255, unique=true)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="reference1", type="string", length=255, nullable=true)
     */
    private $reference1;

    /**
     * @var string
     *
     * @ORM\Column(name="reference2", type="string", length=255, nullable=true)
     */
    private $reference2;

    /**
     * @var string
     *
     * @ORM\Column(name="reference3", type="string", length=255, nullable=true)
     */
    private $reference3;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_first_name", type="string", length=255, nullable=true)
     */
    private $shipToFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_last_name", type="string", length=255, nullable=true)
     */
    private $shipToLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address1", type="string", length=255)
     */
    private $shipToAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address2", type="string", length=255, nullable=true)
     */
    private $shipToAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address3", type="string", length=255, nullable=true)
     */
    private $shipToAddress3;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_city", type="string", length=255)
     */
    private $shipToCity;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_state", type="string", length=255, nullable=true)
     */
    private $shipToState;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_zip", type="string", length=255, nullable=true)
     */
    private $shipToZip;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_country", type="string", length=255)
     */
    private $shipToCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_phone", type="string", length=255, nullable=true)
     */
    private $shipToPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_email", type="string", length=255, nullable=true)
     */
    private $shipToEmail;

    /**
     * @ORM\Column(name="customer_number", type="string", length=255)
     * */
    private $customerNumber;

    /**
     * @ORM\Column(name="order_date", type="datetime")
     */
    private $orderDate;

    /**
     * @ORM\Column(name="rush", type="boolean", nullable=true)
     */
    private $rush;

    /**
     * @ORM\OneToMany(targetEntity="WeborderItem", mappedBy="weborder", cascade={"persist", "remove"})
     * */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity="WeborderAudit", mappedBy="weborder", cascade={"persist", "remove"})
     * */
    private $audits;

    /**
     * @ORM\OneToOne(targetEntity="Shipment", mappedBy="weborder")
     */
    private $shipment;

    /**
     * @ORM\OneToOne(targetEntity="Invoice", mappedBy="weborder")
     */
    private $invoice;

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
        $this->audits = new ArrayCollection();
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set reference1
     *
     * @param string $reference1
     * @return Weborder
     */
    public function setReference1($reference1) {
        $this->reference1 = $reference1;

        return $this;
    }

    /**
     * Get reference1
     *
     * @return string 
     */
    public function getReference1() {
        return $this->reference1;
    }

    public function getReference2() {
        return $this->reference2;
    }

    public function getReference3() {
        return $this->reference3;
    }

    public function getShipToFirstName() {
        return $this->shipToFirstName;
    }

    public function getShipToLastName() {
        return $this->shipToLastName;
    }

    public function getShipToAddress1() {
        return $this->shipToAddress1;
    }

    public function getShipToAddress2() {
        return $this->shipToAddress2;
    }

    public function getShipToCity() {
        return $this->shipToCity;
    }

    public function getShipToState() {
        return $this->shipToState;
    }

    public function getShipToZip() {
        return $this->shipToZip;
    }

    public function getShipToCountry() {
        return $this->shipToCountry;
    }

    public function getShipToPhone() {
        return $this->shipToPhone;
    }

    public function getShipToEmail() {
        return $this->shipToEmail;
    }

    public function setReference2($reference2) {
        $this->reference2 = $reference2;
        return $this;
    }

    public function setReference3($reference3) {
        $this->reference3 = $reference3;
        return $this;
    }

    public function setShipToFirstName($shipToFirstName) {
        $this->shipToFirstName = $shipToFirstName;
        return $this;
    }

    public function setShipToLastName($shipToLastName) {
        $this->shipToLastName = $shipToLastName;
        return $this;
    }

    public function setShipToAddress1($shipToAddress1) {
        $this->shipToAddress1 = $shipToAddress1;
        return $this;
    }

    public function setShipToAddress2($shipToAddress2) {
        $this->shipToAddress2 = $shipToAddress2;
        return $this;
    }

    public function setShipToCity($shipToCity) {
        $this->shipToCity = $shipToCity;
        return $this;
    }

    public function setShipToState($shipToState) {
        $this->shipToState = $shipToState;
        return $this;
    }

    public function setShipToZip($shipToZip) {
        $this->shipToZip = $shipToZip;
        return $this;
    }

    public function setShipToCountry($shipToCountry) {
        $this->shipToCountry = $shipToCountry;
        return $this;
    }

    public function setShipToPhone($shipToPhone) {
        $this->shipToPhone = $shipToPhone;
        return $this;
    }

    public function setShipToEmail($shipToEmail) {
        $this->shipToEmail = $shipToEmail;
        return $this;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function setOrderDate($orderDate) {
        $this->orderDate = $orderDate;
        return $this;
    }

    public function getRush() {
        return $this->rush;
    }

    public function setRush($rush) {
        $this->rush = $rush;
        return $this;
    }

    public function getItems() {
        return $this->items;
    }

    public function setItems($weborderItems) {
        $this->items = $weborderItems;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getShipToAttention() {
        return $this->shipToAttention;
    }

    public function setShipToAttention($shipToAttention) {
        $this->shipToAttention = $shipToAttention;
        return $this;
    }

    public function getShipToCompany() {
        return $this->shipToCompany;
    }

    public function setShipToCompany($shipToCompany) {
        $this->shipToCompany = $shipToCompany;
        return $this;
    }

    public function getShipToAddress3() {
        return $this->shipToAddress3;
    }

    public function setShipToAddress3($shipToAddress3) {
        $this->shipToAddress3 = $shipToAddress3;
        return $this;
    }

    public function addItem(WeborderItem $item) {
        $this->items[] = $item;
    }

    public function removeItem(WeborderItem $item) {
        $this->items->removeElement($item);
    }

    public function getAudits() {
        return $this->audits;
    }

    public function setAudits($audits) {
        $this->audits = $audits;
        return $this;
    }

    public function addAudit(WeborderAudit $audit) {
        $this->audits[] = $audit;
    }

    public function removeAudit(WeborderAudit $audit) {
        $this->audits->removeElement($audit);
    }

    public function getFriendlyStatus() {
        $audits = $this->getAudits();
        $shipped = false;
        $packed = false;
        $pickedUp = false;
        foreach ($audits as $audit) {
            if ($audit->getStatusCode() == 'SH') {
                $shipped = true;
            }
            if ($audit->getStatusCode() == 'OESHIP') {
                $pickedUp = true;
            }
            if ($audit->getStatusCode() == 'OEPT') {
                $packed = true;
            }
        }

        if ($shipped) {
            return "Shipped";
        }

        if ($pickedUp) {
            return "Picked Up";
        }

        if ($packed) {
            return "Packed";
        }

        return "Processing";
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

    public function getShipment() {
        return $this->shipment;
    }

    public function getInvoice() {
        return $this->invoice;
    }

    public function setShipment($shipment) {
        $this->shipment = $shipment;
        return $this;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
        return $this;
    }

}
