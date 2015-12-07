<?php

namespace AppBundle\Controller\Api;

use SoapServer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WSDL\WSDLCreator;

/**
 * @Route("/api")
 */
class DefaultController extends Controller {

    /**
     * @Route("/soap.wsdl", name="api_soap_wsdl")
     */
    public function wsdlAction() {
        $wsdl = new WSDLCreator('AppBundle\Service\SoapService', $this->generateUrl('api_soap_endpoint', array(), true));
        $wsdl->setNamespace("http://williamstradingco.com/");
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        ob_start();
        $wsdl->renderWSDL();
        $response->setContent(ob_get_clean());

        return $response;
    }

    /**
     * @Route("/soap", name="api_soap_endpoint")
     */
    public function soapEndpointAction() {

        $server = new SoapServer(null, array('uri' => $this->generateUrl('api_soap_wsdl', array(), true)));
        
        $server->setObject($this->get('api.soap_service'));

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        ob_start();
        $server->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }

}
