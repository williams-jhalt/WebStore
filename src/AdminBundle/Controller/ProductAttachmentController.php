<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\ProductAttachment;
use AppBundle\Entity\ProductDetail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/product_attachments")
 */
class ProductAttachmentController extends Controller {

    /**
     * @Route("/", name="admin_product_attachment_list")
     * @Template("AdminBundle:ProductAttachment:list.html.twig")
     */
    public function listProductAttachmentsAction(Request $request) {

        $page = $request->get('page', 1);

        $repository = $this->getDoctrine()->getRepository("AppBundle:ProductAttachment");

        $qb = $repository->createQueryBuilder('p');

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 10
        );

        return array('pagination' => $pagination);
    }

    /**
     * @Route("/edit/{id}", name="admin_product_attachment_edit")
     * @Template("AdminBundle:ProductAttachment:edit.html.twig")
     */
    public function editProductAttachmentAction($id, Request $request) {

        $productAttachment = $this->getDoctrine()->getRepository('AppBundle:ProductAttachment')->find($id);

        $form = $this->createFormBuilder($productAttachment)
                ->add('explicit', 'checkbox', array('required' => false))
                ->add('primaryAttachment', 'checkbox', array('required' => false))
                ->add('save', 'submit', array('label' => 'Update Attachment'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($productAttachment);
            $em->flush();

            return $this->redirectToRoute('admin_product_attachment_list', $request->query->all());
        }

        return array('productAttachment' => $productAttachment, 'form' => $form->createView());
    }

    /**
     * @Route("/add", name="admin_product_attachment_add")
     * @Template("AdminBundle:ProductAttachment:add.html.twig")
     */
    public function addProductAttachmentAction(Request $request) {

        $attachment = new ProductAttachment();

        $form = $this->createFormBuilder($attachment)
                ->add('file', 'file', array('mapped' => false))
                ->add('sku', 'text', array('mapped' => false))
                ->add('explicit', 'checkbox', array('required' => false))
                ->add('save', 'submit', array('label' => 'Add Product Attachment'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $product = $em->getRepository('AppBundle:Product')->findOneBySku($form->get('sku')->getData());

            $attachment->setProduct($product);
            
            $attachment->upload();

            $em->persist($attachment);
            $em->flush();

            return $this->redirectToRoute('admin_product_attachment_list', $request->query->all());
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_product_attachment_import")
     * @Template("AdminBundle:ProductAttachment:import.html.twig")
     */
    public function importProductAttachmentsAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $filename = $form['importFile']->getData()->move(sys_get_temp_dir(), "import_products.csv")->getRealPath();


            $file = new SplFileObject($filename, "r");

            $service = $this->get('app.product_attachment_service');

            $service->importFromCSV($file, array(
                'sku' => 0,
                'filename' => 2
                    ), true);

            return $this->redirectToRoute('admin_product_attachment_list');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/remove/{id}", name="admin_product_attachment_remove")
     */
    public function removeAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:ProductAttachment')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('admin_product_attachment_list', $request->query->all());
    }

    /**
     * @Route("/toggle_explicit/{id}", name="admin_product_attachment_toggle_explicit", options={"expose": true})
     */
    public function toggleExplicitAction($id) {

        $attachment = $this->getDoctrine()->getRepository('AppBundle:ProductAttachment')->find($id);
        $attachment->setExplicit(!$attachment->getExplicit());

        $em = $this->getDoctrine()->getManager();

        $em->persist($attachment);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
        
    }

}
