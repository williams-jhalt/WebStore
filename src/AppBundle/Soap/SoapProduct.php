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
     * @type wrapper $manufacturer @className=\AppBundle\Soap\SoapManufacturer
     */
    public $manufacturer;

    /**
     * @type wrapper $productType @className=\AppBundle\Soap\SoapProductType
     */
    public $productType;
    
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
    
    public function __construct() {
        $this->manufacturer = new SoapManufacturer();
        $this->productType = new SoapProductType();
        $this->attachments = array();
        $this->detail = new SoapProductDetail();
    }

}
