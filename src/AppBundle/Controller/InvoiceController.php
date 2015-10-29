<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Invoice;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
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
                        'searchTerms' => $searchTerms,
                        'open' => null
        )));
    }

    /**
     * @Route("/ajax-view/{id}", name="invoice_ajax_view", options={"expose": true})
     */
    public function ajaxViewAction($id) {

        $response = new Response();

        $service = $this->get('app.invoice_service');

        $invoice = $service->find($id);

        $user = $this->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            if ((array_search($invoice->getCustomerNumber(), $user->getCustomerNumbers())) === FALSE) {
                $response->setStatusCode(403);
                return $response;
            }
        }

        $engine = $this->container->get('templating');
        $response->setContent($engine->render('AppBundle:Invoice:view.html.twig', array(
                    'invoice' => $invoice
        )));
        return $response;
    }

    /**
     * @Route("/ajax-list", name="invoice_ajax_list", options={"expose": true})
     */
    public function ajaxListAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);
        $customerNumber = $request->get('customerNumber');
        $consolidated = $request->get('consolidated', false);
        $perPage = 25;

        $user = $this->getUser();

        $service = $this->get('app.invoice_service');

        $offset = (($page - 1) * $perPage);

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {

            $params['customer_numbers'] = $user->getCustomerNumbers();
        }

        $params['search_terms'] = $searchTerms;

        if ($consolidated !== null) {
            $params['consolidated'] = (boolean) $consolidated;
        }

        $invoices = $service->findBySearchOptions($params, $offset, $perPage);

        if ($request->isXmlHttpRequest()) {
            $response = new Response();
            $engine = $this->container->get('templating');
            if (!empty($invoices)) {
                $nextPage = $this->generateUrl('invoice_ajax_list', array(
                    'searchTerms' => $searchTerms,
                    'customerNumber' => $customerNumber,
                    'consolidated' => $consolidated,
                    'page' => $page + 1
                ));
                $response->setContent($engine->render('AppBundle:Invoice:list.html.twig', array('invoices' => $invoices, 'nextPage' => $nextPage)));
            } else {
                $response->setContent("<p>NO MORE RECORDS</p>");
            }
            return $response;
        } else {
            return $this->render('AppBundle:Invoice:list_test.html.twig', array('invoices' => $invoices));
        }
    }

    /**
     * @Route("/export", name="invoice_export")
     * @Template("AppBundle:Invoices:export.html.twig")
     */
    public function exportAction() {

        return array();
    }

}
