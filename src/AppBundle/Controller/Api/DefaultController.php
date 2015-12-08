<?php

namespace AppBundle\Controller\Api;

use SoapServer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WSDL\DocumentLiteralWrapper;
use WSDL\WSDLCreator;
use WSDL\XML\Styles\DocumentLiteralWrapped;

/**
 * @Route("/api")
 */
class DefaultController extends Controller {

    /**
     * @Route("/soap.wsdl", name="api_soap_wsdl")
     */
    public function wsdlAction() {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        $response->setContent(file_get_contents($this->_generateWsdl()));
        return $response;
    }

    /**
     * @Route("/soap", name="api_soap_endpoint")
     */
    public function soapEndpointAction() {

        $server = new SoapServer($this->_generateWsdl(), array(
            'style' => SOAP_DOCUMENT,
            'use' => SOAP_LITERAL,
        ));

        $server->setObject(new DocumentLiteralWrapper($this->get('api.soap_service')));

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        ob_start();
        $server->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }

    private function _generateWsdl() {
        $filename = $this->get('kernel')->getRootDir() . '/soap.wsdl';
        if (!file_exists($filename)) {
            $wsdl = new WSDLCreator('AppBundle\Service\SoapService', $this->generateUrl('api_soap_endpoint', array(), true));
            $wsdl->setNamespace("http://williamstradingco.com/");
            $wsdl->setBindingStyle(new DocumentLiteralWrapped());
            ob_start();
            $wsdl->renderWSDL();
            file_put_contents($filename, ob_get_clean());
        }
        return $filename;
    }

}
