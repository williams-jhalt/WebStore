<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductTypeService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
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

        $i = 0;

        $this->_em->beginTransaction();

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $productType = $this->_em->getRepository('AppBundle:ProductType')->findOrCreateByCode($row[$mapping['code']]);

                $productType->setCode($row[$mapping['code']]);
                $productType->setName($row[$mapping['name']]);

                $this->_em->persist($productType);
                $this->_em->flush();
            }

            $i++;
        }

        $this->_em->commit();
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
