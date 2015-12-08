<?php

namespace AppBundle\Soap;

class SoapInvoice {

    /**
     * @type string
     */
    public $orderNumber;

    /**
     * @type int
     */
    public $recordSequence;

    /**
     * @type boolean
     */
    public $consolidated;

    /**
     * @type string
     */
    public $customerNumber;

    /**
     * @type double
     */
    public $freightCharge;

    /**
     * @type double
     */
    public $grossAmount;

    /**
     * @type date
     */
    public $invoiceDate;

    /**
     * @type string
     */
    public $invoiceNumber;

    /**
     * @type double
     */
    public $netAmount;

    /**
     * @type boolean
     */
    public $open;

    /**
     * @type double
     */
    public $shippingAndHandlingCharge;

    /**
     * @type string
     */
    public $status;

    /**
     * @type wrapper[] $invoiceItems @className=\AppBundle\Soap\SoapInvoiceItem
     */
    public $invoiceItems;

}
