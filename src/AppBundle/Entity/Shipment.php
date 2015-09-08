<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Shipment extends Order {

    private $manifestId; // Manifest_id
    private $shipDate; // ship_date
    private $packages;

    public function __construct() {
        parent::__construct();
        $this->packages = new ArrayCollection();
    }

    public function getManifestId() {
        return $this->manifestId;
    }

    public function getShipDate() {
        return $this->shipDate;
    }

    public function getPackages() {
        return $this->packages;
    }

    public function setManifestId($manifestId) {
        $this->manifestId = $manifestId;
        return $this;
    }

    public function setShipDate($shipDate) {
        $this->shipDate = $shipDate;
        return $this;
    }

    public function setPackages($packages) {
        $this->packages = $packages;
        return $this;
    }

}
