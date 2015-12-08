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
     * @Route("/", name="catalog_index")
     */
    public function indexAction() {

        $rep = $this->getDoctrine()->getRepository('AppBundle:Manufacturer');

        $manufacturers = $rep->findBy(array('showInMenu' => true), array('name' => 'ASC'));

        return $this->render('AppBundle:Catalog:index.html.twig', array(
                    'manufacturers' => $manufacturers
        ));
    }

    /**
     * @Route("/search", name="catalog_search")
     */
    public function searchAction(Request $request) {

        $searchTerms = $request->get('searchTerms');

        return $this->render('AppBundle:Catalog:search.html.twig', array('searchTerms' => $searchTerms));
    }

    /**
     * @Route("/list", name="catalog_list", options={"expose": true})
     */
    public function listAction(Request $request) {
        
        $category = $request->get('category_id', null);
        $manufacturer = $request->get('manufacturer', null);
        $type = $request->get('type', null);
        $searchTerms = $request->get('searchTerms', null);
        $page = $request->get('page', 1);

        $perPage = 10;

        $service = $this->get('app.product_service');

        $options = array(
            'category_id' => $category,
            'manufacturer' => $manufacturer,
            'product_line' => $type,
            'search_terms' => $searchTerms
        );

        $products = $service->findBy($options, (($page - 1) * $perPage), $perPage);

        $params = array(
            'products' => $products,
            'category_id' => $category,
            'manufacturer' => $manufacturer,
            'type' => $type,
            'searchTerms' => $searchTerms,
            'page' => $page
        );

        if (!empty($products)) {
            $params['nextPage'] = $this->generateUrl('catalog_list', array(
                'category_id' => $category,
                'manufacturer' => $manufacturer,
                'type' => $type,
                'searchTerms' => $searchTerms,
                'page' => $page + 1
            ));
        }


        return $this->render('AppBundle:Catalog:list.html.twig', $params);
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
     * @Cache(expires="+5 minute")
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
