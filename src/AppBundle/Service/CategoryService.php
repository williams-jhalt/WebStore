<?php

namespace AppBundle\Service;

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

        $i = 0;
        
        $repository = $this->em->getRepository('AppBundle:Category');
        
        $this->em->beginTransaction();

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
                $this->em->flush();
            }

            $i++;
        }
        
        $this->em->commit();
        
    }
    
    public function exportCsv($filename) {
        
        
        $file = new SplFileObject($filename, "wb");
        
        $repository = $this->em->getRepository('AppBundle:Category');

        $categories = $repository->findAll();
        
        foreach ($categories as $category) {
            $file->fputcsv(array(
                $category->getCode(),
                $category->getName(),
                $category->getParent() ? $category->getParent()->getCode() : 0
            ));
        }
        
    }

}
