<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="sales_order")
 * @ORM\Entity()
 */
class SalesOrder {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="order_number", type="string")
     */
    private $orderNumber; // order

    /**
     * @ORM\Column(name="record_sequence", type="integer")
     */
    private $recordSequence; // rec_seq

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_name", type="string", length=255, nullable=true)
     */
    protected $shipToName; // name

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address1", type="string", length=255, nullable=true)
     */
    protected $shipToAddress1; // adr[1]

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address2", type="string", length=255, nullable=true)
     */
    protected $shipToAddress2; // adr[2]

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address3", type="string", length=255, nullable=true)
     */
    protected $shipToAddress3; // adr[3]

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_city", type="string", length=255, nullable=true)
     */
    protected $shipToCity; // adr[4]

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_state", type="string", length=255, nullable=true)
     */
    protected $shipToState; // state

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_postal_code", type="string", length=255, nullable=true)
     */
    protected $shipToPostalCode; // postal_code

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_country_code", type="string", length=255, nullable=true)
     */
    protected $shipToCountryCode; // country_code

    /**
     * @var string
     *
     * @ORM\Column(name="ship_via_code", type="string", length=255, nullable=true)
     */
    protected $shipViaCode; // ship_via_code

    /**
     * @var string
     *
     * @ORM\Column(name="customer_po", type="string", length=255, nullable=true)
     */
    protected $customerPO; // cu_po

    /**
     * @var string
     *
     * @ORM\Column(name="order_date", type="date", nullable=true)
     */
    protected $orderDate; // ord_date

    /**
     * @var string
     *
     * @ORM\Column(name="open", type="boolean", nullable=true)
     */
    protected $open; // opn

    /**
     * @var string
     *
     * @ORM\Column(name="order_gross_amount", type="decimal", nullable=true)
     */
    protected $orderGrossAmount; // o_tot_gross

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status; // stat

    /**
     * @var string
     *
     * @ORM\Column(name="customer_number", type="string", length=255, nullable=true)
     */
    protected $customerNumber; // customer

    /**
     * @var string
     *
     * @ORM\Column(name="external_order_number", type="string", length=255, nullable=true)
     */
    protected $externalOrderNumber; // ord_ext

    /**
     * @ORM\OneToMany(targetEntity="SalesOrderItem", mappedBy="salesOrder", cascade={"persist", "remove"})
     * */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="salesOrder", cascade={"persist", "remove"})
     * */
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity="Shipment", mappedBy="salesOrder", cascade={"persist", "remove"})
     * */
    private $shipments;

    /**
     * @ORM\OneToMany(targetEntity="Credit", mappedBy="salesOrder", cascade={"persist", "remove"})
     * */
    private $credits;

    /**
     * @ORM\OneToMany(targetEntity="Package", mappedBy="salesOrder", cascade={"persist", "remove"})
     * */
    private $packages;

    public function __construct() {
        $this->items = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->shipments = new ArrayCollection();
        $this->credits = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getShipToName() {
        return $this->shipToName;
    }

    public function getShipToAddress1() {
        return $this->shipToAddress1;
    }

    public function getShipToAddress2() {
        return $this->shipToAddress2;
    }

    public function getShipToAddress3() {
        return $this->shipToAddress3;
    }

    public function getShipToCity() {
        return $this->shipToCity;
    }

    public function getShipToState() {
        return $this->shipToState;
    }

    public function getShipToPostalCode() {
        return $this->shipToPostalCode;
    }

    public function getShipToCountryCode() {
        return $this->shipToCountryCode;
    }

    public function getShipViaCode() {
        return $this->shipViaCode;
    }

    public function getCustomerPO() {
        return $this->customerPO;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function getOpen() {
        return $this->open;
    }

    public function getOrderGrossAmount() {
        return $this->orderGrossAmount;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function getExternalOrderNumber() {
        return $this->externalOrderNumber;
    }

    public function getItems() {
        return $this->items;
    }

    public function getInvoices() {
        return $this->invoices;
    }

    public function getShipments() {
        return $this->shipments;
    }

    public function getCredits() {
        return $this->credits;
    }

    public function getPackages() {
        return $this->packages;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setShipToName($shipToName) {
        $this->shipToName = $shipToName;
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

    public function setShipToAddress3($shipToAddress3) {
        $this->shipToAddress3 = $shipToAddress3;
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

    public function setShipToPostalCode($shipToPostalCode) {
        $this->shipToPostalCode = $shipToPostalCode;
        return $this;
    }

    public function setShipToCountryCode($shipToCountryCode) {
        $this->shipToCountryCode = $shipToCountryCode;
        return $this;
    }

    public function setShipViaCode($shipViaCode) {
        $this->shipViaCode = $shipViaCode;
        return $this;
    }

    public function setCustomerPO($customerPO) {
        $this->customerPO = $customerPO;
        return $this;
    }

    public function setOrderDate($orderDate) {
        $this->orderDate = $orderDate;
        return $this;
    }

    public function setOpen($open) {
        $this->open = $open;
        return $this;
    }

    public function setOrderGrossAmount($orderGrossAmount) {
        $this->orderGrossAmount = $orderGrossAmount;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function setExternalOrderNumber($externalOrderNumber) {
        $this->externalOrderNumber = $externalOrderNumber;
        return $this;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    public function setInvoices($invoices) {
        $this->invoices = $invoices;
        return $this;
    }

    public function setShipments($shipments) {
        $this->shipments = $shipments;
        return $this;
    }

    public function setCredits($credits) {
        $this->credits = $credits;
        return $this;
    }

    public function setPackages($packages) {
        $this->packages = $packages;
        return $this;
    }

}
