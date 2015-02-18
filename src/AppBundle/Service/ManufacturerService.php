<?php

namespace AppBundle\Service;

use AppBundle\Entity\Manufacturer;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class ManufacturerService {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
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

        $batchSize = 500;
        $i = 0;

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {   
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $manufacturer = $this->em->getRepository('AppBundle:Manufacturer')->findOrCreateByCode($row[$mapping['code']]);

                $manufacturer->setCode($row[$mapping['code']]);
                $manufacturer->setName($row[$mapping['name']]);

                $this->em->persist($manufacturer);

                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $i++;
        }

        $this->em->flush();
        $this->em->clear();
    }
    
    public function exportCsv($filename) {
        
        
        $file = new SplFileObject($filename, "wb");
        
        $repository = $this->em->getRepository('AppBundle:Manufacturer');

        $manufacturers = $repository->findAll();
        
        foreach ($manufacturers as $manufacturer) {
            $file->fputcsv(array(
                $manufacturer->getCode(),
                $manufacturer->getName()
            ));
        }
        
    }

}
