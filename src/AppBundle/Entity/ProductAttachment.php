<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductAttachment
 *
 * @ORM\Table(name="product_attachment")
 * @ORM\Entity
 */
class ProductAttachment {

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
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="explicit", type="boolean")
     */
    private $explicit;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productAttachments")
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
     * Set filename
     *
     * @param string $filename
     * @return ProductAttachment
     */
    public function setFilename($filename) {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return ProductAttachment
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set explicit
     *
     * @param boolean $explicit
     * @return ProductAttachment
     */
    public function setExplicit($explicit) {
        $this->explicit = $explicit;

        return $this;
    }

    /**
     * Get explicit
     *
     * @return boolean 
     */
    public function getExplicit() {
        return $this->explicit;
    }

    /**
     * 
     * @return Product
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * 
     * @param Product $product
     * @return ProductAttachment
     */
    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

}
