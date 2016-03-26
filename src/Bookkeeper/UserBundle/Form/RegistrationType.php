<?php

namespace Bookkeeper\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RegistrationType
 * @package Bookkeeper\UserBundle\Form
 */
class RegistrationType extends AbstractType
{
    /**
     * Build form elements
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text')
                ->add('password', 'repeated', array(
                    'type'            => 'password',
                    'invalid_message' => 'Passwords do not match'
                ))
                ->add('email', 'email');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bookkeeper\UserBundle\Entity\User',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'user_signup';
    }
}
