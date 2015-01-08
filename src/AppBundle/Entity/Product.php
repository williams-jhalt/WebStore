<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductRepository")
 */
class Product {

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
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var DateTime
     * 
     * @ORM\Column(name="release_date", type="date", nullable=true)
     */
    private $releaseDate;

    /**
     * @var integer
     * 
     * @ORM\Column(name="stock_quantity", type="integer", nullable=true)
     */
    private $stockQuantity;

    /**
     * @var double
     * 
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price;

    /**
     * @var string
     * 
     * @ORM\Column(name="barcode", type="string", length=255, nullable=true)
     */
    private $barcode;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="shown", type="boolean")
     */
    private $shown = true;

    /**
     * @ORM\OneToOne(targetEntity="ProductDetail", mappedBy="product")
     * */
    private $productDetail;

    /**
     * @ORM\ManyToOne(targetEntity="Manufacturer", inversedBy="products")
     * @ORM\JoinColumn(name="manufacturer_id", referencedColumnName="id")
     * */
    private $manufacturer;

    /**
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="products")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id")
     * */
    private $productType;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="products")
     * @ORM\JoinTable(name="products_categories",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     *      )
     * */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="ProductAttachment", mappedBy="product")
     * */
    private $productAttachments;

    public function __construct() {
        $this->categories = new ArrayCollection();
        $this->productAttachments = new ArrayCollection();
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
     * Set sku
     *
     * @param string $sku
     * @return Product
     */
    public function setSku($sku) {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string 
     */
    public function getSku() {
        return $this->sku;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Product
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

    public function getReleaseDate() {
        return $this->releaseDate;
    }

    public function getStockQuantity() {
        return $this->stockQuantity;
    }

    public function getBarcode() {
        return $this->barcode;
    }

    public function setReleaseDate(DateTime $releaseDate) {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function setStockQuantity($stockQuantity) {
        $this->stockQuantity = $stockQuantity;
        return $this;
    }

    public function setBarcode($barcode) {
        $this->barcode = $barcode;
        return $this;
    }

    public function getProductType() {
        return $this->productType;
    }

    public function setProductType($productType) {
        $this->productType = $productType;
        return $this;
    }

    /**
     * 
     * @return Manufacturer
     */
    public function getManufacturer() {
        return $this->manufacturer;
    }

    /**
     * 
     * @param Manufacturer $manufacturer
     * @return Product
     */
    public function setManufacturer(Manufacturer $manufacturer) {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getCategory() {
        return $this->categories[0];
    }

    public function getCategories() {
        return $this->categories;
    }

    public function setCategories($categories) {
        $this->categories = new ArrayCollection();
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
        return $this;
    }

    public function addCategory($category) {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }
        if (($parent = $category->getParent())) {
            $this->addCategory($parent);
        }
    }

    public function removeCategory($category) {
        if (($key = $this->categories->indexOf($category)) !== false) {
            unset($this->categories[$key]);
        }
        foreach ($category->getChildren() as $child) {
            $this->removeCategory($child);
        }
        return $this;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {

        $this->price = $price;

        return $this;
    }

    public function getShown() {
        return $this->shown;
    }

    public function setShown($shown) {
        $this->shown = $shown;
        return $this;
    }

    public function getProductAttachments() {
        return $this->productAttachments;
    }

    public function setProductAttachments($productAttachments) {
        $this->productAttachments = $productAttachments;
        return $this;
    }

    public function getProductDetail() {
        return $this->productDetail;
    }

    public function setProductDetail($productDetail) {
        $this->productDetail = $productDetail;
        return $this;
    }

}
