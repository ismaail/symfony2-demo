<?php

namespace Bookkeeper\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
