<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction() {           
        return $this->render('AppBundle:Default:index.html.twig');
    }
    
    /**
     * @Route("/search", name="default_search")
     */
    public function searchAction(Request $request) {
        
        $searchTerms = $request->get('searchTerms');
        
        $productService = $this->get('app.product_service');
        $orderService = $this->get('app.order_service');
        
        $products = $productService->findBySearchTerms($searchTerms, 0, 10);
        $orders = $orderService->findBySearchTerms($searchTerms, 0, 10);
        
        return $this->render('AppBundle:Default:search.html.twig', array(
            'products' => $products,
            'orders' => $orders,
            'searchTerms' => $searchTerms
        ));
        
    }

    /**
     * @Route("/display_invoice", name="display_invoice")
     */
    public function displayInvoiceAction(Request $request) {

        $response = new Response();

        $orderNumber = $request->get('orderNumber');
        $sequence = $request->get('sequence');

        $orderService = $this->get('app.order_service');

        $order = $orderService->find($orderNumber);

        $user = $this->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            if ((array_search($order->getCustomerNumber(), $user->getCustomerNumbers())) === FALSE) {
                $response->setStatusCode(403);
                return $response;
            }
        }

        $service = $this->get('app.erp_connector_service');

        $res = $service->getPdf('invoice', $orderNumber, $sequence);

        $response->headers->set('Content-Type', $res->encoding);

        $response->setContent(base64_decode($res->document));

        return $response;
    }
    
    /**
     * @Route("/display_product_image", name="display_product_image")
     */
    public function displayProductImage(Request $request) {
        
        $path = $request->get('path');
        $width = $request->get('width');
        $height = $request->get('height');
        
        $response = new Response();
        
        $data = $this->get('image.handling')->open($path)
                ->resize($width, $height)
                ->get('jpg');
        
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent($data);
        
        return $response;
        
    }

}
