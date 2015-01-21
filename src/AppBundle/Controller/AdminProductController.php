<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductDetail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/products")
 */
class AdminProductController extends Controller {

    /**
     * @Route("/", name="admin_list_products")
     * @Template("admin/products/list.html.twig")
     */
    public function listProductsAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);

        $repository = $this->getDoctrine()->getRepository("AppBundle:Product");

        $qb = $repository->createQueryBuilder('p');

        if ($searchTerms !== null) {
            $qb->where('p.name LIKE :searchTerms OR p.sku LIKE :searchTerms OR p.barcode = :barcode')
                    ->setParameter('searchTerms', "%{$searchTerms}%")
                    ->setParameter('barcode', $searchTerms);
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 10
        );

        return array('pagination' => $pagination, 'searchTerms' => $searchTerms);
    }

    /**
     * @Route("/edit/{id}", name="admin_edit_product")
     * @Template("admin/products/edit.html.twig")
     */
    public function editProductAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $productDetail = $this->getDoctrine()->getRepository('AppBundle:ProductDetail')->findByProduct($product);

        if (!$productDetail) {
            $productDetail = new ProductDetail();
        }

        $form = $this->createFormBuilder($product)
                ->add('name', 'text')
                ->add('price', 'text')
                ->add('releaseDate', 'date', array('widget' => 'single_text'))
                ->add('stockQuantity', 'integer')
                ->add('price', 'money', array('currency' => 'USD'))
                ->add('barcode', 'text')
                ->add('manufacturer', 'entity', array(
                    'class' => 'AppBundle:Manufacturer',
                    'property' => 'name'
                ))
                ->add('productType', 'entity', array(
                    'class' => 'AppBundle:ProductType',
                    'property' => 'name'
                ))
                ->add('save', 'submit', array('label' => 'Update Product'))
                ->getForm();

        $detailForm = $this->createFormBuilder($productDetail)
                ->getForm();

        $form->handleRequest($request);
        $detailForm->handleRequest($request);

        if ($form->isValid() && $detailForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_list_products', $request->query->all());
        }

        return array('product' => $product, 'form' => $form->createView(), 'detail_form' => $detailForm->createView());
    }

    /**
     * @Route("/add", name="admin_add_product")
     * @Template("admin/products/add.html.twig")
     */
    public function addProductAction(Request $request) {

        $product = new Product();

        $form = $this->createFormBuilder($product)
                ->add('sku', 'text')
                ->add('name', 'text')
                ->add('price', 'text')
                ->add('releaseDate', 'date', array('widget' => 'single_text'))
                ->add('stockQuantity', 'integer')
                ->add('price', 'money', array('currency' => 'USD'))
                ->add('barcode', 'text')
                ->add('manufacturer', 'entity', array(
                    'class' => 'AppBundle:Manufacturer',
                    'property' => 'name'
                ))
                ->add('productType', 'entity', array(
                    'class' => 'AppBundle:ProductType',
                    'property' => 'name'
                ))
                ->add('save', 'submit', array('label' => 'Update Product'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_list_products', $request->query->all());
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_import_products")
     * @Template("admin/products/import.html.twig")
     */
    public function importProductsAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $filename = $form['importFile']->getData()->move(sys_get_temp_dir(), "import_products.csv")->getRealPath();

            $file = new SplFileObject($filename, "r");

            $service = $this->get('app.product_service');

            $service->importFromCSV($file, array(
                'sku' => 0,
                'name' => 1,
                'releaseDate' => 2,
                'stockQuantity' => 3,
                'manufacturerCode' => 4,
                'productTypeCode' => 5,
                'categoryCodes' => 6,
                'barcode' => 7
                    ), true);

            return $this->redirectToRoute('admin_list_products');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/updateShown", name="admin_products_toggle_shown", options={"expose": true})
     */
    public function updateShownAction(Request $request) {

        $id = $request->request->get('id');

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $product->setShown(!$product->getShown());

        $em = $this->getDoctrine()->getManager();

        $em->persist($product);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/showAll", name="admin_show_all_products")
     */
    public function showAllProductsAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Product p SET p.shown = 1")->execute();

        return $this->redirectToRoute('admin_list_products', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_hide_all_products")
     */
    public function hideAllProductsAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Product p SET p.shown = 0")->execute();

        return $this->redirectToRoute('admin_list_products', $request->query->all());
    }

    /**
     * @Route("/categoryTree", name="admin_product_category_tree", options={"expose": true})
     */
    public function categoryTreeAction(Request $request) {

        $productId = $request->get("productId");

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($productId);

        $parentId = $request->get("parentId");

        $data = array();

        $qb = $this->getDoctrine()->getRepository("AppBundle:Category")
                ->createQueryBuilder('c')
                ->where("c.showInMenu = 1");

        if ($parentId) {
            $parent = $this->getDoctrine()->getRepository("AppBundle:Category")->find($parentId);
            $qb->andWhere('c.parent = :parent')->setParameter('parent', $parent);
        } else {
            $qb->andWhere('c.parent is null');
        }

        $categories = $qb->getQuery()->getResult();

        foreach ($categories as $category) {
            $selected = $product->getCategories()->contains($category);
            $data[] = array(
                'id' => $category->getId(),
                'text' => $category->getName(),
                'children' => sizeof($category->getChildren()) > 0 ? true : false,
                'state' => array(
                    'checked' => $selected,
                    'opened' => $selected
                )
            );
        }

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');


        return $response;
    }

    /**
     * @Route("/addToCategory", name="admin_product_add_to_category", options={"expose": true})
     */
    public function addToCategoryAction(Request $request) {

        $productId = $request->request->get("productId");
        $categoryId = $request->request->get("categoryId");

        $em = $this->getDoctrine()->getManager();

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($productId);
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($categoryId);

        $product->addCategory($category);

        $em->persist($product);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/removeFromCategory", name="admin_product_remove_from_category", options={"expose": true})
     */
    public function removeFromCategoryAction(Request $request) {

        $productId = $request->request->get("productId");
        $categoryId = $request->request->get("categoryId");

        $em = $this->getDoctrine()->getManager();

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($productId);
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($categoryId);

        $product->removeCategory($category);

        $em->persist($product);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/remove/{id}", name="admin_remove_product")
     */
    public function removeAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('admin_list_products', $request->query->all());
    }

}
