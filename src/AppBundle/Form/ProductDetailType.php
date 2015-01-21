<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductDetailType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('textDescription', 'textarea', array('label' => "Text Description", 'required' => false))
                ->add('htmlDescription', 'textarea', array('label' => "HTML Description", 'required' => false))
                ->add('productHeight', 'text', array('label' => "Product Height", 'required' => false))
                ->add('productLength', 'text', array('label' => "Product Length", 'required' => false))
                ->add('productWidth', 'text', array('label' => "Product Width", 'required' => false))
                ->add('productWeight', 'text', array('label' => "Product Weight", 'required' => false))
                ->add('packageHeight', 'text', array('label' => "Package Height", 'required' => false))
                ->add('packageLength', 'text', array('label' => "Package Length", 'required' => false))
                ->add('packageWidth', 'text', array('label' => "Package Width", 'required' => false))
                ->add('packageWeight', 'text', array('label' => "Package Weight", 'required' => false))
                ->add('color', 'text', array('label' => "Color", 'required' => false))
                ->add('material', 'text', array('label' => "Material", 'required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductDetail',
        ));
    }

    public function getName() {
        return 'productDetail';
    }

}
