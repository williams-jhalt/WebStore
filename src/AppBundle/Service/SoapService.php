<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\Weborder;
use Doctrine\ORM\EntityManager;
use Exception;

class SoapService {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * @return wrapper[] $products @className=\ApiBundle\Service\Soap\Product
     * @throws Exception
     */
    public function findAllProducts() {

        $products = $this->em->getRepository('AppBundle:Product')->findAll();

        $response = array();

        foreach ($products as $product) {
            $response[] = $this->_productToSoap($product);
        }

        return $response;
    }

    /**
     * 
     * @param string $sku
     * @return wrapper $product @className=\ApiBundle\Service\Soap\Product
     */
    public function getProduct($sku) {

        $product = $this->em->getRepository('AppBundle:Product')->findOneBySku($sku);
        
        return $this->_productToSoap($product);
        
    }

    /**
     * @return wrapper[] $weborders @className=\ApiBundle\Service\Soap\Weborder
     * @throws Exception
     */
    public function findAllWeborders() {
        
        $weborders = $this->em->getRepository('AppBundle:Weborder')->findAll();
        
        $response = array();
        
        foreach ($weborders as $weborder) {
            $response[] = $this->_weborderToSoap($weborder);
        }
        
        return $response;
        
    }
    
    /**
     * @param string $orderNumber
     * @return wrapper $product @className=\ApiBundle\Service\Soap\Weborder
     */
    public function getWeborder($orderNumber) {
        
        $weborder = $this->em->getRepository('AppBundle:Weborder')->findOneByOrderNumber($orderNumber);
        
        return $this->_weborderToSoap($weborder);
        
    }
    
    /**
     * @param wrapper $weborder @className=\ApiBundle\Service\Soap\WeborderSubmit
     */
    public function submitWeborder($weborder) {
        
        $this->em->persist($this->_soapToWeborder($weborder));
        $this->em->flush();
        
    }

    /**
     * 
     * @param Product $product
     * @return \ApiBundle\Service\Soap\Product
     */
    private function _productToSoap(Product $product) {
        
        $t = new \ApiBundle\Service\Soap\Product();
        $t->sku = $product->getSku();
        $t->name = $product->getName();
        $t->barcode = $product->getBarcode();
        $t->price = $product->getPrice();
        $t->stockQuantity = $product->getStockQuantity();
        $t->releaseDate = $product->getReleaseDate()->format('c');
        
        return $t;
        
    }
    
    private function _weborderToSoap(Weborder $weborder) {
        
        $t = new \ApiBundle\Service\Soap\Weborder();
        $t->orderNumber = $weborder->getOrderNumber();
        $t->reference1 = $weborder->getReference1();
        $t->reference2 = $weborder->getReference2();
        $t->reference3 = $weborder->getReference3();
        
        return $t;
        
    }
    
    private function _soapToWeborder($weborder) {
        
        $t = new Weborder();
        $t->setReference1($weborder->reference1);
        $t->setReference2($weborder->reference2);
        $t->setReference3($weborder->reference3);
        
        return $t;
        
    }

}
