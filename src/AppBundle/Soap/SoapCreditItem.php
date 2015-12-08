<?php

namespace AppBundle\Soap;

class SoapCreditItem {
    
    /**
     * @type int
     */
    public $lineNumber;
    
    /**
     * @type string
     */
    public $itemNumber;
    
    /**
     * @type string
     */
    public $name;
    
    /**
     * @type double
     */
    public $price;
    
    /**
     * @type int
     */
    public $quantityOrdered;
    
    /**
     * @type int
     */
    public $quantityCredited;
    
}