<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/productTypes")
 */
class ProductTypeController extends Controller {

    /**
     * @Route("/", name="admin_product_type_list")
     * @Template("AdminBundle:ProductType:list.html.twig")
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
                $qb->getQuery(), $page, 25
        );

        return array('pagination' => $pagination, 'searchTerms' => $searchTerms);
    }

    /**
     * @Route("/edit/{id}", name="admin_product_type_edit")
     * @Template("AdminBundle:ProductType:edit.html.twig")
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

            return $this->redirectToRoute('admin_product_type_list', $request->query->all());
        }

        return array('productType' => $productType, 'form' => $form->createView());
    }

    /**
     * @Route("/add", name="admin_product_type_add")
     * @Template("AdminBundle:ProductType:add.html.twig")
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

            return $this->redirectToRoute('admin_product_type_list', $request->query->all());
        }

        return array('productType' => $productType, 'form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_product_type_import")
     * @Template("AdminBundle:ProductType:import.html.twig")
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

            return $this->redirectToRoute('admin_product_type_list');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/updateShowInMenu/{id}", name="admin_product_type_toggle_show_in_menu", options={"expose": true})
     */
    public function updateShowInMenuAction($id) {

        $productType = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($id);
        $productType->setShowInMenu(!$productType->getShowInMenu());

        $em = $this->getDoctrine()->getManager();

        $em->persist($productType);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/showAll", name="admin_product_type_show_all")
     */
    public function showAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:ProductType p SET p.showInMenu = 1")->execute();

        return $this->redirectToRoute('admin_product_type_list', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_product_type_hide_all")
     */
    public function hideAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:ProductType p SET p.showInMenu = 0")->execute();

        return $this->redirectToRoute('admin_product_type_list', $request->query->all());
    }

    /**
     * @Route("/remove/{id}", name="admin_product_type_remove")
     */
    public function removeAction($id, Request $request) {

        $productType = $this->getDoctrine()->getRepository('AppBundle:ProductType')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($productType);
        $em->flush();
        return $this->redirectToRoute('admin_product_type_list', $request->query->all());
    }

}
