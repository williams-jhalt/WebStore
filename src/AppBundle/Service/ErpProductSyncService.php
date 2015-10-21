<?php

namespace AppBundle\Service;

use AppBundle\Entity\Manufacturer;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use AppBundle\Service\ErpOneConnectorService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;

class ErpProductSyncService {

    private $_em;
    private $_erp;
    private $_company;

    public function __construct(EntityManager $em, ErpOneConnectorService $erp, $company) {
        $this->_em = $em;
        $this->_erp = $erp;
        $this->_company = $company;
    }

    public function updateProduct(Product $product) {

        $query = "FOR EACH item NO-LOCK WHERE company_it = '{$this->_company}' AND item = '{$product->getSku()}'";

        $result = $this->_erp->read($query, "item,manufacturer,product_line,descr");
        
        if (empty($result)) {
            return;
        }
        
        $item = $result[0];

        $mrep = $this->_em->getRepository('AppBundle:Manufacturer');
        $trep = $this->_em->getRepository('AppBundle:ProductType');

        $manufacturer = $mrep->findOneByCode($item->manufacturer);

        if ($manufacturer === null) {
            $manufacturer = new Manufacturer();
            $manufacturer->setCode($item->manufacturer);
            $manufacturer->setName($item->manufacturer);
            $manufacturer->setShowInMenu(true);
            $this->_em->persist($manufacturer);
            $this->_em->flush($manufacturer);
        }

        $type = $trep->findOneByCode($item->product_line);

        if ($type === null) {
            $type = new ProductType();
            $type->setCode($item->product_line);
            $type->setName($item->product_line);
            $type->setShowInMenu(true);
            $this->_em->persist($type);
            $this->_em->flush($type);
        }

        $product->setName(implode(" ", $item->descr));
        $product->setManufacturer($manufacturer);
        $product->setProductType($type);

        $this->_em->persist($product);
        $this->_em->flush();
    }

    public function loadFromErp(OutputInterface $output) {

        $query = "FOR EACH item NO-LOCK WHERE company_it = '{$this->_company}' AND web_item = yes";

        $batch = 0;
        $batchSize = 1000;

        do {

            $result = $this->_erp->read($query, "item,manufacturer,product_line,descr", $batch, $batchSize);

            foreach ($result as $item) {
                $this->_loadFromErp($item);
            }

            $batch += $batchSize;

            $output->writeln("Loaded {$batch} items");

            $this->_em->flush();
        } while (!empty($result));
    }

    private function _loadFromErp($item) {

        $prep = $this->_em->getRepository('AppBundle:Product');
        $mrep = $this->_em->getRepository('AppBundle:Manufacturer');
        $trep = $this->_em->getRepository('AppBundle:ProductType');

        $manufacturer = $mrep->findOneByCode($item->manufacturer);

        if ($manufacturer === null) {
            $manufacturer = new Manufacturer();
            $manufacturer->setCode($item->manufacturer);
            $manufacturer->setName($item->manufacturer);
            $manufacturer->setShowInMenu(true);
            $this->_em->persist($manufacturer);
            $this->_em->flush($manufacturer);
        }

        $type = $trep->findOneByCode($item->product_line);

        if ($type === null) {
            $type = new ProductType();
            $type->setCode($item->product_line);
            $type->setName($item->product_line);
            $type->setShowInMenu(true);
            $this->_em->persist($type);
            $this->_em->flush($type);
        }

        $product = $prep->findOneBySku($item->item);

        if ($product === null) {
            $product = new Product();
            $product->setSku($item->item);
        }

        $product->setName(implode(" ", $item->descr));
        $product->setManufacturer($manufacturer);
        $product->setProductType($type);

        $this->_em->persist($product);
    }

}
