<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/categories")
 */
class AdminCategoryController extends Controller {

    /**
     * @Route("/", name="admin_list_categories")
     * @Template("admin/categories/list.html.twig")
     */
    public function listCategoriesAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);

        $repository = $this->getDoctrine()->getRepository("AppBundle:Category");

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
     * @Route("/edit/{id}", name="admin_edit_category")
     * @Template("admin/categories/edit.html.twig")
     */
    public function editCategoryAction($id, Request $request) {

        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);

        $form = $this->createFormBuilder($category)
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Update Category'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_list_categories', $request->query->all());
        }

        return array('category' => $category, 'form' => $form->createView());
    }

    /**
     * @Route("/add", name="admin_add_category")
     * @Template("admin/categories/add.html.twig")
     */
    public function addCategoryAction(Request $request) {

        $category = new Category();

        $form = $this->createFormBuilder($category)
                ->add('code', 'text')
                ->add('name', 'text')
                ->add('save', 'submit', array('label' => 'Add Category'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_list_categories', $request->query->all());
        }

        return array('category' => $category, 'form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_import_categories")
     * @Template("admin/categories/import.html.twig")
     */
    public function importCategoriesAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $file = $form['importFile']->getData()->openFile('r');
            
            $categoryService = $this->get('app.category_service');
            
            $categoryService->importFromCSV($file, array(
                'code' => 0,
                'name' => 1,
                'parent' => 2
            ), true);

            return $this->redirectToRoute('admin_list_categories');
        }

        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/updateShowInMenu", name="admin_categories_toggle_show_in_menu", options={"expose": true})
     */
    public function updateShowInMenuAction(Request $request) {
        
        $id = $request->request->get('id');
        
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        $category->setShowInMenu(!$category->getShowInMenu());
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($category);
        $em->flush();
        
        $response = array('code' => 100, 'success' => true);
        
        return new Response(json_encode($response));
        
    }

    /**
     * @Route("/showAll", name="admin_show_all_categories")
     */
    public function showAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Category p SET p.showInMenu = 1")->execute();

        return $this->redirectToRoute('admin_list_categories', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_hide_all_categories")
     */
    public function hideAllAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Category p SET p.showInMenu = 0")->execute();

        return $this->redirectToRoute('admin_list_categories', $request->query->all());
    }
    
    /**
     * @Route("/remove/{id}", name="admin_remove_category")
     */
    public function removeAction($id, Request $request) {
        
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        return $this->redirectToRoute('admin_list_categories', $request->query->all());
        
    }

}
