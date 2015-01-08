<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Manufacturer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/manufacturers")
 */
class AdminManufacturerController extends Controller {

    /**
     * @Route("/", name="admin_list_manufacturers")
     * @Template("admin/manufacturers/list.html.twig")
     */
    public function listManufacturersAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);

        $repository = $this->getDoctrine()->getRepository("AppBundle:Manufacturer");

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
     * @Route("/edit/{id}", name="admin_edit_manufacturer")
     * @Template("admin/manufacturers/edit.html.twig")
     */
    public function editManufacturerAction($id, Request $request) {

        $manufacturer = $this->getDoctrine()->getRepository('AppBundle:Manufacturer')->find($id);

        $form = $this->createFormBuilder($manufacturer)
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Update Manufacturer'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($manufacturer);
            $em->flush();

            return $this->redirectToRoute('admin_list_manufacturers', $request->query->all());
        }

        return array('manufacturer' => $manufacturer, 'form' => $form->createView());
    }

    /**
     * @Route("/add", name="admin_add_manufacturer")
     * @Template("admin/manufacturers/add.html.twig")
     */
    public function addManufacturerAction(Request $request) {

        $manufacturer = new Manufacturer();
        
        $form = $this->createFormBuilder($manufacturer)
                ->add('code', 'text')
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Add Manufacturer'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($manufacturer);
            $em->flush();

            return $this->redirectToRoute('admin_list_manufacturers', $request->query->all());
        }

        return array('manufacturer' => $manufacturer, 'form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_import_manufacturers")
     * @Template("admin/manufacturers/import.html.twig")
     */
    public function importManufacturersAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $file = $form['importFile']->getData()->openFile('r');
            
            $manufacturerService = $this->get('app.manufacturer_service');
            
            $manufacturerService->importFromCSV($file, array(
                'code' => 0,
                'name' => 1
            ), true);

            return $this->redirectToRoute('admin_list_manufacturers');
        }

        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/updateShowInMenu", name="admin_manufacturers_toggle_show_in_menu", options={"expose": true})
     */
    public function updateShowInMenuAction(Request $request) {
        
        $id = $request->request->get('id');
        
        $manufacturer = $this->getDoctrine()->getRepository('AppBundle:Manufacturer')->find($id);
        $manufacturer->setShowInMenu(!$manufacturer->getShowInMenu());
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($manufacturer);
        $em->flush();
        
        $response = array('code' => 100, 'success' => true);
        
        return new Response(json_encode($response));
        
    }

    /**
     * @Route("/showAll", name="admin_show_all_manufacturers")
     */
    public function showAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Manufacturer p SET p.showInMenu = 1")->execute();

        return $this->redirectToRoute('admin_list_manufacturers', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_hide_all_manufacturers")
     */
    public function hideAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Manufacturer p SET p.showInMenu = 0")->execute();

        return $this->redirectToRoute('admin_list_manufacturers', $request->query->all());
    }
    
    /**
     * @Route("/remove/{id}", name="admin_remove_manufacturer")
     */
    public function removeAction($id, Request $request) {
        
        $manufacturer = $this->getDoctrine()->getRepository('AppBundle:Manufacturer')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($manufacturer);
        $em->flush();
        return $this->redirectToRoute('admin_list_manufacturers', $request->query->all());
        
    }

}
