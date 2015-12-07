<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="invoice")
 * @ORM\Entity()
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
     * @ORM\ManyToOne(targetEntity="SalesOrder", inversedBy="invoices")
     * @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id")
     * */
    private $salesOrder;

    /**
     * @ORM\OneToMany(targetEntity="SalesOrder", mappedBy="consolidatedInvoice", cascade={"persist", "remove"})
     * */
    private $consolidatedSalesOrders;

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
     * @ORM\Column(name="open", type="boolean", nullable=true)
     */
    protected $open; // opn

    /**
     * @var string
     *
     * @ORM\Column(name="consolidated", type="boolean", nullable=true)
     */
    protected $consolidated;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status; // stat

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_date", type="date", nullable=true)
     */
    private $invoiceDate; // invc_date

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", length=255, nullable=true)
     */
    protected $invoiceNumber; // name

    /**
     * @var string
     *
     * @ORM\Column(name="customer_number", type="string", length=255, nullable=true)
     */
    protected $customerNumber; // customer

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
     * @ORM\Column(name="gross_amount", type="float", nullable=true)
     */
    private $grossAmount; // c_tot_gross

    /**
     * @var string
     *
     * @ORM\Column(name="net_amount", type="float", nullable=true)
     */
    private $netAmount; // c_tot_net_ar

    /**
     * @ORM\OneToMany(targetEntity="InvoiceItem", mappedBy="invoice", cascade={"persist", "remove"})
     * */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="parent", cascade={"persist", "remove"})
     * */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * */
    private $parent;

    public function __construct() {
        $this->items = new ArrayCollection();
        $this->consolidatedSalesOrders = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getSalesOrder() {
        return $this->salesOrder;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getOpen() {
        return $this->open;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getItems() {
        return $this->items;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setSalesOrder($salesOrder) {
        $this->salesOrder = $salesOrder;
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

    public function setOpen($open) {
        $this->open = $open;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
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

    public function getConsolidated() {
        return $this->consolidated;
    }

    public function setConsolidated($consolidated) {
        $this->consolidated = $consolidated;
        return $this;
    }

    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getChildren() {
        return $this->children;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setChildren($children) {
        $this->children = $children;
        return $this;
    }

    public function setParent($parent) {
        $this->parent = $parent;
        return $this;
    }

    public function getConsolidatedSalesOrders() {
        return $this->consolidatedSalesOrders;
    }

    public function setConsolidatedSalesOrders($consolidatedSalesOrders) {
        $this->consolidatedSalesOrders = $consolidatedSalesOrders;
        return $this;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getFreightCharge() {
        return $this->freightCharge;
    }

    public function getShippingAndHandlingCharge() {
        return $this->shippingAndHandlingCharge;
    }

    public function getGrossAmount() {
        return $this->grossAmount;
    }

    public function getNetAmount() {
        return $this->netAmount;
    }

    public function setFreightCharge($freightCharge) {
        $this->freightCharge = $freightCharge;
        return $this;
    }

    public function setShippingAndHandlingCharge($shippingAndHandlingCharge) {
        $this->shippingAndHandlingCharge = $shippingAndHandlingCharge;
        return $this;
    }

    public function setGrossAmount($grossAmount) {
        $this->grossAmount = $grossAmount;
        return $this;
    }

    public function setNetAmount($netAmount) {
        $this->netAmount = $netAmount;
        return $this;
    }

}
