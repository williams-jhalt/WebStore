<?php

namespace AppBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Package {

    private $manifestId; // Manifest_id
    private $orderNumber; // order
    private $recordSequence; // rec_seq
    private $trackingNumber; // tracking_no
    private $shipViaCode; // ship_via_code
    private $packageCharge; // pkg_chg
    private $weight; // pack_weight
    private $height; // pack_height
    private $length; // pack_length
    private $width; // pack_width
    private $items;

    public function getManifestId() {
        return $this->manifestId;
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

    public function getItems() {
        return $this->items;
    }

    public function setManifestId($manifestId) {
        $this->manifestId = $manifestId;
        return $this;
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

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

}
