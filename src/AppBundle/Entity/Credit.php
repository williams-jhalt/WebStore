<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="credit")
 * @ORM\Entity()
 */
class Credit {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SalesOrder", inversedBy="credits")
     * @ORM\JoinColumn(name="sales_order_id", referencedColumnName="id")
     * */
    private $salesOrder;

    /**
     * @ORM\Column(name="order_number", type="string")
     */
    private $orderNumber; // order

    /**
     * @ORM\Column(name="record_sequence", type="integer")
     */
    private $recordSequence; // rec_seq

    /**
     * @var string
     *
     * @ORM\Column(name="open", type="boolean", nullable=true)
     */
    protected $open; // opn

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status; // stat

    /**
     * @ORM\OneToMany(targetEntity="CreditItem", mappedBy="credit", cascade={"persist", "remove"})
     * */
    private $items;

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getSalesOrder() {
        return $this->salesOrder;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getRecordSequence() {
        return $this->recordSequence;
    }

    public function getOpen() {
        return $this->open;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getItems() {
        return $this->items;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setSalesOrder($salesOrder) {
        $this->salesOrder = $salesOrder;
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

    public function setOpen($open) {
        $this->open = $open;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setItems($items) {
        $this->items = $items;
        return $this;
    }

}
