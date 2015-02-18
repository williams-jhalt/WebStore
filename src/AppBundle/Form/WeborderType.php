<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WeborderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderNumber')
            ->add('reference1')
            ->add('reference2')
            ->add('reference3')
            ->add('shipToFirstName')
            ->add('shipToLastName')
            ->add('shipToAddress1')
            ->add('shipToAddress2')
            ->add('shipToCity')
            ->add('shipToState')
            ->add('shipToZip')
            ->add('shipToCountry')
            ->add('shipToPhone')
            ->add('shipToEmail')
            ->add('customerNumber')
            ->add('orderDate')
            ->add('rush')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Weborder'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_weborder';
    }
}
