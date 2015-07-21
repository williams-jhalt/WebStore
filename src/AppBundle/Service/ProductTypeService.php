<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductTypeService {

    private $em;
    private $erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->em = $em;
        $this->erp = $erp;
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

        $this->em->beginTransaction();

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $productType = $this->em->getRepository('AppBundle:ProductType')->findOrCreateByCode($row[$mapping['code']]);

                $productType->setCode($row[$mapping['code']]);
                $productType->setName($row[$mapping['name']]);

                $this->em->persist($productType);
                $this->em->flush();
            }

            $i++;
        }

        $this->em->commit();
    }

    public function exportCsv($filename) {

        $file = new SplFileObject($filename, "wb");

        $repository = $this->em->getRepository('AppBundle:ProductType');

        $productTypes = $repository->findAll();

        foreach ($productTypes as $productType) {
            $file->fputcsv(array(
                $productType->getCode(),
                $productType->getName()
            ));
        }
    }

}
