<?php

namespace AppBundle\Entity;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Invoice extends Order {

    private $invoiceNumber; // invoice
    private $freightCharge;
    private $shippingAndHandlingCharge;
    private $invoiceGrossAmount; // c_tot_gross
    private $invoiceNetAmount; // c_tot_net_ar
    private $invoiceDate; // invc_date

    public function getInvoiceDate() {
        return $this->invoiceDate;
    }

    public function setInvoiceDate($invoiceDate) {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    public function getFreightCharge() {
        return $this->freightCharge;
    }

    public function getShippingAndHandlingCharge() {
        return $this->shippingAndHandlingCharge;
    }

    public function getInvoiceGrossAmount() {
        return $this->invoiceGrossAmount;
    }

    public function getInvoiceNetAmount() {
        return $this->invoiceNetAmount;
    }

    public function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function setFreightCharge($freightCharge) {
        $this->freightCharge = $freightCharge;
        return $this;
    }

    public function setShippingAndHandlingCharge($shippingAndHandlingCharge) {
        $this->shippingAndHandlingCharge = $shippingAndHandlingCharge;
        return $this;
    }

    public function setInvoiceGrossAmount($invoiceGrossAmount) {
        $this->invoiceGrossAmount = $invoiceGrossAmount;
        return $this;
    }

    public function setInvoiceNetAmount($invoiceNetAmount) {
        $this->invoiceNetAmount = $invoiceNetAmount;
        return $this;
    }

}
