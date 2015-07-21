<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Weborder;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/weborders")
 */
class WebordersController extends Controller {

    /**
     * @Route("/", name="weborders_index")
     */
    public function indexAction(Request $request) {

        $page = 1;
        $searchTerms = $request->get('searchTerms', null);

        if ($request->get('action') == 'clear') {
            $searchTerms = null;
        }

        return $this->render('AppBundle:Weborders:index.html.twig', array('pageOptions' => array(
                        'page' => $page,
                        'searchTerms' => $searchTerms
        )));
    }

    /**
     * @Route("/ajax-view/{id}", name="weborders_ajax_view", options={"expose": true})
     */
    public function ajaxViewAction($id) {

        $service = $this->get('app.weborder_service');
        $invService = $this->get('app.invoice_service');
        $shipService = $this->get('app.shipment_service');
        $packService = $this->get('app.package_service');

        $weborder = $service->get($id);
        $invoice = $invService->get($id);
        $shipment = $shipService->get($id);
        $packages = $packService->findByOrderNumber($id);

        $response = new Response();
        $engine = $this->container->get('templating');
        $response->setContent($engine->render('AppBundle:Weborders:view.html.twig', array(
                    'weborder' => $weborder,
                    'shipment' => $shipment,
                    'invoice' => $invoice,
                    'packages' => $packages
        )));
        return $response;
    }

    /**
     * @Route("/ajax-list", name="weborders_ajax_list", options={"expose": true})
     */
    public function ajaxListAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms');
        $customerNumber = $request->get('customerNumber');
        $perPage = 50;

        $user = $this->getUser();

        $service = $this->get('app.weborder_service');

        $offset = (($page - 1) * $perPage);

        if ($this->get('security.authorization_checker')->isGranted('ROLE_CUSTOMER')) {
            if (!empty($customerNumber)) {
                if (!empty($searchTerms)) {
                    $weborders = $service->findByCustomerAndSearchTerms($customerNumber, $searchTerms, $offset, $perPage);
                } else {
                    $weborders = $service->findByCustomer($customerNumber, $offset, $perPage);
                }
            } else {
                if (!empty($searchTerms)) {
                    $weborders = $service->findByCustomerNumbersAndSearchTerms($user->getCustomerNumbers(), $searchTerms, $offset, $perPage);
                } else {
                    $weborders = $service->findByCustomerNumbers($user->getCustomerNumbers(), $offset, $perPage);
                }
            }
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if (!empty($searchTerms)) {
                $weborders = $service->findBySearchTerms($searchTerms, $offset, $perPage);
            } else {
                $weborders = $service->findAll($offset, $perPage);
            }
        }

        if ($request->isXmlHttpRequest()) {
            $response = new Response();
            $engine = $this->container->get('templating');
            if (!empty($weborders)) {
                $nextPage = $this->generateUrl('weborders_ajax_list', array(
                    'searchTerms' => $searchTerms,
                    'customerNumber' => $customerNumber,
                    'page' => $page + 1
                ));
                $response->setContent($engine->render('AppBundle:Weborders:list.html.twig', array('weborders' => $weborders, 'nextPage' => $nextPage)));
            } else {
                $response->setContent("<p>NO MORE RECORDS</p>");
            }
            return $response;
        } else {
            return $this->render('AppBundle:Weborders:list_test.html.twig', array('weborders' => $weborders));
        }
    }

    /**
     * @Route("/submit", name="weborders_submit")
     * @Template("AppBundle:Weborders:submit.html.twig")
     */
    public function submitAction(Request $request) {

        $weborder = new Weborder();

        $formBuilder = $this->createFormBuilder($weborder);

        $customerNumbers = $this->getUser()->getCustomerNumbers();

        if (sizeof($customerNumbers) > 1) {
            $options = array();
            foreach ($customerNumbers as $customerNumber) {
                $options[$customerNumber] = $customerNumber;
            }
            $formBuilder->add('customerNumber', 'choice', array('label' => 'Customer Account', 'choices' => $options));
        } elseif (sizeof($customerNumbers) > 0) {
            $weborder->setCustomerNumber($customerNumbers[0]);
        } else {
            $formBuilder->add('customerNumber', 'text');
        }

        $form = $formBuilder->add('reference1', 'text', array('label' => 'Customer Reference (PO#)', 'required' => false))
                ->add('reference2', 'text', array('label' => 'Customer Reference 2', 'required' => false))
                ->add('reference3', 'text', array('label' => 'Customer Reference 3', 'required' => false))
                ->add('shipToFirstName', 'text', array('label' => 'First Name', 'required' => false))
                ->add('shipToLastName', 'text', array('label' => 'Last Name', 'required' => true))
                ->add('shipToAddress1', 'text', array('label' => 'Address', 'required' => true))
                ->add('shipToAddress2', 'text', array('label' => 'Address (Cont.)', 'required' => false))
                ->add('shipToCity', 'text', array('label' => 'City', 'required' => true))
                ->add('shipToState', 'text', array('label' => 'State', 'required' => false))
                ->add('shipToZip', 'text', array('label' => 'Zip', 'required' => false))
                ->add('shipToCountry', 'country', array('label' => 'Country', 'required' => true, 'preferred_choices' => array('US')))
                ->add('shipToPhone', 'text', array('label' => 'Phone', 'required' => false))
                ->add('shipToEmail', 'email', array('label' => 'Email', 'required' => false))
                ->add('rush', 'checkbox', array('label' => 'Rush Order', 'required' => false))
                ->add('save', 'submit', array('label' => 'Submit Order'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            // this is where the order will be submitted to DistOne
            $weborder->setOrderNumber(rand(1000000, 9999999)); # just some test data

            $weborder->setOrderDate(new DateTime());

            $em = $this->getDoctrine()->getManager();

            $em->persist($weborder);
            $em->flush();

            return $this->redirectToRoute('weborders_index');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/cartSummary", name="weborders_cart_summary")
     * @Template("AppBundle:Weborders:cart_summary.html.twig")
     */
    public function cartSummaryAction() {

        $cartItems = $this->getUser()->getCartItems();

        return array('cartItems' => $cartItems);
    }

    /**
     * @Route("/import", name="weborders_import")
     * @Template("AppBundle:Weborders:import.html.twig")
     */
    public function importAction() {

        return array();
    }

    /**
     * @Route("/export", name="weborders_export")
     * @Template("AppBundle:Weborders:export.html.twig")
     */
    public function exportAction() {

        return array();
    }

}
