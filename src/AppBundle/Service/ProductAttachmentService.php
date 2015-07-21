<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProductAttachment;
use Doctrine\ORM\EntityManager;
use Exception;
use SplFileObject;

class ProductAttachmentService {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
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

        $productRepository = $this->em->getRepository('AppBundle:Product');

        $batchSize = 50;
        $i = 0;

        while (!$file->eof()) {
            
            $this->em->beginTransaction();

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $product = $productRepository->findOneBySku($row[$mapping['sku']]);

                if ($product) {
                    
                    try {

                        $attachment = new ProductAttachment();
                        $attachment->setProduct($product);
                        $attachment->setPath("http://s3.amazonaws.com/images.williams-trading.com/product_images/" . $product->getSku() . "/" . $row[$mapping['filename']]);
                        $attachment->setExplicit(false);

                        $this->em->persist($attachment);
                        
                    } catch (Exception $e) {
                        // don't worry about it
                    }
                    
                }


                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }
            
            $this->em->commit();

            $i++;
        }

        $this->em->flush();
        $this->em->clear();
    }
    
    public function exportCsv($filename) {

        $repository = $this->em->getRepository('AppBundle:ProductAttachment');
        
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
