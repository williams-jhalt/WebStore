<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="erp_order")
 * @ORM\Entity()
 */
class ErpOrder {

    /**
     * @ORM\Id @ORM\Column(name="order_number", type="string")
     */
    private $orderNumber; // order

    /**
     * @ORM\Id @ORM\Column(name="record_sequence", type="integer")
     */
    private $recordSequence; // rec_seq

    /**
     * @ORM\Id @ORM\Column(name="record_type", type="string")
     */
    private $recordType; // rec_type

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
     * @ORM\Column(name="order_gross_amount", type="float", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=255, nullable=true)
     */
    private $invoiceNumber; // invoice

    /**
     * @var string
     *
     * @ORM\Column(name="freight_charge", type="float", nullable=true)
     */
    private $freightCharge; // c_tot_code_amt[1]

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_and_handling_charge", type="float", nullable=true)
     */
    private $shippingAndHandlingCharge; // c_tot_code_amt[2]

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_gross_amount", type="float", nullable=true)
     */
    private $invoiceGrossAmount; // c_tot_gross

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_net_amount", type="float", nullable=true)
     */
    private $invoiceNetAmount; // c_tot_net_ar

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_date", type="date", nullable=true)
     */
    private $invoiceDate; // invc_date

    /**
     * @var string
     *
     * @ORM\Column(name="manifest_id", type="string", length=255, nullable=true)
     */
    private $manifestId; // Manifest_id

    /**
     * @var string
     *
     * @ORM\Column(name="ship_date", type="date", nullable=true)
     */
    private $shipDate; // ship_date

    /**
     * @var string
     *
     * @ORM\Column(name="consolidated", type="boolean", nullable=true)
     */
    protected $consolidated;

    public function __construct($orderNumber, $recordSequence, $recordType) {
        $this->orderNumber = $orderNumber;
        $this->recordSequence = $recordSequence;
        $this->recordType = $recordType;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getRecordType() {
        return $this->recordType;
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

    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    public function getFreightCharge() {
        return $this->freightCharge;
    }

    public function getShippingAndHandlingCharge() {
        return $this->shippingAndHandlingCharge;
    }

    public function getInvoiceGrossAmount() {
        return $this->invoiceGrossAmount;
    }

    public function getInvoiceNetAmount() {
        return $this->invoiceNetAmount;
    }

    public function getInvoiceDate() {
        return $this->invoiceDate;
    }

    public function getManifestId() {
        return $this->manifestId;
    }

    public function getShipDate() {
        return $this->shipDate;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setRecordType($recordType) {
        $this->recordType = $recordType;
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

    public function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function setFreightCharge($freightCharge) {
        $this->freightCharge = $freightCharge;
        return $this;
    }

    public function setShippingAndHandlingCharge($shippingAndHandlingCharge) {
        $this->shippingAndHandlingCharge = $shippingAndHandlingCharge;
        return $this;
    }

    public function setInvoiceGrossAmount($invoiceGrossAmount) {
        $this->invoiceGrossAmount = $invoiceGrossAmount;
        return $this;
    }

    public function setInvoiceNetAmount($invoiceNetAmount) {
        $this->invoiceNetAmount = $invoiceNetAmount;
        return $this;
    }

    public function setInvoiceDate($invoiceDate) {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function setManifestId($manifestId) {
        $this->manifestId = $manifestId;
        return $this;
    }

    public function setShipDate($shipDate) {
        $this->shipDate = $shipDate;
        return $this;
    }

    public function getConsolidated() {
        return $this->consolidated;
    }

    public function setConsolidated($consolidated) {
        $this->consolidated = $consolidated;
        return $this;
    }
    
    

}
