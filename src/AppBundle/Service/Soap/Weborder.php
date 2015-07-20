<?php

namespace AppBundle\Service\Soap;

class Weborder {

    /**
     * @type string
     */
    public $orderNumber;

    /**
     * @type string
     */
    public $reference1;

    /**
     * @type string
     */
    public $reference2;

    /**
     * @type string
     */
    public $reference3;

    /**
     * @type string
     */
    public $shipToFirstName;

    /**
     * @type string
     */
    public $shipToLastName;

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
    public $shipToCity;

    /**
     * @type string
     */
    public $shipToState;

    /**
     * @type string
     */
    public $shipToZip;

    /**
     * @type string
     */
    public $shipToCountry;

    /**
     * @type string
     */
    public $shipToPhone;

    /**
     * @type string
     */
    public $shipToEmail;

    /**
     * @type string
     * */
    public $customerNumber;

    /**
     * @type dateTime
     */
    public $orderDate;

    /**
     * @type boolean
     */
    public $rush;
    
}
