<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Twig_Extension;
use Twig_SimpleFunction;

class CartCheckExtension extends Twig_Extension {
    
    private $security;
    private $em;
    
    public function __construct(TokenStorage $security, EntityManager $em) {
        $this->security = $security;
        $this->em = $em;
    }

    public function getFunctions() {
        return array(
            new Twig_SimpleFunction('cartCheck', array($this, 'cartCheck')),
        );
    }

    public function cartCheck(Product $product) {
        
        $inCart = false;
        
        foreach ($this->security->getToken()->getUser()->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() == $product) {
                $inCart = true;
                break;
            }
        }
        
        return $inCart;
        
    }

    public function getName() {
        return 'app';
    }

}
