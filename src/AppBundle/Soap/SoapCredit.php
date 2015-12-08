<?php

namespace AppBundle\Soap;

class SoapCredit {
    
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
     * @type wrapper[] $creditItems @className=\AppBundle\Soap\SoapCreditItem
     */
    public $creditItems;
    
}