<?php

namespace AppBundle\Soap;

class SoapPackage {

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
    public $manifestId;

    /**
     * @type string
     */
    public $trackingNumber;

    /**
     * @type double
     */
    public $packageCharge;

    /**
     * @type double
     */
    public $height;

    /**
     * @type double
     */
    public $length;

    /**
     * @type string
     */
    public $shipViaCode;

    /**
     * @type double
     */
    public $weight;

    /**
     * @type double
     */
    public $width;

}
