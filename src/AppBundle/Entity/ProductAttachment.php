<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use JMS\Serializer\Annotation as JMS;

/**
 * ProductAttachment
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Table(name="product_attachment")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductAttachmentRepository")
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
     * @JMS\Expose
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var boolean
     *
     * @JMS\Expose
     * @ORM\Column(name="explicit", type="boolean")
     */
    private $explicit = false;

    /**
     * @var boolean
     *
     * @JMS\Expose
     * @ORM\Column(name="primary_attachment", type="boolean")
     */
    private $primaryAttachment = false;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productAttachments")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * */
    private $product;

    /**
     *
     * @var UploadedFile
     */
    private $file;

    public function getId() {
        return $this->id;
    }

    public function getPath() {
        return $this->path;
    }

    public function getExplicit() {
        return $this->explicit;
    }

    public function getPrimaryAttachment() {
        return $this->primaryAttachment;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getFile() {
        return $this->file;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function setExplicit($explicit) {
        $this->explicit = $explicit;
        return $this;
    }

    public function setPrimaryAttachment($primaryAttachment) {
        $this->primaryAttachment = $primaryAttachment;
        return $this;
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function setFile(UploadedFile $file) {
        $this->file = $file;
        return $this;
    }

}
