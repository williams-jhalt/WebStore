<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CartItem;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/cart")
 */
class CartController extends Controller {

    /**
     * @Route("/", name="cart_index")
     * @Template("AppBundle:Cart:index.html.twig")
     */
    public function indexAction() {

        $cartItems = $this->getUser()->getCartItems();

        return array('cartItems' => $cartItems);
    }

    /**
     * @Route("/add", name="cart_add")
     */
    public function addAction(Request $request) {

        $searchTerms = $request->get('searchTerms');
        $quantity = $request->get('quantity', 1);

        try {

            $product = $this->getDoctrine()->getRepository('AppBundle:Product')->createQueryBuilder('p')
                    ->where('p.sku = :sku')
                    ->setParameter('sku', $searchTerms)
                    ->getQuery()
                    ->getSingleResult();

            $cartItem = new CartItem();
            $cartItem->setUser($this->getUser());
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);

            $em = $this->getDoctrine()->getManager();

            $em->persist($cartItem);
            $em->flush();
        } catch (NonUniqueResultException $e) {
            return $this->redirectToRoute('cart_lookup', array('searchTerms' => $searchTerms));
        } catch (NoResultException $e) {
            if (sizeof($this->getDoctrine()->getRepository("AppBundle:Product")->findBySearchTerms($searchTerms)) > 0) {
                return $this->redirectToRoute('cart_lookup', array('searchTerms' => $searchTerms));
            }

            $request->getSession()->getFlashBag()->add('notices', "Item not found");
        } catch (UniqueConstraintViolationException $e) {
            $request->getSession()->getFlashBag()->add('notices', "Item already in cart");
        }

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/cart/update", name="cart_update"))
     */
    public function updateAction(Request $request) {

        $quantities = $request->request->get('quantity');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('AppBundle:CartItem');

        foreach ($quantities as $productId => $quantity) {

            $cartItem = $repository->find(array('product' => $productId, 'user' => $user->getId()));

            if ($quantity > 0) {
                $cartItem->setQuantity($quantity);
                $em->persist($cartItem);
            } else {

                $em->remove($cartItem);
            }
        }

        $em->flush();

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/remove/{id}", name="cart_remove")
     */
    public function removeAction($id) {

        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        $user = $this->getUser();

        $cartItem = $this->getDoctrine()->getRepository('AppBundle:CartItem')->find(array(
            'product' => $product->getId(), 'user' => $user->getId()));

        $em = $this->getDoctrine()->getManager();

        $em->remove($cartItem);
        $em->flush();

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/clear", name="cart_clear")
     */
    public function clearAction() {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        foreach ($user->getCartItems() as $cartItem) {

            $em->remove($cartItem);
        }

        $em->flush();

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Template("AppBundle:Cart:sidebar_display.html.twig")
     */
    public function sidebarDisplayAction() {

        $user = $this->getUser();

        if ($user) {

            $cartItems = $this->getUser()->getCartItems();
        }

        return array('cartItems' => $cartItems);
    }

    /**
     * @Route("/lookup", name="cart_lookup")
     * @Template("AppBundle:Cart:lookup.html.twig")
     */
    public function cartLookupAction(Request $request) {

        $searchTerms = $request->get('searchTerms');

        $products = $this->getDoctrine()->getRepository("AppBundle:Product")->findBySearchTerms($searchTerms);

        return array('products' => $products);
    }

    /**
     * @Route("/copyAndPaste", name="cart_copy_and_paste")
     */
    public function copyAndPasteAction(Request $request) {

        $input = $request->request->get('input');

        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');

        $lines = explode("\n", $input);

        foreach ($lines as $line) {

            $t = preg_split("/[\s,]+/", $line);

            if (sizeof($t) > 1) {

                $sku = $t[0];
                $qty = $t[1];
            } else {

                $sku = $t[0];
                $qty = 1;
            }

            try {

                $product = $repository->findOneBySku($sku);

                if ($product) {

                    $cartItem = new CartItem();
                    $cartItem->setUser($this->getUser());
                    $cartItem->setProduct($product);
                    $cartItem->setQuantity($qty);

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($cartItem);
                    $em->flush();
                }
            } catch (Exception $e) {
                // don't bother
            }
        }

        return $this->redirectToRoute('cart_index');
    }

}
