<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Invoice;
use AppBundle\Service\InvoiceService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/invoice")
 */
class InvoiceController extends Controller {

    /**
     * @Route("/", name="invoice_index")
     */
    public function indexAction(Request $request) {
        
        $page = 1;
        $searchTerms = $request->get('searchTerms', null);
        
        if ($request->get('action') == 'clear') {
            $searchTerms = null;
        }
        
        return $this->render('AppBundle:Invoice:index.html.twig', array('pageOptions' => array(
            'page' => $page,
            'searchTerms' => $searchTerms
        )));
    }
    
    /**
     * @Route("/ajax-view/{id}", name="invoice_ajax_view", options={"expose": true})
     */
    public function ajaxViewAction($id) {
                
        $service = $this->get('app.invoice_service');
        
        $invoice = $service->get($id);

        $response = new Response();
        $engine = $this->container->get('templating');
        $response->setContent($engine->render('AppBundle:Invoice:view.html.twig', array('invoice' => $invoice)));
        return $response;
        
    }

    /**
     * @Route("/ajax-list", name="invoice_ajax_list", options={"expose": true})
     */
    public function ajaxListAction(Request $request) {

        $page = $request->get('page', 1);
        $perPage = 50;
        
        $user = $this->getUser();

        $service = $this->get('app.invoice_service');
        
        $offset = (($page - 1) * $perPage);
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            $invoices = $service->findByCustomerNumbers($user->getCustomerNumbers(), $offset, $perPage);
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $invoices = $service->findAll($offset, $perPage);
        }

        $response = new Response();
        $nextPage = "";
        $engine = $this->container->get('templating');
        if (!empty($invoices)) {
            $nextPage = $this->generateUrl('invoice_ajax_list', array(
                'page' => $page + 1
            ));
        }
        $response->setContent($engine->render('AppBundle:Invoice:list.html.twig', array('invoices' => $invoices, 'nextPage' => $nextPage)));
        return $response;
    }
    
    /**
     * @Route("/export", name="invoice_export")
     * @Template("AppBundle:Invoice:export.html.twig")
     */
    public function exportAction() {

        return array();
    }

}
