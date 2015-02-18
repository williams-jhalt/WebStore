<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/users")
 */
class UserController extends Controller {

    /**
     * @Route("/", name="admin_user_list")
     * @Template("AdminBundle:User:list.html.twig")
     */
    public function listUsersAction(Request $request) {

        $page = $request->get('page', 1);
        $searchTerms = $request->get('searchTerms', null);

        $repository = $this->getDoctrine()->getRepository("AppBundle:User");

        $qb = $repository->createQueryBuilder('u');

        if ($searchTerms !== null) {
            $qb->where('u.username LIKE :searchTerms')
                    ->setParameter('searchTerms', "%{$searchTerms}%");
        }

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $qb->getQuery(), $page, 25
        );

        return array('pagination' => $pagination, 'searchTerms' => $searchTerms);
    }

    /**
     * @Route("/new", name="admin_user_add")
     * @Template("AdminBundle:User:new.html.twig")
     */
    public function newUserAction(Request $request) {

        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();

        $form = $this->_createUserForm($user)
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => "Password fields must match",
                    'required' => true,
                    'first_options' => array('label' => "Password"),
                    'second_options' => array('label' => "Confirm Password")
                ))
                ->add('save', 'submit', array('label' => 'Create User'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->updateUser($user);

            return $this->redirectToRoute('admin_list_users');
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/edit/{id}", name="admin_user_edit")
     * @Template("AdminBundle:User:edit.html.twig")
     */
    public function editUserAction($id, Request $request) {

        $userManager = $this->get('fos_user.user_manager');

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $form = $this->_createUserForm($user)
                ->add('save', 'submit', array('label' => 'Update User'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->updateUser($user);

            return $this->redirectToRoute('admin_list_users');
        }

        return array('user' => $user, 'form' => $form->createView());
    }

    /**
     * @Route("/addCustomer/{id}", name="admin_user_add_customer")
     */
    public function addCustomerAction($id, Request $request) {

        $customerNumber = $request->request->get('customerNumber');

        $userManager = $this->get('fos_user.user_manager');

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $user->addCustomerNumber($customerNumber);

        $userManager->updateUser($user);

        return $this->redirectToRoute('admin_user_edit', array('id' => $id));
    }

    /**
     * @Route("/removeCustomer/{id}", name="admin_user_remove_customer")
     */
    public function removeCustomerAction($id, Request $request) {

        $customerNumber = $request->query->get('customerNumber');

        $userManager = $this->get('fos_user.user_manager');

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $user->removeCustomerNumber($customerNumber);

        $userManager->updateUser($user);

        return $this->redirectToRoute('admin_user_edit', array('id' => $id));
    }

    /**
     * @Route("/changePassword/{id}", name="admin_user_change_password")
     * @Template("AdminBundle:User:change_password.html.twig")
     */
    public function changePasswordAction($id, Request $request) {

        $userManager = $this->get('fos_user.user_manager');

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $form = $this->createFormBuilder($user)
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => "Password fields must match",
                    'required' => true,
                    'first_options' => array('label' => "Password"),
                    'second_options' => array('label' => "Confirm Password")
                ))
                ->add('save', 'submit', array('label' => 'Update Password'))
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userManager->updateUser($user);

            return $this->redirectToRoute('admin_list_users');
        }

        return array('form' => $form->createView());
    }

    private function _createUserForm(User $user) {
        $form = $this->createFormBuilder($user)
                ->add('username', 'text')
                ->add('email', 'text')
                ->add('enabled', 'checkbox', array(
                    'label' => 'Enabled',
                    'required' => false))
                ->add('admin', 'checkbox', array(
            'label' => 'Administrator',
            'required' => false));
        return $form;
    }

    /**
     * @Route("/remove/{id}", name="admin_user_remove")
     */
    public function removeAction($id, Request $request) {

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('admin_list_users', $request->query->all());
    }

}
