<?php

namespace AppBundle\Service;

use AppBundle\Entity\Category;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class CategoryService {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * Import categories from a CSV file
     * 
     * Valid mappings:
     * 
     * code
     * name
     * parent
     * 
     * @param SplFileObject $file
     * @param array $mapping
     * @param type $skipFirstRow
     */
    public function importFromCSV(SplFileObject $file, array $mapping, $skipFirstRow = false) {

        $batchSize = 500;
        $i = 0;
        
        $repository = $this->em->getRepository('AppBundle:Category');

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {   
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $category = $repository->findOrCreateByCode($row[$mapping['code']]);

                $category->setCode($row[$mapping['code']]);
                $category->setName($row[$mapping['name']]);
                
                if ($row[$mapping['parent']] != 0) {
                    $category->setParent($repository->findOrCreateByCode($row[$mapping['parent']]));
                } else {
                    $category->setParent(null);
                }

                $this->em->persist($category);

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

}
