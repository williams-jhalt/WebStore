<?php

namespace AppBundle\Soap;

class SoapSalesOrder {

    /**
     * @type string
     */
    public $orderNumber;

    /**
     * @type int
     */
    public $recordSequence;

    /**
     * @type string
     */
    public $customerNumber;

    /**
     * @type string
     */
    public $customerPO;

    /**
     * @type string
     */
    public $externalOrderNumber;

    /**
     * @type boolean
     */
    public $open;

    /**
     * @type date
     */
    public $orderDate;

    /**
     * @type double
     */
    public $orderGrossAmount;

    /**
     * @type string
     */
    public $shipToAddress1;

    /**
     * @type string
     */
    public $shipToAddress2;

    /**
     * @type string
     */
    public $shipToAddress3;

    /**
     * @type string
     */
    public $shipToCity;

    /**
     * @type string
     */
    public $shipToCountryCode;

    /**
     * @type string
     */
    public $shipToName;

    /**
     * @type string
     */
    public $shipToPostalCode;

    /**
     * @type string
     */
    public $shipToState;

    /**
     * @type string
     */
    public $status;

    /**
     * @type string
     */
    public $shipViaCode;

    /**
     * @type wrapper[] $salesOrderItems @className=\AppBundle\Soap\SoapSalesOrderItem
     */
    public $salesOrderItems;

    /**
     * @type wrapper[] $shipments @className=\AppBundle\Soap\SoapShipment
     */
    public $shipments;

    /**
     * @type wrapper[] $invoices @className=\AppBundle\Soap\SoapInvoice
     */
    public $invoices;

    /**
     * @type wrapper[] $credits @className=\AppBundle\Soap\SoapCredit
     */
    public $credits;

    /**
     * @type wrapper[] $packages @className=\AppBundle\Soap\SoapPackage
     */
    public $packages;

}
