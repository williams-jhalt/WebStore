<?php

namespace AppBundle\Soap;

class SoapShipment {
    
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
    public $open;
    
    /**
     * @type string
     */
    public $status;
    
    /**
     * @type wrapper[] $shipmentItems @className=\AppBundle\Soap\SoapShipmentItem
     */
    public $shipmentItems;
    
}