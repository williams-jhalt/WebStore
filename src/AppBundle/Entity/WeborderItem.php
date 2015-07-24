<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WeborderItem
 *
 * @ORM\Table(name="weborder_item")
 * @ORM\Entity(repositoryClass="WeborderItemRepository")
 */
class WeborderItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Weborder", inversedBy="items")
     * @ORM\JoinColumn(name="weborder_id", referencedColumnName="id")
     * */
    private $weborder;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_number", type="string", length=255)
     */
    private $orderNumber;

    /**
     * @var string
     * 
     * @ORM\Column(name="line_number", type="string", length=255)
     */
    private $lineNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    private $sku;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;
    
    public function __construct($data = null) {
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
        
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getWeborder() {
        return $this->weborder;
    }

    public function getSku() {
        return $this->sku;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setWeborder($weborder) {
        $this->weborder = $weborder;
        return $this;
    }

    public function setSku($sku) {
        $this->sku = $sku;
        return $this;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        return $this;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getLineNumber() {
        return $this->lineNumber;
    }

    public function setLineNumber($lineNumber) {
        $this->lineNumber = $lineNumber;
        return $this;
    }

}
