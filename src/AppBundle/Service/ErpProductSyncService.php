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

        $query = "FOR EACH item NO-LOCK WHERE "
                . "item.company_it = '{$this->_company}' AND item.item = '{$product->getSku()}' AND item.web_item = yes, "
                . "EACH wa_item NO-LOCK WHERE "
                . "wa_item.company_it = item.company_it AND wa_item.item = item.item";

        $result = $this->_erp->read($query, "item.item,item.manufacturer,item.product_line,item.descr,wa_item.qty_oh,wa_item.list_price");

        if (empty($result)) {
            return;
        }

        $item = $result[0];

        $mrep = $this->_em->getRepository('AppBundle:Manufacturer');
        $trep = $this->_em->getRepository('AppBundle:ProductType');

        $manufacturer = $mrep->findOneByCode($item->item_manufacturer);

        if ($manufacturer === null) {
            $manufacturer = new Manufacturer();
            $manufacturer->setCode($item->item_manufacturer);
            $manufacturer->setName($item->item_manufacturer);
            $manufacturer->setShowInMenu(true);
            $this->_em->persist($manufacturer);
            $this->_em->flush($manufacturer);
        }

        $type = $trep->findOneByCode($item->item_product_line);

        if ($type === null) {
            $type = new ProductType();
            $type->setCode($item->item_product_line);
            $type->setName($item->item_product_line);
            $type->setShowInMenu(true);
            $this->_em->persist($type);
            $this->_em->flush($type);
        }

        $product->setName(implode(" ", $item->item_descr));
        $product->setManufacturer($manufacturer);
        $product->setProductType($type);
        $product->setStockQuantity($item->wa_item_qty_oh);
        $product->setPrice($item->wa_item_list_price);

        $this->_em->persist($product);
        $this->_em->flush();
    }

    public function loadFromErp(OutputInterface $output) {

        $query = "FOR EACH item NO-LOCK WHERE "
                . "item.company_it = '{$this->_company}' AND item.web_item = yes, "
                . "EACH wa_item NO-LOCK WHERE "
                . "wa_item.company_it = item.company_it AND wa_item.item = item.item";

        $batch = 0;
        $batchSize = 1000;

        do {

            $result = $this->_erp->read($query, "item.item,item.manufacturer,item.product_line,item.descr,wa_item.qty_oh,wa_item.list_price", $batch, $batchSize);

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

        $manufacturer = $mrep->findOneByCode($item->item_manufacturer);

        if ($manufacturer === null) {
            $manufacturer = new Manufacturer();
            $manufacturer->setCode($item->item_manufacturer);
            $manufacturer->setName($item->item_manufacturer);
            $manufacturer->setShowInMenu(true);
            $this->_em->persist($manufacturer);
            $this->_em->flush($manufacturer);
        }

        $type = $trep->findOneByCode($item->item_product_line);

        if ($type === null) {
            $type = new ProductType();
            $type->setCode($item->item_product_line);
            $type->setName($item->item_product_line);
            $type->setShowInMenu(true);
            $this->_em->persist($type);
            $this->_em->flush($type);
        }

        $product = $prep->findOneBySku($item->item_item);

        if ($product === null) {
            $product = new Product();
            $product->setSku($item->item_item);
        }

        $product->setName(implode(" ", $item->item_descr));
        $product->setManufacturer($manufacturer);
        $product->setProductType($type);
        $product->setStockQuantity($item->wa_item_qty_oh);
        $product->setPrice($item->wa_item_list_price);

        $this->_em->persist($product);
    }

}
