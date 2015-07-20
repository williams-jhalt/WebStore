<?php

namespace AppBundle\Service\Soap;

class Product {
    
    /**
     * @type string
     */
    public $sku;
    
    /**
     * @type string
     */
    public $name;
    
    /**
     * @type decimal
     */
    public $price;
    
    /**
     * @type int
     */
    public $stockQuantity;
    
    /**
     * @type string
     */
    public $barcode;
    
    /**
     * @type dateTime
     */
    public $releaseDate;
    
}

