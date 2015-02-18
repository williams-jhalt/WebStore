<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/account")
 */
class AccountController extends Controller {

    /**
     * @Route("/", name="account_index")
     */
    public function indexAction() {
        return $this->render('AppBundle:Account:index.html.twig');
    }

}
