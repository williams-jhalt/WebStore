<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use SplFileObject;

class ManufacturerService {

    private $_em;
    private $_erp;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp) {
        $this->_em = $em;
        $this->_erp = $erp;
    }

    /**
     * Import manufacturers from a CSV file
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

                $manufacturer = $this->_em->getRepository('AppBundle:Manufacturer')->findOrCreateByCode($row[$mapping['code']]);

                $manufacturer->setCode($row[$mapping['code']]);
                $manufacturer->setName($row[$mapping['name']]);

                $this->_em->persist($manufacturer);
                $this->_em->flush();
            }

            $i++;
        }

        $this->_em->commit();
    }

    public function exportCsv($filename) {


        $file = new SplFileObject($filename, "wb");

        $repository = $this->_em->getRepository('AppBundle:Manufacturer');

        $manufacturers = $repository->findAll();

        foreach ($manufacturers as $manufacturer) {
            $file->fputcsv(array(
                $manufacturer->getCode(),
                $manufacturer->getName()
            ));
        }
    }

}
