<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProductAttachment;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductAttachmentService {

    private $_em;
    private $_storageLocation;

    public function __construct(EntityManager $em, $storageLocation) {
        $this->_em = $em;
        $this->_storageLocation = $storageLocation;
    }

    public function upload(ProductAttachment $productAttachment) {

        if ($productAttachment->getFile() !== null) {
            $loc = $this->_storageLocation . DIRECTORY_SEPARATOR . $productAttachment->getProduct()->getSku();
            if (!file_exists($loc)) {
                mkdir($loc, 0777, true);
            }
            $filename = hash_file("md5", $productAttachment->getProduct()->getSku() . ":" . $productAttachment->getFile()->getClientOriginalName()) . "." . $productAttachment->getFile()->getExtension();
            $productAttachment->getFile()->move($loc, $filename);
            $productAttachment->setPath($loc . DIRECTORY_SEPARATOR . $filename);
            $productAttachment->setFile(null);
            $this->_em->persist($productAttachment);
            $this->_em->flush();
        }
    }

    /**
     * Import products from a CSV file
     * 
     * Valid mappings:
     * 
     * sku
     * filename
     * 
     * @param SplFileObject $file
     * @param array $mapping
     * @param type $skipFirstRow
     */
    public function importFromCSV(SplFileObject $file, array $mapping, $skipFirstRow = false) {

        $repository = $this->_em->getRepository('AppBundle:ProductAttachment');
        $productRepository = $this->_em->getRepository('AppBundle:Product');

        $batchSize = 500;
        $i = 0;

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $product = $productRepository->findOneBySku($row[$mapping['sku']]);

                if ($product !== null) {

                    $path = "http://s3.amazonaws.com/images.williams-trading.com/product_images/" . $product->getSku() . "/" . $row[$mapping['filename']];
                    $attachment = $repository->findOneBy(array('product' => $product, 'path' => $path));
                    
                    if ($attachment === null) {
                        $attachment = new ProductAttachment();
                        $attachment->setProduct($product)
                                ->setExplicit(false)
                                ->setPath($path);
                        $this->_em->persist($attachment);
                    }
                    
                }
            }

            if (($i % $batchSize) === 0) {
                $this->_em->flush();
                $this->_em->clear();
            }

            $i++;
        }
    }

    public function exportCsv($filename) {

        $repository = $this->_em->getRepository('AppBundle:ProductAttachment');

        $file = new SplFileObject($filename, "wb");

        $attachments = $repository->findAll();

        foreach ($attachments as $attachment) {

            $file->fputcsv(array(
                $attachment->getProduct()->getSku(),
                $attachment->getUrl(),
                $attachment->getExplicit()
            ));
        }
    }

}
