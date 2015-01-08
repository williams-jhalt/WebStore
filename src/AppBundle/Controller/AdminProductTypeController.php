<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/productTypes")
 */
class AdminProductTypeController extends Controller {

    /**
     * @Route("/", name="admin_list_product_types")
     * @Template("admin/product_types/list.html.twig")
     */
    public function listProductTypesAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);

        $repository = $this->getDoctrine()->getRepository("AppBundle:ProductType");

        $qb = $repository->createQueryBuilder('p');
        
        if ($searchTerms !== null) {
            $qb->where('p.name LIKE :searchTerms')
                    ->setParameter('searchTerms', "%{$searchTerms}%");
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 10
        );

        return array('pagination' => $pagination, 'searchTerms' => $searchTerms);
    }

    /**
     * @Route("/edit/{id}", name="admin_edit_product_type")
     * @Template("admin/product_types/edit.html.twig")
     */
    public function editProductTypeAction($id, Request $request) {

        $productType = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($id);

        $form = $this->createFormBuilder($productType)
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Update ProductType'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($productType);
            $em->flush();

            return $this->redirectToRoute('admin_list_product_types', $request->query->all());
        }

        return array('productType' => $productType, 'form' => $form->createView());
    }

    /**
     * @Route("/add", name="admin_add_product_type")
     * @Template("admin/product_types/add.html.twig")
     */
    public function addProductTypeAction(Request $request) {

        $productType = new ProductType();

        $form = $this->createFormBuilder($productType)
                ->add('code', 'text')
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Add ProductType'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($productType);
            $em->flush();

            return $this->redirectToRoute('admin_list_product_types', $request->query->all());
        }

        return array('productType' => $productType, 'form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_import_product_types")
     * @Template("admin/product_types/import.html.twig")
     */
    public function importProductTypesAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $file = $form['importFile']->getData()->openFile('r');
            
            $productTypeService = $this->get('app.product_type_service');
            
            $productTypeService->importFromCSV($file, array(
                'code' => 0,
                'name' => 1
            ), true);

            return $this->redirectToRoute('admin_list_product_types');
        }

        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/updateShowInMenu", name="admin_product_types_toggle_show_in_menu", options={"expose": true})
     */
    public function updateShowInMenuAction(Request $request) {
        
        $id = $request->request->get('id');
        
        $productType = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($id);
        $productType->setShowInMenu(!$productType->getShowInMenu());
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($productType);
        $em->flush();
        
        $response = array('code' => 100, 'success' => true);
        
        return new Response(json_encode($response));
        
    }

    /**
     * @Route("/showAll", name="admin_show_all_product_types")
     */
    public function showAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:ProductType p SET p.showInMenu = 1")->execute();

        return $this->redirectToRoute('admin_list_product_types', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_hide_all_product_types")
     */
    public function hideAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:ProductType p SET p.showInMenu = 0")->execute();

        return $this->redirectToRoute('admin_list_product_types', $request->query->all());
    }
    
    /**
     * @Route("/remove/{id}", name="admin_remove_product_type")
     */
    public function removeAction($id, Request $request) {
        
        $productType = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($productType);
        $em->flush();
        return $this->redirectToRoute('admin_list_product_types', $request->query->all());
        
    }

}
