<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Weborder
 *
 * @ORM\Table(name="package")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PackageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Package {

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
     * @ORM\Column(name="tracking_number", type="string", length=255, nullable=true)
     */
    private $trackingNumber;

    /**
     * @var string
     * 
     * @ORM\Column(name="pkg_chg", type="string", length=255, nullable=true)
     */
    private $packageCharge;

    /**
     * @ORM\OneToMany(targetEntity="PackageItem", mappedBy="package")
     * */
    private $items;

    /**
     * @ORM\Column(name="created_on", type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\Column(name="updated_on", type="datetime")
     */
    private $updatedOn;

    public function __construct($data = null) {
        $this->items = new ArrayCollection();
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getTrackingNumber() {
        return $this->trackingNumber;
    }

    public function getPackageCharge() {
        return $this->packageCharge;
    }

    public function getItems() {
        return $this->items;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setTrackingNumber($trackingNumber) {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function setPackageCharge($packageCharge) {
        $this->packageCharge = $packageCharge;
        return $this;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

    public function getCreatedOn() {
        return $this->createdOn;
    }

    public function getUpdatedOn() {
        return $this->updatedOn;
    }

    public function setCreatedOn($createdOn) {
        $this->createdOn = $createdOn;
        return $this;
    }

    public function setUpdatedOn($updatedOn) {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->createdOn = new DateTime();
        $this->updatedOn = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->updatedOn = new DateTime();
    }

}
