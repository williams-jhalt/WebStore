<?php

namespace AppBundle\Service;

class OrderSearchOptions {

    private $open;
    private $searchTerms;
    private $customerNumber;
    private $new;
    private $processed;
    private $shipped;
    private $invoiced;

    public function getOpen() {
        return $this->open;
    }

    public function getSearchTerms() {
        return $this->searchTerms;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setOpen($open) {
        $this->open = $open;
        return $this;
    }

    public function setSearchTerms($searchTerms) {
        $this->searchTerms = $searchTerms;
        return $this;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getNew() {
        return $this->new;
    }

    public function getProcessed() {
        return $this->processed;
    }

    public function getShipped() {
        return $this->shipped;
    }

    public function getInvoiced() {
        return $this->invoiced;
    }

    public function setNew($new) {
        $this->new = $new;
        return $this;
    }

    public function setProcessed($processed) {
        $this->processed = $processed;
        return $this;
    }

    public function setShipped($shipped) {
        $this->shipped = $shipped;
        return $this;
    }

    public function setInvoiced($invoiced) {
        $this->invoiced = $invoiced;
        return $this;
    }

}
