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
     * @type string
     */
    public $barcode;

    /**
     * @type date
     */
    public $releaseDate;
    
    /**
     * @type wrapper[] $attachments @className=\AppBundle\Soap\SoapProductAttachment
     */
    public $attachments;
    
    /**
     * @type wrapper $detail @className=\AppBundle\Soap\SoapProductDetail
     */
    public $detail;

}
