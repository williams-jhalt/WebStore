<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductDetail
 *
 * @ORM\Table(name="product_detail")
 * @ORM\Entity
 */
class ProductDetail {

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
     * @ORM\Column(name="text_description", type="text")
     */
    private $textDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="html_description", type="text")
     */
    private $htmlDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="product_height", type="decimal")
     */
    private $productHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="product_length", type="decimal")
     */
    private $productLength;

    /**
     * @var string
     *
     * @ORM\Column(name="product_width", type="decimal")
     */
    private $productWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="product_weight", type="decimal")
     */
    private $productWeight;

    /**
     * @var string
     *
     * @ORM\Column(name="package_height", type="decimal")
     */
    private $packageHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="package_length", type="decimal")
     */
    private $packageLength;

    /**
     * @var string
     *
     * @ORM\Column(name="package_width", type="decimal")
     */
    private $packageWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="package_weight", type="decimal")
     */
    private $packageWeight;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="material", type="string", length=255)
     */
    private $material;

    /**
     * @ORM\OneToOne(targetEntity="Product", inversedBy="productDetail")
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
     * Set textDescription
     *
     * @param string $textDescription
     * @return ProductDetail
     */
    public function setTextDescription($textDescription) {
        $this->textDescription = $textDescription;

        return $this;
    }

    /**
     * Get textDescription
     *
     * @return string 
     */
    public function getTextDescription() {
        return $this->textDescription;
    }

    /**
     * Set htmlDescription
     *
     * @param string $htmlDescription
     * @return ProductDetail
     */
    public function setHtmlDescription($htmlDescription) {
        $this->htmlDescription = $htmlDescription;

        return $this;
    }

    /**
     * Get htmlDescription
     *
     * @return string 
     */
    public function getHtmlDescription() {
        return $this->htmlDescription;
    }

    /**
     * Set productHeight
     *
     * @param string $productHeight
     * @return ProductDetail
     */
    public function setProductHeight($productHeight) {
        $this->productHeight = $productHeight;

        return $this;
    }

    /**
     * Get productHeight
     *
     * @return string 
     */
    public function getProductHeight() {
        return $this->productHeight;
    }

    /**
     * Set productLength
     *
     * @param string $productLength
     * @return ProductDetail
     */
    public function setProductLength($productLength) {
        $this->productLength = $productLength;

        return $this;
    }

    /**
     * Get productLength
     *
     * @return string 
     */
    public function getProductLength() {
        return $this->productLength;
    }

    /**
     * Set productWidth
     *
     * @param string $productWidth
     * @return ProductDetail
     */
    public function setProductWidth($productWidth) {
        $this->productWidth = $productWidth;

        return $this;
    }

    /**
     * Get productWidth
     *
     * @return string 
     */
    public function getProductWidth() {
        return $this->productWidth;
    }

    /**
     * Set productWeight
     *
     * @param string $productWeight
     * @return ProductDetail
     */
    public function setProductWeight($productWeight) {
        $this->productWeight = $productWeight;

        return $this;
    }

    /**
     * Get productWeight
     *
     * @return string 
     */
    public function getProductWeight() {
        return $this->productWeight;
    }

    /**
     * Set packageHeight
     *
     * @param string $packageHeight
     * @return ProductDetail
     */
    public function setPackageHeight($packageHeight) {
        $this->packageHeight = $packageHeight;

        return $this;
    }

    /**
     * Get packageHeight
     *
     * @return string 
     */
    public function getPackageHeight() {
        return $this->packageHeight;
    }

    /**
     * Set packageLength
     *
     * @param string $packageLength
     * @return ProductDetail
     */
    public function setPackageLength($packageLength) {
        $this->packageLength = $packageLength;

        return $this;
    }

    /**
     * Get packageLength
     *
     * @return string 
     */
    public function getPackageLength() {
        return $this->packageLength;
    }

    /**
     * Set packageWidth
     *
     * @param string $packageWidth
     * @return ProductDetail
     */
    public function setPackageWidth($packageWidth) {
        $this->packageWidth = $packageWidth;

        return $this;
    }

    /**
     * Get packageWidth
     *
     * @return string 
     */
    public function getPackageWidth() {
        return $this->packageWidth;
    }

    /**
     * Set packageWeight
     *
     * @param string $packageWeight
     * @return ProductDetail
     */
    public function setPackageWeight($packageWeight) {
        $this->packageWeight = $packageWeight;

        return $this;
    }

    /**
     * Get packageWeight
     *
     * @return string 
     */
    public function getPackageWeight() {
        return $this->packageWeight;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return ProductDetail
     */
    public function setColor($color) {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor() {
        return $this->color;
    }

    /**
     * Set material
     *
     * @param string $material
     * @return ProductDetail
     */
    public function setMaterial($material) {
        $this->material = $material;

        return $this;
    }

    /**
     * Get material
     *
     * @return string 
     */
    public function getMaterial() {
        return $this->material;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

}
