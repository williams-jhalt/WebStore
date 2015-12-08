<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductDetail;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use SplFileObject;
use XMLWriter;

class ProductService {

    const EXPORT_FORMAT_DEFAULT = 'default';
    const EXPORT_FORMAT_SHORT = 'short';
    const EXPORT_FORMAT_CATEGORIES = 'categories';
    const EXPORT_FORMAT_RELEASE_DATE = 'release_date';
    const EXPORT_FORMAT_MINIMAL = 'minimal';
    const EXPORT_FORMAT_DIMENSIONS = 'dimensions';

    private $_em;

    public function __construct(EntityManager $em) {
        $this->_em = $em;
    }

    /**
     * Find all products
     * 
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function findAll($offset = 0, $limit = 10) {

        $rep = $this->_em->getRepository('AppBundle:Product');

        $products = $rep->findBy(array(), array('sku' => 'ASC'), $limit, $offset);

        return $products;
    }

    /**
     * Find products by multiple fields
     * 
     * $params['manufacturer']
     * $params['product_line']
     * $params['category_id']
     * $params['search_terms']
     * 
     * @param array $params
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function findBy(array $params, $offset = 0, $limit = 100) {

        $repository = $this->_em->getRepository("AppBundle:Product");

        $qb = $repository->createQueryBuilder('p');

        if (isset($params['search_terms']) && !empty($params['search_terms'])) {
            $qb->andWhere('p.sku LIKE :searchTerms OR p.name LIKE :searchTerms')->setParameter('searchTerms', '%' . $params['search_terms'] . '%');
        }

        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $qb->join('p.categories', 'c', 'WITH', 'c.id = :categoryId')->setParameter('categoryId', $params['category_id']);
        }

        if (isset($params['manufacturer']) && !empty($params['manufacturer'])) {
            $manufacturer = $this->_em->getRepository("AppBundle:Manufacturer")->findOneByCode($params['manufacturer']);
            $qb->andWhere('p.manufacturer = :manufacturer')->setParameter('manufacturer', $manufacturer);
        }

        if (isset($params['product_line']) && !empty($params['product_line'])) {
            $productType = $this->_em->getRepository("AppBundle:ProductType")->findOneByCode($params['product_line']);
            $qb->andWhere('p.productType = :productType')->setParameter('productType', $productType);
        }

        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);

        $query = $qb->getQuery();

        $products = $query->getResult();

        return $products;
    }

    public function get($itemNumber) {

        $rep = $this->_em->getRepository('AppBundle:Product');

        $product = $rep->findOneBy(array('sku' => $itemNumber));

        return $product;
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

        $productRepository = $this->_em->getRepository('AppBundle:Product');
        $categoryRepository = $this->_em->getRepository('AppBundle:Category');

        $categoryLookup = array();

        $batchSize = 500;
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

                $categories = array();
                foreach (explode("|", $row[$mapping['categoryCodes']]) as $categoryCode) {
                    if (!array_key_exists($categoryCode, $categoryLookup)) {
                        $categoryLookup[$categoryCode] = $categoryRepository->findOrCreateByCode($categoryCode);
                    }
                    $categories[] = $categoryLookup[$categoryCode];
                }

                if ($product->getCategories() != $categories) {
                    $product->setCategories($categories);
                }

                $this->_em->persist($product);

                if (($i % $batchSize) == 0) {
                    $this->_em->flush();
                    $this->_em->clear();
                    $categoryLookup = array();
                    echo "Memory usage after: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;
                }
            }

            $i++;
        }
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

        $productRepository = $this->_em->getRepository('AppBundle:Product');

        $i = 0;

        $this->_em->beginTransaction();

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

                $this->_em->persist($product);
                $this->_em->flush();
            }

            $i++;
        }

        $this->_em->commit();
    }

    /**
     * 
     * @param SplFileObject $file
     */
    public function importDescriptionsFromXML(SplFileObject $file) {

        $productRepository = $this->_em->getRepository('AppBundle:Product');

        $xmlProducts = simplexml_load_file($file->getRealPath());

        $batchSize = 50;
        $i = 0;

        foreach ($xmlProducts as $xmlProduct) {

            $product = $productRepository->findOneBySku($xmlProduct['sku']);

            if (!$product) {
                continue;
            }

            $productDetail = $product->getProductDetail();

            if (!$productDetail) {
                $productDetail = new ProductDetail();
            }

            $productDetail->setTextDescription($xmlProduct);

            $product->setProductDetail($productDetail);

            $this->_em->persist($product);


            if (($i % $batchSize) === 0) {
                $this->_em->flush();
                $this->_em->clear();
            }

            $i++;
        }

        $this->_em->flush();
        $this->_em->clear();
    }

