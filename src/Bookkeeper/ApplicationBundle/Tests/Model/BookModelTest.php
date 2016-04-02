<?php

namespace Bookkeeper\ApplicationBundle\Tests\Model;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bookkeeper\ApplicationBundle\Tests\Traits\ServiceMocker;
use Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase;
use Bookkeeper\ApplicationBundle\Model\BookModel;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Doctrine\ORM\NoResultException;

/**
 * Class BookTest
 * @package Bookkeeper\ApplicationBundle\Tests\Model
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class BookModelTest extends DoctrineTestCase
{
    use ServiceMocker;

    /**
     * @var BookModel
     */
    private $bookModel;

    protected function setUp()
    {
        parent::setUp();

        $this->mockDisabledCacheService();

        $this->bookModel = new BookModel($this->getContainer());
    }

    /**
     * Create & Persist Single Book Entity.
     *
     * @param array $data
     *
     * @return Book
     */
    private function createBook($data = [])
    {
        $data = array_merge([
            'title' => 'Book Title',
            'description' => 'Book Description Sample',
            'pages' => 1000,
        ], $data);

        $book = new Book();
        $book
            ->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setPages($data['pages'])
        ;

        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();

        return $book;
    }

    /**
     * @test
     */
    public function getBooks_initially_returns_empty_books_list()
    {
        $books = $this->bookModel->getBooks(1, 10);

        $this->assertEmpty($books, 'Books list is not empty.');
        $this->assertCount(0, $books, 'Books list count is not 0.');
    }

    /**
     * @test
     */
    public function getBooks_returns_books()
    {
        $this->createBook();

        $books = $this->bookModel->getBooks(1, 10);

        $this->assertCount(1, $books);
        $this->assertInstanceOf(Book::class, $books[0]);
    }

    /**
     * @test
     */
    public function pagination_returns_the_right_amount_of_books()
    {
        $this->createBook(['title' => 'Book 1']);

        $this->assertCount(1, $this->bookModel->getBooks(1, 1), 'Book pagination did not returned single book.');

        $this->createBook(['title' => 'Book 2']);

        $this->assertCount(1, $this->bookModel->getBooks(2, 1), 'Book pagination did not returned single book.');
        $this->assertCount(2, $this->bookModel->getBooks(1, 2), 'Book pagination did not returned 2 books.');
    }

    /**
     * @test
     */
    public function findBySlug_throws_exception_if_book_do_not_exist()
    {
        $this->expectException(NoResultException::class);

        $this->bookModel->findBySlug('non-existant-book');
    }

    /**
     * @test
     */
    public function findBySlug_returns_found_book()
    {
        $bookTitle = 'Book Title to test findBySlug Method';
        $bookSlug = 'book-title-to-test-findbyslug-method';

        $this->createBook(['title' => $bookTitle]);

        $book = $this->bookModel->findBySlug(($bookSlug));

        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame(1, $book->getId());
        $this->assertEquals($bookTitle, $book->getTitle());
    }

    /**
     * @test
     */
    public function findBySlugOrFail_throws_exception_if_book_do_not_exist()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->bookModel->findBySlugOrFail('non-existant-book');
    }

    /**
     * @test
     */
    public function findBySlugOrFail_returns_found_book()
    {
        $bookTitle = 'Book Title to test findBySlug Method';
        $bookSlug = 'book-title-to-test-findbyslug-method';

        $this->createBook(['title' => $bookTitle]);

        $book = $this->bookModel->findBySlugOrFail(($bookSlug));

        $this->assertInstanceOf(Book::class, $book);
        $this->assertSame(1, $book->getId());
        $this->assertEquals($bookTitle, $book->getTitle());
    }

    /**
     * @test
     */
    public function create_persists_book()
    {
        $bookTitle = sprintf('Book Title #%s', __METHOD__);

        $newBook = new Book();
        $newBook
            ->setTitle($bookTitle)
            ->setDescription('Book Description')
            ->setPages(10)
        ;

        // Assert Table has no books.
        $this->assertEmpty($this->bookModel->getBooks(1, 10), 'Books list has books.');

        $this->bookModel->create($newBook);

        $books = $this->bookModel->getBooks(1, 10);

        // Assert has books.
        $this->assertNotEmpty($books);
        $this->assertCount(1, $books);
        $this->assertEquals($bookTitle, $books[0]->getTitle(), 'Wrong Book Title.');

    }

    /**
     * @test
     */
    public function update_book()
    {
        $title = 'First Title';
        $slug = 'first-title';
        $newTitle = 'New Title';
        $newSlug = 'new-title';

        $this->createBook(['title' => $title]);

        $book = $this->bookModel->findBySlug($slug);

        // Assert First Book
        $this->assertCount(1, $this->bookModel->getBooks(1, 10), 'Books list has not just 1 single Book.');
        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals($title, $book->getTitle());

        // Update the Book
        $book->setTitle($newTitle);
        $this->bookModel->update($book, $slug);

        $updatedBook = $this->bookModel->findBySlug($newSlug);

        // Assert the updated Book
        $this->assertCount(1, $this->bookModel->getBooks(1, 10), 'Books list has not just 1 single Book.');
        $this->assertInstanceOf(Book::class, $updatedBook);
        $this->assertEquals($newTitle, $updatedBook->getTitle());
    }

    /**
     * @test
     */
    public function delete_book()
    {
        $this->assertCount(0, $this->bookModel->getBooks(1, 10), 'Books list has some books.');

        $book = $this->createBook();

        // Assert Single Book.
        $this->assertCount(1, $this->bookModel->getBooks(1, 10), 'Books list has not just 1 single Book.');

        // Delete the Book.
        $this->bookModel->remove($book);

        // Asset No books.
        $this->assertCount(0, $this->bookModel->getBooks(1, 10), 'Books list has some books.');
    }
}
