<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Customer;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAware;

class LoadUserData extends ContainerAware implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        
        $userManager = $this->container->get('fos_user.user_manager');
        
        $userAdmin = $userManager->createUser();
        $userAdmin->setUsername('admin');
        $userAdmin->setPlainPassword('test');
        $userAdmin->setEmail('admin@example.com');
        $userAdmin->setEnabled(true);
        $userAdmin->setRoles(array('ROLE_SUPER_ADMIN'));

        $userManager->updateUser($userAdmin);
        
        $user = $userManager->createUser();
        $user->setUsername('user');
        $user->setPlainPassword('test');
        $user->setEmail('user@example.com');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_USER'));

        $userManager->updateUser($user);
        
        $customerUser = $userManager->createUser();
        $customerUser->setUsername('customer');
        $customerUser->setPlainPassword('test');
        $customerUser->setEmail('customer@example.com');
        $customerUser->setEnabled(true);
        $customerUser->setRoles(array('ROLE_CUSTOMER'));
        $customerUser->setCustomerNumbers(array("TEST001"));

        $userManager->updateUser($customerUser);
        
    }
}