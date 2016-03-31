<?php

namespace Bookkeeper\ManagerBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bookkeeper\ApplicationBundle\Form\BookType;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\NoResultException;

/**
 * Class DefaultController
 * @package Bookkeeper\ManagerBundle\Controller
 */
class BookController extends Controller
{
    /**
     * @var \Bookkeeper\ApplicationBundle\Model\BookModel
     */
    protected $bookModel;

    /**
     * New book action
     *
     * Add new book action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $form = $this->createBookTypeForm(new Book());

        return $this->render('BookkeeperManagerBundle:Book:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create new book action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $book = new Book();
        $form = $this->createBookTypeForm($book);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getBookModel()->create($book);

            $this->get('session')->getFlashBag()->add('success', 'Book has been created.');

            return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);
        }

        $this->get('session')->getFlashBag()->add('error', 'Error creating a new book');

        return $this->render('BookkeeperManagerBundle:Book:new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit book action
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($slug)
    {
        try {
            $book = $this->getBookModel()->findBySlug($slug);
            $form = $this->createBookTypeForm($book, false);

            return $this->render('BookkeeperManagerBundle:Book:edit.html.twig', [
                'form' => $form->createView(),
                'book' => $book,
            ]);

        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Book not found");
        }
    }

    /**
     * Update book action
     *
     * @param Request $request
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, $slug)
    {
        try {
            $book = $this->getBookModel()->findBySlug($slug);
            /** @var Book $book */
            $book = $this->getBookModel()->merge($book);
            $form = $this->createBookTypeForm($book, false);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getBookModel()->update($book, $slug);

                $this->get('session')->getFlashBag()->add('success', 'Book has been updated.');
                return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);
            }

            $this->get('session')->getFlashBag()->add('error', 'Error updating the book');

            return $this->render('BookkeeperManagerBundle:Book:edit.html.twig', [
                'form' => $form->createView(),
                'book' => $book,
            ]);

        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Book not found");
        }
    }

    /**
     * @param Request $request
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $slug)
    {
        try {
            $book = $this->getBookModel()->findBySlug($slug);
            $form = $this->createBookDeleteForm($book);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getBookModel()->remove($book);

                $this->get('session')->getFlashBag()->add('success', 'Book has been deleted.');
                return $this->redirectToRoute('home');
            }

            $this->get('session')->getFlashBag()->add('error', 'Error deleting the book');

            return $this->redirectToRoute('book_show', ['slug' => $book->getSlug()]);

        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Book not found");
        }
    }

    /**
     * Create Book form
     *
     * @param Book $book
     * @param bool $isCreationForm
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createBookTypeForm(Book $book, $isCreationForm = true)
    {
        $formOptions = [
            'action' => ['url' => 'book_create', 'params' => []],
            'method' => 'POST',
            'label' => 'Create',
        ];

        if (! $isCreationForm) {
            $formOptions = [
                'action' => ['url' => 'book_update', 'params' => ['slug' => $book->getSlug()]],
                'method' => 'PUT',
                'label' => 'Update',
            ];
        }

        $form = $this->createForm(BookType::class, $book, [
            'action' => $this->generateUrl($formOptions['action']['url'], $formOptions['action']['params']),
            'method' => $formOptions['method'],
        ]);

        $form->add('submit', SubmitType::class, ['label' => $formOptions['label']]);

        return $form;
    }

    /**
     * @param Book $book
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createBookDeleteForm(Book $book)
    {
        $form = $this
            ->createFormBuilder()
            ->setAction($this->generateUrl('book_delete', ['slug' => $book->getSlug()]))
            ->setMethod('delete')
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm()
        ;

        return $form;
    }

    /**
     * Get book model
     *
     * @return \Bookkeeper\ApplicationBundle\Model\BookModel
     */
    protected function getBookModel()
    {
        if (! $this->bookModel) {
            $this->bookModel = $this->get('book_model');
        }

        return $this->bookModel;
    }
}
