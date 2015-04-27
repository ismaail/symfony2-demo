<?php

namespace Bookkeeper\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookkeeper\ApplicationBundle\Exception\ApplicationException;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Bookkeeper\ApplicationBundle\Form\BookType;
use Doctrine\ORM\NoResultException;

/**
 * Class DefaultController
 * @package Bookkeeper\ApplicationBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @var \Bookkeeper\ApplicationBundle\Model\BookModel
     */
    protected $bookModel;

    /**
     * List all books
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $booksParams = $this->container->getParameter('books');

        $books = $this->getBookModel()->getBooks($request->query->get('page', 1), $booksParams['pagination']['limit']);

        return $this->render('BookkeeperApplicationBundle:Default:index.html.twig', array(
            'books' => $books,
        ));
    }

    /**
     * Show book details
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($slug)
    {
        try {
            $book = $this->getBookModel()->getBookBySlug($slug);

            return $this->render('BookkeeperApplicationBundle:Default:show.html.twig', array(
                'book' => $book,
            ));

        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Book not found");
        }
    }

    /**
     * Add new book action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $form = $this->createBookTypeForm(new Book());

        return $this->render('BookkeeperApplicationBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
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
            $this->getBookModel()->createNewBook($book);
            $this->get('session')->getFlashBag()->add('success', 'Book has been created.');

            return $this->redirect($this->generateUrl('home'), 201);
        }

        $this->get('session')->getFlashBag()->add('error', 'Error creating a new book');

        return $this->render('BookkeeperApplicationBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit book
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($slug)
    {
        try {
            $book = $this->getBookModel()->getBookBySlug($slug);
            $form = $this->createBookTypeForm($book, false);

            return $this->render('BookkeeperApplicationBundle:Default:edit.html.twig', array(
                'form' => $form->createView(),
                'book' => $book,
            ));

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
    public function updateAction(Request $request, $slug)
    {
        try {
            $book = $this->getBookModel()->getBookBySlug($slug);
            $form = $this->createBookTypeForm($book, false);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Book has been created.');
                return $this->redirect($this->generateUrl('book_show', array('slug' => $book->getSlug())));
            }

            $this->get('session')->getFlashBag()->add('error', 'Error updating the book');

            return $this->render('BookkeeperApplicationBundle:Default:edit.html.twig', array(
                'form' => $form->createView(),
                'book' => $book,
            ));

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
        $formOptions = array(
            'action'  => $isCreationForm
                         ? array('url' => 'book_create', 'params' => array())
                         : array('url' => 'book_update', 'params' => array('slug' => $book->getSlug())),
            'method'  => $isCreationForm ? 'POST'   : 'PUT',
            'label'   => $isCreationForm ? 'Create' : 'Update',
        );

        $form = $this->createForm(new BookType(), $book, array(
            'action' => $this->generateUrl($formOptions['action']['url'], $formOptions['action']['params']),
            'method' => $formOptions['method'],
        ));

        $form->add('submit', 'submit', array('label' => $formOptions['label']));

        return $form;
    }

    /**
     * Send email message
     *
     * @param string $subject
     * @param string $body
     * @param string $to
     *
     * @throws ApplicationException
     */
    protected function sendMessage($subject, $body, $to)
    {
        $emailParams = $this->container->getParameter('email');

        if (! isset($emailParams['address'])) {
            throw new ApplicationException("Email sender address param no defined");
        }

        if (! isset($emailParams['name'])) {
            throw new ApplicationException("Email sender name param no defined");
        }

        /** @var \Swift_Mailer $mailer */
        $mailer  = $this->get('mailer');
        /** @var \Swift_Message $message */
        $message = $mailer->createMessage();
        $message->setSubject($subject)
                ->setFrom($emailParams['address'], $emailParams['name'])
                ->setTo($to)
                ->setBody($body, 'text/html');

        $mailer->send($message);
    }

    /**
     * Get book model
     *
     * @return \Bookkeeper\ApplicationBundle\Model\BookModel $bookModel
     */
    protected function getBookModel()
    {
        if (! $this->bookModel) {
            $this->bookModel = $this->get('book_model');
        }

        return $this->bookModel;
    }
}
