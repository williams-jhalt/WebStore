<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Order {

    private $orderNumber; // order
    private $recordSequence; // rec_seq
    private $shipToName; // name
    private $shipToAddress1; // adr[1]
    private $shipToAddress2; // adr[2]
    private $shipToAddress3; // adr[3]
    private $shipToCity; // adr[4]
    private $shipToState; // state
    private $shipToPostalCode; // postal_code
    private $shipToCountryCode; // country_code
    private $shipViaCode; // ship_via_code
    private $customerPO; // cu_po
    private $orderDate; // ord_date
    private $open; // opn
    private $orderGrossAmount; // o_tot_gross
    private $status;
    private $customerNumber; // customer

    /**
     *
     * @var ArrayCollection
     */
    private $items;

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getShipToName() {
        return $this->shipToName;
    }

    public function getShipToAddress1() {
        return $this->shipToAddress1;
    }

    public function getShipToAddress2() {
        return $this->shipToAddress2;
    }

    public function getShipToAddress3() {
        return $this->shipToAddress3;
    }

    public function getShipToCity() {
        return $this->shipToCity;
    }

    public function getShipToState() {
        return $this->shipToState;
    }

    public function getShipToPostalCode() {
        return $this->shipToPostalCode;
    }

    public function getShipToCountryCode() {
        return $this->shipToCountryCode;
    }

    public function getShipViaCode() {
        return $this->shipViaCode;
    }

    public function getCustomerPO() {
        return $this->customerPO;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function getOpen() {
        return $this->open;
    }

    public function getOrderGrossAmount() {
        return $this->orderGrossAmount;
    }

    public function getItems() {
        return $this->items;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordSequence($recordSequence) {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setShipToName($shipToName) {
        $this->shipToName = $shipToName;
        return $this;
    }

    public function setShipToAddress1($shipToAddress1) {
        $this->shipToAddress1 = $shipToAddress1;
        return $this;
    }

    public function setShipToAddress2($shipToAddress2) {
        $this->shipToAddress2 = $shipToAddress2;
        return $this;
    }

    public function setShipToAddress3($shipToAddress3) {
        $this->shipToAddress3 = $shipToAddress3;
        return $this;
    }

    public function setShipToCity($shipToCity) {
        $this->shipToCity = $shipToCity;
        return $this;
    }

    public function setShipToState($shipToState) {
        $this->shipToState = $shipToState;
        return $this;
    }

    public function setShipToPostalCode($shipToPostalCode) {
        $this->shipToPostalCode = $shipToPostalCode;
        return $this;
    }

    public function setShipToCountryCode($shipToCountryCode) {
        $this->shipToCountryCode = $shipToCountryCode;
        return $this;
    }

    public function setShipViaCode($shipViaCode) {
        $this->shipViaCode = $shipViaCode;
        return $this;
    }

    public function setCustomerPO($customerPO) {
        $this->customerPO = $customerPO;
        return $this;
    }

    public function setOrderDate($orderDate) {
        $this->orderDate = $orderDate;
        return $this;
    }

    public function setOpen($open) {
        $this->open = $open;
        return $this;
    }

    public function setOrderGrossAmount($orderGrossAmount) {
        $this->orderGrossAmount = $orderGrossAmount;
        return $this;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

}
