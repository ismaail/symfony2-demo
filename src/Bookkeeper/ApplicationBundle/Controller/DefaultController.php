<?php

namespace Bookkeeper\ApplicationBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookkeeper\ApplicationBundle\Entity\Book;

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
        $book = $this->getBookModel()->findBySlugOrFail($slug);

        return $this->render('BookkeeperApplicationBundle:Default:show.html.twig', [
            'book' => $book,
            'form' => $this->createBookDeleteForm($book)->createView(),
        ]);
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
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
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
