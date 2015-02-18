<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * CartItem
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Table(name="cart_item")
 * @ORM\Entity
 */
class CartItem {

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="cartItems")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * */
    private $product;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return CartItem
     */
    public function setQuantity($quantity) {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity() {
        return $this->quantity;
    }

    public function getUser() {
        return $this->user;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

}
