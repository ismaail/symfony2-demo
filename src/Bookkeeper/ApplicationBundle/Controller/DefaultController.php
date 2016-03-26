<?php

namespace Bookkeeper\ApplicationBundle\Controller;

use Bookkeeper\ApplicationBundle\Exception\ApplicationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookkeeper\ApplicationBundle\Entity\Book;
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
     * List all books action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $booksParams = $this->container->getParameter('books');

        $books = $this->getBookModel()->getBooks(
            $request->query->get('page', 1),
            $booksParams['pagination']['limit']
        );

        return $this->render('BookkeeperApplicationBundle:Default:index.html.twig', compact('books'));
    }

    /**
     * Show book details action
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($slug)
    {
        try {
            $book = $this->getBookModel()->findBySlug($slug);

            return $this->render('BookkeeperApplicationBundle:Default:show.html.twig', [
                'book' => $book,
                'form' => $this->createBookDeleteForm($book)->createView(),
            ]);

        } catch (NoResultException $e) {
            throw $this->createNotFoundException("Book not found");
        }
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
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;

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
        $message
            ->setSubject($subject)
            ->setFrom($emailParams['address'], $emailParams['name'])
            ->setTo($to)
            ->setBody($body, 'text/html')
        ;

        $mailer->send($message);
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
