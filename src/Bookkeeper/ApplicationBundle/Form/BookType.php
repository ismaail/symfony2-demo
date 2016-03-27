<?php

namespace Bookkeeper\ApplicationBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Symfony\Component\Form\AbstractType;

/**
 * Class BookType
 * @package Bookkeeper\ApplicationBundle\Form
 */
class BookType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('pages')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'application_book';
    }
}