    public function exportToXML($filename) {

        $productRepository = $this->_em->getRepository('AppBundle:Product');

        $writer = new XMLWriter();

        $writer->openUri($filename);

        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('products');

        $products = $productRepository->findAll();

        foreach ($products as $product) {

            $productDetail = $product->getProductDetail();

            if ($productDetail == null) {
                $productDetail = new ProductDetail();
            }

            $writer->startElement('product');

            $writer->writeElement('sku', $product->getSku());

            $writer->startElement('name');
            $writer->writeCData($product->getName());
            $writer->endElement(); // name

            $writer->startElement('description');
            $writer->writeCData($productDetail->getTextDescription());
            $writer->endElement(); // description

            $writer->writeElement('keywords', null);

            $writer->writeElement('price', $product->getPrice());

            $writer->writeElement('stock_quantity', $product->getStockQuantity());

            $writer->writeElement('reorder_quantity', 0);

            $writer->writeElement('height', $productDetail->getPackageHeight());

            $writer->writeElement('length', $productDetail->getPackageLength());

            $writer->writeElement('diameter', $productDetail->getPackageWidth());

            $writer->writeElement('weight', $productDetail->getPackageWeight());

            $writer->writeElement('color', $productDetail->getColor());

            $writer->writeElement('material', $productDetail->getMaterial());

            $writer->writeElement('barcode', $product->getBarcode());

            $writer->writeElement('release_date', $product->getReleaseDate()->format('Y-m-d'));

            $writer->startElement('images');

            foreach ($product->getProductAttachments() as $attachment) {
                $pathArr = explode('/', $attachment->getUrl());
                $length = sizeof($pathArr);
                $path = "/{$pathArr[$length - 2]}/{$pathArr[$length - 1]}";
                $writer->writeElement('image', $path);
            }

            $writer->endElement(); // images

            $writer->startElement('categories');

            foreach ($product->getCategories() as $category) {

                $writer->startElement('category');
                $writer->writeAttribute('code', $category->getCode());
                $writer->writeAttribute('video', 0);
                $writer->writeAttribute('parent', $category->getParent() ? $category->getParent()->getCode() : 0);
                $writer->text($category->getName());
                $writer->endElement(); // category
            }

            $writer->endElement(); // categories

            $writer->startElement('manufacturer');
            $writer->writeAttribute('code', $product->getManufacturer()->getCode());
            $writer->writeAttribute('video', 0);
            $writer->text($product->getManufacturer()->getName());
            $writer->endElement(); // manufacturer

            $writer->startElement('type');
            $writer->writeAttribute('code', $product->getProductType()->getCode());
            $writer->writeAttribute('video', 0);
            $writer->text($product->getProductType()->getName());
            $writer->endElement(); // type

            $writer->endElement(); // product
        }


        $writer->endElement(); // products
    }

