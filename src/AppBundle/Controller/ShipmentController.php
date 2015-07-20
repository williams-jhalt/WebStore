<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Shipment;
use AppBundle\Service\ShipmentService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        
        $shipment = $service->get($id);

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
        $perPage = 50;
        
        $user = $this->getUser();

        $service = $this->get('app.shipment_service');
        
        $offset = (($page - 1) * $perPage);
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            $shipments = $service->findByCustomerNumbers($user->getCustomerNumbers(), $offset, $perPage);
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $shipments = $service->findAll($offset, $perPage);
        }

        $response = new Response();
        $nextPage = "";
        $engine = $this->container->get('templating');
        if (!empty($shipments)) {
            $nextPage = $this->generateUrl('shipment_ajax_list', array(
                'page' => $page + 1
            ));
        }
        $response->setContent($engine->render('AppBundle:Shipment:list.html.twig', array('shipments' => $shipments, 'nextPage' => $nextPage)));
        return $response;
    }
    
    /**
     * @Route("/export", name="shipment_export")
     * @Template("AppBundle:Shipment:export.html.twig")
     */
    public function exportAction() {

        return array();
    }

}
