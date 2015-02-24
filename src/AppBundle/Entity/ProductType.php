<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * ProductType
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Table(name="product_type")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductTypeRepository")
 */
class ProductType {

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
     * @JMS\Expose
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @JMS\Expose
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Product", mappedBy="productType", cascade={"persist","remove"})
     */
    private $products;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="show_in_menu", type="boolean", options={"default" = 1})
     */
    private $showInMenu = true;

    public function __construct() {
        $this->products = new ArrayCollection();
        $this->showInMenu = false;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return ProductType
     */
    public function setCode($code) {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ProductType
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    public function getProducts() {
        return $this->products;
    }

    public function getShowInMenu() {
        return $this->showInMenu;
    }

    public function setProducts($products) {
        $this->products = $products;
        return $this;
    }

    public function setShowInMenu($showInMenu) {
        $this->showInMenu = $showInMenu;
        return $this;
    }

}
