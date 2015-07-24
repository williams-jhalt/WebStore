<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProductAttachment;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductAttachmentService {

    private $_em;

    public function __construct(EntityManager $em) {
        $this->_em = $em;
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

        $this->_em->beginTransaction();

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $product = $productRepository->findOrCreate(array(
                    'sku' => $row[$mapping['sku']]));

                if ($product) {

                    $repository->findOrCreate(array(
                        'product' => $product,
                        'path' => "http://s3.amazonaws.com/images.williams-trading.com/product_images/" . $product->getSku() . "/" . $row[$mapping['filename']],
                        'explicit' => false
                    ));
                }
            }

            if (($i % $batchSize) === 0) {
                $this->_em->commit();
                $this->_em->clear();
                $this->_em->beginTransaction();
            }

            $i++;
        }

        $this->_em->commit();
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
