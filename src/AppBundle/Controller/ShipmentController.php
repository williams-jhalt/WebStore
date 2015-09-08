<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shipment")
 */
class ShipmentController extends Controller {

    /**
     * @Route("/", name="shipment_index")
     */
    public function indexAction(Request $request) {
        
        $page = 1;
        $searchTerms = $request->get('searchTerms', null);
        
        if ($request->get('action') == 'clear') {
            $searchTerms = null;
        }
        
        return $this->render('AppBundle:Shipment:index.html.twig', array('pageOptions' => array(
            'page' => $page,
            'searchTerms' => $searchTerms
        )));
    }
    
    /**
     * @Route("/ajax-view/{id}", name="shipment_ajax_view", options={"expose": true})
     */
    public function ajaxViewAction($id) {
                
        $service = $this->get('app.shipment_service');
        
        $shipment = $service->find($id);

        $response = new Response();
        $engine = $this->container->get('templating');
        $response->setContent($engine->render('AppBundle:Shipment:view.html.twig', array('shipment' => $shipment)));
        return $response;
        
    }

    /**
     * @Route("/ajax-list", name="shipment_ajax_list", options={"expose": true})
     */
    public function ajaxListAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms');
        $customerNumber = $request->get('customerNumber');
        $perPage = 50;

        $user = $this->getUser();

        $service = $this->get('app.shipment_service');

        $offset = (($page - 1) * $perPage);

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            if (!empty($customerNumber)) {
                if (!empty($searchTerms)) {
                    $shipments = $service->findByCustomerAndSearchTerms($customerNumber, $searchTerms, $offset, $perPage);
                } else {
                    $shipments = $service->findByCustomer($customerNumber, $offset, $perPage);
                }
            } else {
                if (!empty($searchTerms)) {
                    $shipments = $service->findByCustomerNumbersAndSearchTerms($user->getCustomerNumbers(), $searchTerms, $offset, $perPage);
                } else {
                    $shipments = $service->findByCustomerNumbers($user->getCustomerNumbers(), $offset, $perPage);
                }
            }
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!empty($searchTerms)) {
                $shipments = $service->findBySearchTerms($searchTerms, $offset, $perPage);
            } else {
                $shipments = $service->findAll($offset, $perPage);
            }
        }

        if ($request->isXmlHttpRequest()) {
            $response = new Response();
            $engine = $this->container->get('templating');
            if (!empty($shipments)) {
                $nextPage = $this->generateUrl('shipment_ajax_list', array(
                    'searchTerms' => $searchTerms,
                    'customerNumber' => $customerNumber,
                    'page' => $page + 1
                ));
                $response->setContent($engine->render('AppBundle:Shipment:list.html.twig', array('shipments' => $shipments, 'nextPage' => $nextPage)));
            } else {
                $response->setContent("<p>NO MORE RECORDS</p>");
            }
            return $response;
        } else {
            return $this->render('AppBundle:Shipment:list_test.html.twig', array('shipments' => $shipments));
        }
    }
    
    /**
     * @Route("/export", name="shipment_export")
     * @Template("AppBundle:Shipment:export.html.twig")
     */
    public function exportAction() {

        return array();
    }

}
