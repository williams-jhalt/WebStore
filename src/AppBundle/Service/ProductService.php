<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductDetail;
use DateTime;
use Doctrine\ORM\EntityManager;
use SplFileObject;

class ProductService {

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
     * name
     * stockQuantity
     * manufacturerCode
     * productTypeCode
     * categoryCodes
     * releaseDate
     * barcode
     * 
     * @param SplFileObject $file
     * @param array $mapping
     * @param type $skipFirstRow
     */
    public function importFromCSV(SplFileObject $file, array $mapping, $skipFirstRow = false) {
        
        $productRepository = $this->em->getRepository('AppBundle:Product');
        $manufacturerRepository = $this->em->getRepository('AppBundle:Manufacturer');
        $productTypeRepository = $this->em->getRepository('AppBundle:ProductType');
        $categoryRepository = $this->em->getRepository('AppBundle:Category');
        
        $batchSize = 50;
        $i = 0;

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {   
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $product = $productRepository->findOneBySku($row[$mapping['sku']]);

                if (!$product) {
                    $product = new Product();
                }

                $product->setSku($row[$mapping['sku']]);
                $product->setName($row[$mapping['name']]);
                $product->setReleaseDate(DateTime::createFromFormat('Y-m-d', $row[$mapping['releaseDate']]));
                $product->setStockQuantity($row[$mapping['stockQuantity']]);
                
                $product->setManufacturer($manufacturerRepository->findOrCreateByCode($row[$mapping['manufacturerCode']]));
                $product->setProductType($productTypeRepository->findOrCreateByCode($row[$mapping['productTypeCode']]));
                
                $categories = array();
                foreach (explode("|", $row[$mapping['categoryCodes']]) as $categoryCode) {
                    $categories[] = $categoryRepository->findOrCreateByCode($categoryCode);
                }
                
                $product->setCategories($categories);

                $product->setBarcode($row[$mapping['barcode']]);

                $this->em->persist($product);
                

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

    /**
     * Import product details from a CSV file
     * 
     * Valid mappings:
     * 
     * sku
     * package_height
     * package_length
     * package_width
     * package_weight
     * color
     * material
     * 
     * @param SplFileObject $file
     * @param array $mapping
     * @param type $skipFirstRow
     */
    public function importDetailsFromCSV(SplFileObject $file, array $mapping, $skipFirstRow = false) {
        
        $productRepository = $this->em->getRepository('AppBundle:Product');
        
        $batchSize = 50;
        $i = 0;

        while (!$file->eof()) {

            $row = $file->fgetcsv(",");

            if ($skipFirstRow && $i == 0) {   
                $i++;
                continue;
            }

            if (sizeof($row) > 1) {

                $product = $productRepository->findOneBySku($row[$mapping['sku']]);

                if (!$product) {
                    continue;
                }
                
                $productDetail = $product->getProductDetail();
                
                if (!$productDetail) {
                    $productDetail = new ProductDetail();
                }
                
                $productDetail->setPackageHeight($row[$mapping['package_height']]);
                $productDetail->setPackageLength($row[$mapping['package_length']]);
                $productDetail->setPackageWidth($row[$mapping['package_width']]);
                $productDetail->setPackageWeight($row[$mapping['package_weight']]);
                $productDetail->setColor($row[$mapping['color']]);
                $productDetail->setMaterial($row[$mapping['material']]);

                $product->setProductDetail($productDetail);
                
                $this->em->persist($product);
                

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
