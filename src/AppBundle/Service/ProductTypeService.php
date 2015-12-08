<?php

namespace AppBundle\Service;

use AppBundle\Entity\ProductType;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductTypeService {

    private $_em;

    public function __construct(EntityManager $em) {
        $this->_em = $em;
    }

    /**
     * Import productTypes from a CSV file
     * 
     * Valid mappings:
     * 
     * code
     * name
     * 
     * @param SplFileObject $file
     * @param array $mapping
     * @param type $skipFirstRow
     */
    public function importFromCSV(SplFileObject $file, array $mapping, $skipFirstRow = false) {
        
        $repository = $this->_em->getRepository('AppBundle:ProductType');

        $i = 0;

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {
                
                $productType = $repository->findOneByCode($row[$mapping['code']]);
                
                if ($productType === null) {
                    $productType = new ProductType();
                }

                $productType->setCode($row[$mapping['code']]);
                $productType->setName($row[$mapping['name']]);

                $this->_em->persist($productType);
                
            }

            $i++;
        }
        
        $this->_em->flush();
        
    }

    public function exportCsv($filename) {

        $file = new SplFileObject($filename, "wb");

        $repository = $this->_em->getRepository('AppBundle:ProductType');

        $productTypes = $repository->findAll();

        foreach ($productTypes as $productType) {
            $file->fputcsv(array(
                $productType->getCode(),
                $productType->getName()
            ));
        }
    }

}
