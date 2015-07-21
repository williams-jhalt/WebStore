<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CartItem;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/catalog")
 */
class CatalogController extends Controller {

    /**
     * @Route("/", name="catalog_list", options={"expose": true})
     * @Cache(expires="+5 minute")
     */
    public function indexAction(Request $request) {        
        
        $perPage = 25;
        
        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms');
        $categoryId = $request->get('categoryId');
        $manufacturerId = $request->get('manufacturerId');
        $productTypeId = $request->get('productTypeId');
        $sortBy = $request->get('sortBy', 'p.sku');
        
        $service = $this->get('app.product_service');        
        
        $options = array();
        
        if ($manufacturerId) {
            $options['manufacturer'] = $this->getDoctrine()->getRepository('AppBundle:Manufacturer')->find($manufacturerId)->getCode();
        }
        
        if ($productTypeId) {
            $options['product_line'] = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($productTypeId)->getCode();
        }
        
        if ($categoryId) {
            $options['category_id'] = $this->getDoctrine()->getRepository('AppBundle:Category')->find($categoryId)->getId();
        }
        
        if ($searchTerms) {
            $options['search_terms'] = $searchTerms;
        }
        
        $products = $service->findBy($options, (($page - 1) * $perPage), $perPage);

        $params = array(
            'products' => $products,
            'pageOptions' => array(
                'searchTerms' => $searchTerms,
                'categoryId' => $categoryId,
                'manufacturerId' => $manufacturerId,
                'productTypeId' => $productTypeId,
                'sortBy' => $sortBy,
                'page' => $page
            )
        );

        if ($request->isXmlHttpRequest()) {
            $response = new Response();
            $engine = $this->container->get('templating');
            if (!empty($products)) {
                $params['nextPage'] = $this->generateUrl('catalog_list', array(
                    'searchTerms' => $searchTerms,
                    'categoryId' => $categoryId,
                    'manufacturerId' => $manufacturerId,
                    'productTypeId' => $productTypeId,
                    'sortBy' => $sortBy,
                    'page' => $page + 1
                ));
            }
            $response->setContent($engine->render('AppBundle:Catalog:list.html.twig', $params));
            return $response;
        } else {            
            return $this->render('AppBundle:Catalog:index.html.twig', $params);
        }
    }

    public function getFiltersAction() {

        if ($categoryId || $productTypeId) {
            $qb = $this->getDoctrine()->getRepository("AppBundle:Manufacturer")
                    ->createQueryBuilder('m')
                    ->where('m.showInMenu = 1')
                    ->join('m.products', 'p');

            if ($categoryId) {
                $qb->join('p.categories', 'c', 'WITH', 'c.id = :categoryId')
                        ->setParameter('categoryId', $categoryId);
            }

            if ($productTypeId) {
                $qb->join('p.productType', 't', 'WITH', 't.id = :productTypeId')
                        ->setParameter('productTypeId', $productTypeId);
            }

            $manufacturers = $qb->getQuery()->getResult();
        } else {
            $manufacturers = $this->getDoctrine()->getRepository("AppBundle:Manufacturer")->findByShowInMenu(true);
        }

        if ($categoryId || $manufacturerId) {
            $qb = $this->getDoctrine()->getRepository("AppBundle:ProductType")
                    ->createQueryBuilder('t')
                    ->where('t.showInMenu = 1')
                    ->join('t.products', 'p');

            if ($categoryId) {
                $qb->join('p.categories', 'c', 'WITH', 'c.id = :categoryId')
                        ->setParameter('categoryId', $categoryId);
            }

            if ($manufacturerId) {
                $qb->join('p.manufacturer', 'm', 'WITH', 'm.id = :manufacturerId')
                        ->setParameter('manufacturerId', $manufacturerId);
            }
            $productTypes = $qb->getQuery()->getResult();
        } else {
            $productTypes = $this->getDoctrine()->getRepository("AppBundle:ProductType")->findByShowInMenu(true);
        }
    }

    /**
     * @Route("/view/{id}", name="catalog_view", options={"expose": true})
     * @Template("AppBundle:Catalog:view.html.twig")
     */
    public function viewAction($id) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);

        return array('product' => $product);
    }

    /**
     * @Route("/addToCart/{id}", name="catalog_add_to_cart")
     */
    public function addToCartAction($id, Request $request) {

        $quantity = $request->get('quantity');

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $user = $this->getUser();

        try {

            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setUser($user);
            $cartItem->setQuantity($quantity);

            $this->getDoctrine()->getManager()->persist($cartItem);
            $this->getDoctrine()->getManager()->flush();
        } catch (UniqueConstraintViolationException $e) {
            $request->getSession()->getFlashBag()->add('notices', "{$product->getSku()} is already in cart");
        }

        return new Response("Added to Cart");
    }

    /**
     * @Route("/search", name="catalog_search")
     * @Template("AppBundle:Catalog:search.html.twig")
     */
    public function searchAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms');

        $repository = $this->getDoctrine()->getRepository("AppBundle:Product");

        $qb = $repository->createQueryBuilder('p')
                ->where('p.name LIKE :searchTerms AND p.shown = 1')
                ->setParameter('searchTerms', "%{$searchTerms}%");

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 25
        );

        return array('pagination' => $pagination);
    }

    /**
     * @Route("/price/{sku}", name="catalog_item_price")
     */
    public function itemPriceAjaxAction($sku) {
        
        $service = $this->get('app.product_service');
        
        $data = $service->getPrice($sku);

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        
    }

    /**
     * @Route("/stock/{sku}", name="catalog_item_stock")
     */
    public function itemStockAjaxAction($sku) {
        
        $service = $this->get('app.product_service');
        
        $data = $service->getStock($sku);

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        
    }

    /**
     * @Route("/categoryTree", name="catalog_category_tree", options={"expose": true})
     */
    public function categoryTreeAction(Request $request) {

        $id = $request->get("id");

        $data = array();

        if ($id == '#') {

            $data[] = array(
                'id' => '0',
                'text' => 'All Categories',
                'children' => true,
                'state' => array(
                    'opened' => true
                ),
                'a_attr' => array(
                    'href' => $this->generateUrl('catalog_list')
                )
            );
        } else {

            $qb = $this->getDoctrine()->getRepository("AppBundle:Category")
                    ->createQueryBuilder('c')
                    ->where("c.showInMenu = 1");

            if ($id !== '0') {
                $parent = $this->getDoctrine()->getRepository("AppBundle:Category")->find($id);
                $qb->andWhere('c.parent = :parent')->setParameter('parent', $parent);
            } else {
                $qb->andWhere('c.parent is null');
            }

            $categories = $qb->getQuery()->getResult();

            foreach ($categories as $category) {
                $data[] = array(
                    'id' => $category->getId(),
                    'text' => $category->getName(),
                    'children' => sizeof($category->getChildren()) > 0 ? true : false,
                    'a_attr' => array(
                        'href' => $this->generateUrl('catalog_list', array('categoryId' => $category->getId()))
                    )
                );
            }
        }

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');


        return $response;
    }

}