    public function exportCsv($filename, $format = self::EXPORT_FORMAT_DEFAULT) {

        $repository = $this->_em->getRepository("AppBundle:Product");

        $file = new SplFileObject($filename, "wb");
        switch ($format) {
            case self::EXPORT_FORMAT_SHORT:
                $file->fputcsv(array(
                    "sku", "name", "shortdesc", "longdesc", "type", "vendor", "price", "stock", "active", "barcode"
                ));
                break;
            case self::EXPORT_FORMAT_CATEGORIES:
                $file->fputcsv(array(
                    "sku", "name", "shortdesc", "longdesc", "type", "vendor", "price", "stock", "active", "barcode", "categories"
                ));
                break;
            case self::EXPORT_FORMAT_RELEASE_DATE:
                $file->fputcsv(array(
                    "sku", "name", "shortdesc", "longdesc", "type", "vendor", "price", "stock", "active", "barcode", "release_date"
                ));
                break;
            case self::EXPORT_FORMAT_MINIMAL:
                $file->fputcsv(array(
                    "sku", "type", "vendor", "price", "stock", "active", "barcode"
                ));
                break;
            case self::EXPORT_FORMAT_DIMENSIONS:
                $file->fputcsv(array(
                    "sku", "height", "length", "width", "weight", "barcode"
                ));
                break;
            case self::EXPORT_FORMAT_DEFAULT:
            default:
                $file->fputcsv(array(
                    "sku", "name", "shortdesc", "longdesc", "type", "vendor", "price", "stock", "active", "barcode"
                ));
                break;
        }

        $products = $repository->findAll();

        foreach ($products as $product) {

            $productDetail = $product->getProductDetail();

            if (!$productDetail) {
                $productDetail = new ProductDetail();
            }

            switch ($format) {
                case self::EXPORT_FORMAT_SHORT:
                    if (!$product->getActive()) {
                        continue;
                    }
                    $data = array(
                        $product->getSku(),
                        $product->getName(),
                        substr($productDetail->getTextDescription(), 0, 200),
                        $productDetail->getTextDescription(),
                        $product->getProductType()->getCode(),
                        $product->getManufacturer()->getCode(),
                        $product->getPrice(),
                        $product->getStockQuantity(),
                        $product->getActive(),
                        $product->getBarcode()
                    );
                    break;
                case self::EXPORT_FORMAT_CATEGORIES:

                    $categories = array();

                    foreach ($product->getCategories() as $category) {
                        $categories[] = $category->getCode();
                    }

                    $data = array(
                        $product->getSku(),
                        $product->getName(),
                        substr($productDetail->getTextDescription(), 0, 200),
                        $productDetail->getTextDescription(),
                        $product->getProductType()->getCode(),
                        $product->getManufacturer()->getCode(),
                        $product->getPrice(),
                        $product->getStockQuantity(),
                        $product->getActive(),
                        $product->getBarcode(),
                        join(",", $categories)
                    );
                    break;
                case self::EXPORT_FORMAT_RELEASE_DATE:
                    $data = array(
                        $product->getSku(),
                        $product->getName(),
                        substr($productDetail->getTextDescription(), 0, 200),
                        $productDetail->getTextDescription(),
                        $product->getProductType()->getCode(),
                        $product->getManufacturer()->getCode(),
                        $product->getPrice(),
                        $product->getStockQuantity(),
                        $product->getActive(),
                        $product->getBarcode(),
                        $product->getReleaseDate()->format('m-d-Y')
                    );
                    break;
                case self::EXPORT_FORMAT_MINIMAL:
                    $data = array(
                        $product->getSku(),
                        $product->getProductType()->getCode(),
                        $product->getManufacturer()->getCode(),
                        $product->getPrice(),
                        $product->getStockQuantity(),
                        $product->getActive(),
                        $product->getBarcode()
                    );
                    break;
                case self::EXPORT_FORMAT_DIMENSIONS:
                    $data = array(
                        $product->getSku(),
                        $productDetail->getPackageHeight(),
                        $productDetail->getPackageLength(),
                        $productDetail->getPackageWidth(),
                        $productDetail->getPackageWeight(),
                        $product->getBarcode()
                    );
                    break;
                case self::EXPORT_FORMAT_DEFAULT:
                default:
                    $data = array(
                        $product->getSku(),
                        $product->getName(),
                        substr($productDetail->getTextDescription(), 0, 200),
                        $productDetail->getTextDescription(),
                        $product->getProductType()->getCode(),
                        $product->getManufacturer()->getCode(),
                        $product->getPrice(),
                        $product->getStockQuantity(),
                        $product->getActive(),
                        $product->getBarcode()
                    );
                    break;
            }

            $file->fputcsv($data);
        }
    }

}
