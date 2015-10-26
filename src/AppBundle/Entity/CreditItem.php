<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="credit_item")
 * @ORM\Entity()
 */
class CreditItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Credit", inversedBy="items")
     * @ORM\JoinColumn(name="credit_id", referencedColumnName="id")
     * */
    private $credit;

    /**
     * @var integer
     *
     * @ORM\Column(name="line_number", type="integer")
     */
    protected $lineNumber; // q_ord

    /**
     * @var string
     *
     * @ORM\Column(name="item_number", type="string", length=255)
     */
    protected $itemNumber; // item

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name; // descr

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price; // price

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_ordered", type="integer")
     */
    protected $quantityOrdered; // q_ord

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity_credited", type="integer")
     */
    protected $quantityCredited; // q_ord

    public function getId() {
        return $this->id;
    }

    public function getCredit() {
        return $this->credit;
    }

    public function getLineNumber() {
        return $this->lineNumber;
    }

    public function getItemNumber() {
        return $this->itemNumber;
    }

    public function getName() {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getQuantityCredited() {
        return $this->quantityCredited;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setCredit($credit) {
        $this->credit = $credit;
        return $this;
    }

    public function setLineNumber($lineNumber) {
        $this->lineNumber = $lineNumber;
        return $this;
    }

    public function setItemNumber($itemNumber) {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }

    public function setQuantityCredited($quantityCredited) {
        $this->quantityCredited = $quantityCredited;
        return $this;
    }
    
    public function getQuantityOrdered() {
        return $this->quantityOrdered;
    }

    public function setQuantityOrdered($quantityOrdered) {
        $this->quantityOrdered = $quantityOrdered;
        return $this;
    }



}
