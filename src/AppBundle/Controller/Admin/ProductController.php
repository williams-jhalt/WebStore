<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttachment;
use AppBundle\Form\ProductDetailType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/products")
 */
class ProductController extends Controller {

    /**
     * @Route("/", name="admin_product_list", options={"expose": true})
     */
    public function listProductsAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);
        $perPage = 100;

        if ($request->isXmlHttpRequest()) {

            $service = $this->get('app.product_service');

            if (!empty($searchTerms)) {
                $products = $service->findBySearchTerms($searchTerms, (($page - 1) * $perPage), $perPage);
            } else {
                $products = $service->findAll((($page - 1) * $perPage), $perPage);
            }

            $response = new Response();
            $engine = $this->container->get('templating');
            $response->setContent($engine->render('AppBundle:Admin/Product:list.html.twig', array('products' => $products)));
            return $response;
            
        } else {
            
            return $this->render('AppBundle:Admin/Product:index.html.twig', array('pageOptions' => array('page' => $page, 'searchTerms' => $searchTerms)));
            
        }
        
    }

    /**
     * @Route("/edit/{id}", name="admin_product_edit")
     * @Template("AppBundle:Admin/Product:edit.html.twig")
     */
    public function editProductAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);

        $form = $this->createFormBuilder($product)
                ->add('name', 'text')
                ->add('releaseDate', 'date', array('widget' => 'single_text', 'disabled' => true))
                ->add('stockQuantity', 'integer', array('disabled' => true))
                ->add('price', 'money', array('currency' => 'USD', 'disabled' => true))
                ->add('barcode', 'text', array('disabled' => true))
                ->add('manufacturer', 'entity', array(
                    'class' => 'AppBundle:Manufacturer',
                    'property' => 'name'
                ))
                ->add('productType', 'entity', array(
                    'class' => 'AppBundle:ProductType',
                    'property' => 'name'
                ))
                ->add('productDetail', new ProductDetailType())
                ->add('save', 'submit', array('label' => 'Update Product'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_product_list', $request->query->all());
        }

        $attachment = new ProductAttachment();
        $attachment->setProduct($product);

        $attachmentForm = $this->_getProductAttachmentForm($attachment);

        return array('product' => $product, 'form' => $form->createView(), 'attachment_form' => $attachmentForm->createView());
    }

    /**
     * @Route("/add", name="admin_product_add")
     * @Template("AppBundle:Admin/Product:add.html.twig")
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

            return $this->redirectToRoute('admin_product_list', $request->query->all());
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import_details", name="admin_product_import_details")
     * @Template("AppBundle:Admin/Product:import_product_details.html.twig")
     */
    public function importProductDetailsAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $filename = $form['importFile']->getData()->move(sys_get_temp_dir(), "import_products.csv")->getRealPath();

            $file = new SplFileObject($filename, "r");

            $service = $this->get('app.product_service');

            $service->importDetailsFromCSV($file, array(
                'sku' => 0,
                'package_height' => 1,
                'package_length' => 2,
                'package_width' => 3,
                'package_weight' => 4,
                'color' => 5,
                'material' => 6
                    ), true);

            return $this->redirectToRoute('admin_product_list');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import_descriptions", name="admin_product_import_descriptions")
     * @Template("AppBundle:Admin/Product:import_product_descriptions.html.twig")
     */
    public function importProductDescriptionsAction(Request $request) {

        $form = $this->createFormBuilder()
                ->add('importFile', 'file')
                ->add('upload', 'submit', array('label' => 'Upload File'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $filename = $form['importFile']->getData()->move(sys_get_temp_dir(), "import_products.xml")->getRealPath();

            $file = new SplFileObject($filename, "r");

            $service = $this->get('app.product_service');

            $service->importDescriptionsFromXML($file);

            return $this->redirectToRoute('admin_product_list');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/makePrimaryAttachment/{id}", name="admin_product_make_primary_attachment", options={"expose": true})
     */
    public function makePrimaryAttachemntAction($id) {

        $attachment = $this->getDoctrine()->getRepository('AppBundle:ProductAttachment')->find($id);

        $attachment->setPrimaryAttachment(true);

        $em = $this->getDoctrine()->getManager();

        $em->createQuery("UPDATE AppBundle:ProductAttachment p SET p.primaryAttachment = 0 WHERE p.product = :product")
                ->setParameter('product', $attachment->getProduct())
                ->execute();

        $em->persist($attachment);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/edit/{id}/uploadProductAttachment", name="admin_product_upload_product_attachment")
     */
    public function uploadProductAttachmentAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);

        $attachment = new ProductAttachment();
        $attachment->setProduct($product);

        $form = $this->_getProductAttachmentForm($attachment);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $attachment->upload();

            $em->persist($attachment);
            $em->flush();
        }

        return $this->redirectToRoute('admin_product_edit', array('id' => $id));
    }

    /**
     * @Route("/import", name="admin_product_import")
     * @Template("AppBundle:Admin/Product:import.html.twig")
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

            return $this->redirectToRoute('admin_product_list');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/updateShown/{id}", name="admin_product_toggle_shown", options={"expose": true})
     */
    public function updateShownAction($id) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $product->setShown(!$product->getShown());

        $em = $this->getDoctrine()->getManager();

        $em->persist($product);
        $em->flush();

        $response = array('code' => 100, 'success' => true);

        return new Response(json_encode($response));
    }

    /**
     * @Route("/showAll", name="admin_product_show_all")
     */
    public function showAllProductsAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Product p SET p.shown = 1")->execute();

        return $this->redirectToRoute('admin_product_list', $request->query->all());
    }

    /**
     * @Route("/hideAll", name="admin_product_hide_all")
     */
    public function hideAllProductsAction(Request $request) {

        $this->getDoctrine()->getManager()->createQuery("UPDATE AppBundle:Product p SET p.shown = 0")->execute();

        return $this->redirectToRoute('admin_product_list', $request->query->all());
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
     * @Route("/remove/{id}", name="admin_product_remove")
     */
    public function removeAction($id, Request $request) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('admin_product_list', $request->query->all());
    }

    /**
     * @Route("/removeAttachment/{id}", name="admin_product_remove_product_attachment")
     */
    public function removeProductAttachmentAction($id) {

        $attachment = $this->getDoctrine()->getRepository('AppBundle:ProductAttachment')->find($id);

        $productId = $attachment->getProduct()->getId();

        $em = $this->getDoctrine()->getManager();

        $em->remove($attachment);
        $em->flush();

        return $this->redirectToRoute('admin_product_edit', array('id' => $productId));
    }

    /**
     * @Route("/prepareSync", name="admin_product_prepare_synchronize")
     * @Template("AppBundle:Admin/Product:synchronize.html.twig")
     */
    public function prepareSynchronizeAction() {
        $service = $this->get('app.product_service');
        $changes = $service->prepareSynchronizeWithErp();
        $this->addFlash('changes', $changes);
        return array('added' => $changes['added'], 'removed' => $changes['removed']);
    }

    /**
     * @Route("/sync", name="admin_product_synchronize")
     */
    public function synchronizeAction(Request $request) {
        $service = $this->get('app.product_service');
        $changes = $service->prepareSynchronizeWithErp();
        $service->synchronizeWithErp($changes['added'], $changes['removed']);
        return $this->redirectToRoute('admin_product_list');
    }

    private function _getProductAttachmentForm(ProductAttachment $attachment) {

        $form = $this->createFormBuilder($attachment)
                ->setAction($this->generateUrl('admin_product_upload_product_attachment', array('id' => $attachment->getProduct()->getId())))
                ->add('file', 'file')
                ->add('submit', 'submit', array('label' => "Upload Attachment"))
                ->getForm();

        return $form;
    }

}
