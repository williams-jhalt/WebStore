<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CategoryRepository")
 */
class Category {

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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="categories")
     */
    private $products;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="show_in_menu", type="boolean", options={"default" = 0})
     */
    private $showInMenu;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent", cascade={"persist", "remove"})
     * */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * */
    private $parent;

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
     * @return Category
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
     * @return Category
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

    public function setProducts(ArrayCollection $products) {
        $this->products = $products;
        return $this;
    }

    public function setShowInMenu($showInMenu) {
        $this->showInMenu = $showInMenu;
        return $this;
    }

    public function getChildren() {
        return $this->children;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setChildren($children) {
        $this->children = $children;
        return $this;
    }

    public function setParent($parent) {
        $this->parent = $parent;
        return $this;
    }
    
    public function getFullPath() {
        $path = "";
        if ($this->getParent()) {
            $path .= $this->getParent()->getFullPath() . " / ";
        }
        $path .= $this->getName();
        return $path;
    }

}
