<?php

namespace Bookkeeper\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookkeeper\ApplicationBundle\Exception\ApplicationException;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Bookkeeper\ApplicationBundle\Form\BookType;

/**
 * Class DefaultController
 * @package Bookkeeper\ApplicationBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('BookkeeperApplicationBundle:Default:index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        return $this->render('BookkeeperApplicationBundle:Default:new.html.twig', array(
            'form' => $this->createBookTypeForm(new Book())->createView(),
        ));
    }

    /**
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
            $this->createNewBook($book);
            $this->get('session')->getFlashBag()->add('success', 'Book has been created.');

            return $this->redirect($this->generateUrl('book_new'), 201);
        }

        $this->get('session')->getFlashBag()->add('error', 'Error creating a new book');

        return $this->render('BookkeeperApplicationBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Get all books
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\Book[]
     */
    protected function getBooks()
    {
        $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
        $qb->select('b')
           ->from('Bookkeeper\ApplicationBundle\Entity\Book', 'b');

        return $qb->getQuery()->getResult();
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
        $form = $this->createForm(new BookType(), $book, array(
            'action' => $this->generateUrl('book_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $isCreationForm ? 'Create' : 'Update'));

        return $form;
    }

    /**
     * Create new Book in database
     *
     * @param Book $book
     */
    protected function createNewBook(Book $book)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($book);
        $em->flush();
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
}
