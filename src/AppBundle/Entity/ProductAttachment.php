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
     * @JMS\Expose
     * @ORM\Column(type="string", length=255, unique=true)
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
    private $file;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * 
     * @param string $path
     * @return ProductAttachment
     */
    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    /**
     * 
     * @param boolean $primaryAttachment
     * @return ProductAttachment
     */
    public function setPrimaryAttachment($primaryAttachment) {
        $this->primaryAttachment = $primaryAttachment;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function getPrimaryAttachment() {
        return $this->primaryAttachment;
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

    /**
     * 
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * 
     * @param UploadedFile $file
     * @return ProductAttachment
     */
    public function setFile(UploadedFile $file) {
        $this->file = $file;
        return $this;
    }
    
    public function getAbsolutePath() {
        return $this->path;
    }
    
    public function getWebPath() {
        return $this->path;
    }

//    public function getAbsolutePath() {
//        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
//    }
//
//    public function getWebPath() {
//        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
//    }
//
//    public function upload() {
//        
//        // the file property can be empty if the field is not required
//        if (null === $this->getFile()) {
//            return;
//        }
//        
//        $sku = $this->getProduct()->getSku();
//        
//        $targetDir = $this->getUploadRootDir() . '/' . $sku;
//        
//        
//        if (!file_exists($targetDir)) {
//            mkdir($targetDir, 0755, true);
//        }
//        
//        $filename = hash_file('md5', $this->getFile()->getRealPath()) . '.' . $this->getFile()->getExtension();
//
//        // use the original file name here but you should
//        // sanitize it at least to avoid any security issues
//        // move takes the target directory and then the
//        // target filename to move to
//        $this->getFile()->move(
//                $targetDir, $filename
//        );
//
//        // set the path property to the filename where you've saved the file
//        $this->path = $sku . '/' . $filename;
//
//        // clean up the file property as you won't need it anymore
//        $this->file = null;
//    }
//
//    protected function getUploadRootDir() {
//        // the absolute directory path where uploaded
//        // documents should be saved
//        return __DIR__ . '/../../../web/' . $this->getUploadDir();
//    }
//
//    protected function getUploadDir() {
//        // get rid of the __DIR__ so it doesn't screw up
//        // when displaying uploaded doc/image in the view.
//        return 'uploads/product_images';
//    }

}
