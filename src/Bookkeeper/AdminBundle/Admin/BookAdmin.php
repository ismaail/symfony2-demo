<?php

namespace Bookkeeper\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class BookAdmin
 * @package Bookkeeper\AdminBundle\Admin
 */
class BookAdmin extends Admin
{
    /**
     * @var \Bookkeeper\ApplicationBundle\Model\BookModel
     */
    private $bookModel;

    /**
     * Fields to be shown on create/edit forms
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', 'text', array('label' => 'Book Title'))
            ->add('description')
            ->add('pages')
        ;
    }

    /**
     * Fields to be shown on filter forms
     *
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
        ;
    }

    /**
     * Fields to be shown on lists
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('pages')
        ;
    }

    /**
     * Clear Book from cache after update
     *
     * @param \Bookkeeper\ApplicationBundle\Entity\Book $book
     */
    public function postUpdate($book)
    {
        $this->removeBookFromCache($book);
    }

    /**
     * Clear Book from cache after delete
     *
     * @param \Bookkeeper\ApplicationBundle\Entity\Book $book
     */
    public function postRemove($book)
    {
        $this->removeBookFromCache($book);
    }

    /**
     * @param \Bookkeeper\ApplicationBundle\Entity\Book $book
     */
    private function removeBookFromCache($book)
    {
        $this->getBookModel()->removeFromCache($book->getSlug());
    }

    /**
     * @return \Bookkeeper\ApplicationBundle\Model\BookModel
     */
    private function getBookModel()
    {
        if (is_null($this->bookModel)) {
            $this->bookModel = $this->getConfigurationPool()->getContainer()->get('book_model');
        }

        return $this->bookModel;
    }
}
