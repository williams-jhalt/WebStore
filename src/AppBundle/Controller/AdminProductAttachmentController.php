<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProductAttachment;
use AppBundle\Entity\ProductDetail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/product_attachments")
 */
class AdminProductAttachmentController extends Controller {

    /**
     * @Route("/", name="admin_list_product_attachments")
     * @Template("admin/product_attachments/list.html.twig")
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
     * @Route("/edit/{id}", name="admin_edit_product_attachment")
     * @Template("admin/product_attachments/edit.html.twig")
     */
    public function editProductAttachmentAction($id, Request $request) {

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
     * @Route("/add", name="admin_add_product_attachment")
     * @Template("admin/product_attachments/add.html.twig")
     */
    public function addProductAttachmentAction(Request $request) {

        $attachment = new ProductAttachment();

        $form = $this->createFormBuilder($attachment)
                ->add('file', 'file', array('mapped' => false))
                ->add('sku', 'text', array('mapped' => false))
                ->add('explicit', 'checkbox')
                ->add('save', 'submit', array('label' => 'Add Product Attachment'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $product = $em->getRepository('AppBundle:Product')->findOneBySku($form->get('sku')->getData());

            $attachment->setProduct($product);


            $em->persist($attachment);
            $em->flush();

            return $this->redirectToRoute('admin_list_product_attachments', $request->query->all());
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import", name="admin_import_product_attachments")
     * @Template("admin/product_attachments/import.html.twig")
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

            return $this->redirectToRoute('admin_list_products');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/remove/{id}", name="admin_remove_product_attachment")
     */
    public function removeAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('admin_list_products', $request->query->all());
    }

    /**
     * @Route("/toggle_explicit/{id}", name="admin_product_attachments_toggle_explicit")
     */
    public function toggleExplicitAction($id) {
        
    }

}
