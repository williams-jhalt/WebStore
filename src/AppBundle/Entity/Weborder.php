<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Weborder
 *
 * @ORM\Table(name="weborder")
 * @ORM\Entity
 */
class Weborder {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_number", type="string", length=255)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="reference1", type="string", length=255)
     */
    private $reference1;

    /**
     * @var string
     *
     * @ORM\Column(name="reference2", type="string", length=255)
     */
    private $reference2;

    /**
     * @var string
     *
     * @ORM\Column(name="reference3", type="string", length=255)
     */
    private $reference3;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_first_name", type="string", length=255)
     */
    private $shipToFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_last_name", type="string", length=255)
     */
    private $shipToLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address1", type="string", length=255)
     */
    private $shipToAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_address2", type="string", length=255)
     */
    private $shipToAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_city", type="string", length=255)
     */
    private $shipToCity;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_state", type="string", length=255)
     */
    private $shipToState;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_zip", type="string", length=255)
     */
    private $shipToZip;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_country", type="string", length=255)
     */
    private $shipToCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_phone", type="string", length=255)
     */
    private $shipToPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_to_email", type="string", length=255)
     */
    private $shipToEmail;

    /**
     * @ORM\Column(name="customer_number", type="string", length=255)
     * */
    private $customerNumber;

    /**
     * @ORM\Column(name="order_date", type="datetime")
     */
    private $orderDate;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set reference1
     *
     * @param string $reference1
     * @return Weborder
     */
    public function setReference1($reference1) {
        $this->reference1 = $reference1;

        return $this;
    }

    /**
     * Get reference1
     *
     * @return string 
     */
    public function getReference1() {
        return $this->reference1;
    }

    public function getReference2() {
        return $this->reference2;
    }

    public function getReference3() {
        return $this->reference3;
    }

    public function getShipToFirstName() {
        return $this->shipToFirstName;
    }

    public function getShipToLastName() {
        return $this->shipToLastName;
    }

    public function getShipToAddress1() {
        return $this->shipToAddress1;
    }

    public function getShipToAddress2() {
        return $this->shipToAddress2;
    }

    public function getShipToCity() {
        return $this->shipToCity;
    }

    public function getShipToState() {
        return $this->shipToState;
    }

    public function getShipToZip() {
        return $this->shipToZip;
    }

    public function getShipToCountry() {
        return $this->shipToCountry;
    }

    public function getShipToPhone() {
        return $this->shipToPhone;
    }

    public function getShipToEmail() {
        return $this->shipToEmail;
    }

    public function setReference2($reference2) {
        $this->reference2 = $reference2;
        return $this;
    }

    public function setReference3($reference3) {
        $this->reference3 = $reference3;
        return $this;
    }

    public function setShipToFirstName($shipToFirstName) {
        $this->shipToFirstName = $shipToFirstName;
        return $this;
    }

    public function setShipToLastName($shipToLastName) {
        $this->shipToLastName = $shipToLastName;
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

    public function setShipToCity($shipToCity) {
        $this->shipToCity = $shipToCity;
        return $this;
    }

    public function setShipToState($shipToState) {
        $this->shipToState = $shipToState;
        return $this;
    }

    public function setShipToZip($shipToZip) {
        $this->shipToZip = $shipToZip;
        return $this;
    }

    public function setShipToCountry($shipToCountry) {
        $this->shipToCountry = $shipToCountry;
        return $this;
    }

    public function setShipToPhone($shipToPhone) {
        $this->shipToPhone = $shipToPhone;
        return $this;
    }

    public function setShipToEmail($shipToEmail) {
        $this->shipToEmail = $shipToEmail;
        return $this;
    }

    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderDate() {
        return $this->orderDate;
    }

    public function setOrderDate($orderDate) {
        $this->orderDate = $orderDate;
        return $this;
    }

}
