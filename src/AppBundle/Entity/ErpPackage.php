<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="erp_package")
 * @ORM\Entity()
 */
class ErpPackage {

    /**
     * @var string
     *
     * @ORM\Id @ORM\Column(name="order_number", type="string", length=255)
     */
    private $orderNumber; // order

    /**
     * @var string
     *
     * @ORM\Id @ORM\Column(name="record_sequence", type="string", length=255)
     */
    private $recordSequence; // rec_seq

    /**
     * @var string
     *
     * @ORM\Id @ORM\Column(name="tracking_number", type="string", length=255)
     */
    private $trackingNumber; // tracking_no

    /**
     * @var string
     *
     * @ORM\Column(name="manifest_id", type="string", length=255, nullable=true)
     */
    private $manifestId; // Manifest_id

    /**
     * @var string
     *
     * @ORM\Column(name="ship_via_code", type="string", length=255, nullable=true)
     */
    private $shipViaCode; // ship_via_code

    /**
     * @var string
     *
     * @ORM\Column(name="package_charge", type="decimal", nullable=true)
     */
    private $packageCharge; // pkg_chg

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", nullable=true)
     */
    private $weight; // pack_weight

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="decimal", nullable=true)
     */
    private $height; // pack_height

    /**
     * @var string
     *
     * @ORM\Column(name="length", type="decimal", nullable=true)
     */
    private $length; // pack_length

    /**
     * @var string
     *
     * @ORM\Column(name="width", type="decimal", nullable=true)
     */
    private $width; // pack_width
    
    public function __construct($orderNumber, $recordSequence, $trackingNumber) {
        $this->orderNumber = $orderNumber;
        $this->recordSequence = $recordSequence;
        $this->trackingNumber = $trackingNumber;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getTrackingNumber() {
        return $this->trackingNumber;
    }

    public function getManifestId() {
        return $this->manifestId;
    }

    public function getShipViaCode() {
        return $this->shipViaCode;
    }

    public function getPackageCharge() {
        return $this->packageCharge;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getLength() {
        return $this->length;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setTrackingNumber($trackingNumber) {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function setManifestId($manifestId) {
        $this->manifestId = $manifestId;
        return $this;
    }

    public function setShipViaCode($shipViaCode) {
        $this->shipViaCode = $shipViaCode;
        return $this;
    }

    public function setPackageCharge($packageCharge) {
        $this->packageCharge = $packageCharge;
        return $this;
    }

    public function setWeight($weight) {
        $this->weight = $weight;
        return $this;
    }

    public function setHeight($height) {
        $this->height = $height;
        return $this;
    }

    public function setLength($length) {
        $this->length = $length;
        return $this;
    }

    public function setWidth($width) {
        $this->width = $width;
        return $this;
    }

}
