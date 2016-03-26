<?php

namespace Bookkeeper\ApplicationBundle\Tests\Entity;

use Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Doctrine\ORM\NoResultException;

/**
 * Class BookRepositoryTest
 * @package Bookkeeper\ApplicationBundle\Tests\Entity
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class BookRepositoryTest extends DoctrineTestCase
{
    /**
     * @var \Bookkeeper\ApplicationBundle\Entity\BookRepository
     */
    private $bookRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->bookRepository = $this->getEntityManager()->getRepository('BookkeeperApplicationBundle:Book');
    }

    private function createBook()
    {
        $book = new Book();
        $book
            ->setTitle('Book Title')
            ->setDescription('Book Descriptoin Sample')
            ->setPages(1000)
        ;

        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();

        return $book;
    }

    /**
     * @test
     */
    public function it_throws_exception_if_book_is_not_found_by_slug()
    {
        $this->expectException(NoResultException::class);
        $this->expectExceptionMessage('No result was found for query although at least one row was expected.');

        $this->bookRepository->findBySlug('non-existant-book');
    }

    /**
     * @test
     */
    public function it_returns_book_by_slug()
    {
        $this->createBook();

        $book = $this->bookRepository->findBySlug('book-title');

        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame(1, $book->getId());
    }

    /**
     * @test
     */
    public function initially_it_has_no_books()
    {
        $books = $this->bookRepository->findAll();

        $this->assertEmpty($books, 'Repository has Books');
    }

    /**
     * @test
     */
    public function it_return_single_book_for_findAll()
    {
        $this->createBook();

        $books = $this->bookRepository->findAll();

        $this->assertCount(1, $books);
        $this->assertInstanceOf(Book::class, $books[0]);
    }
}
