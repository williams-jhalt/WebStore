<?php

namespace AppBundle\Soap;

class SoapProduct {

    /**
     * @type string
     */
    public $sku;

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
    public $stockQuantity;

    /**
     * @type string
     */
    public $manufacturerCode;

    /**
     * @type string
     */
    public $productTypeCode;

    /**
     * @type date
     */
    public $releaseDate;

}
