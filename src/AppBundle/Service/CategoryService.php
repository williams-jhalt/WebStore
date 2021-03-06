<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use SplFileObject;

class CategoryService {

    private $_em;

    public function __construct(EntityManager $em) {
        $this->_em = $em;
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
        
        $repository = $this->_em->getRepository('AppBundle:Category');
        
        $this->_em->beginTransaction();

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

                $this->_em->persist($category);
                $this->_em->flush();
            }

            $i++;
        }
        
        $this->_em->commit();
        
    }
    
    public function exportCsv($filename) {
        
        
        $file = new SplFileObject($filename, "wb");
        
        $repository = $this->_em->getRepository('AppBundle:Category');

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
