<?php

namespace Bookkeeper\ManagerBundle\Tests\Controller;

use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;
use Bookkeeper\ApplicationBundle\Tests\Traits\ModelMocker;
use Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase;
use Bookkeeper\ApplicationBundle\Tests\Traits\BookTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bookkeeper\UserBundle\Entity\User as EntityUser;
use Bookkeeper\UserBundle\Tests\Traits\UserTrait;

/**
 * Class BookControllerTest
 * @package Bookkeeper\ManagerBundle\Tests\Controller
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class BookControllerTest extends DoctrineTestCase
{
    use UserTrait;
    use ModelMocker;
    use BookTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Bookkeeper\ApplicationBundle\Entity\BookRepository
     */
    private $bookRepository;

    protected function setUp()
    {
        $this->client = static::createClient();

        parent::setUp();

        $this->bookRepository = $this->getEntityManager()->getRepository('BookkeeperApplicationBundle:Book');
    }

    /*
     |---------------------------------------------------------
     | Test Access to Controller paths.
     |---------------------------------------------------------
     |
     | Test authorization access to action paths of the controller
     | for ROLE_ADMIN, ROLE_MEMBER and ROLE_ANONYMOUS roles.
     |
     */

    /**
     * @param string $slug
     */
    private function mockBookModelGetBookBySlug($slug)
    {
        $bookModelMock = $this->getBookModelMock(['findBySlug', 'merge']);

        $book = new EntityBook();
        $bookReflectionClass = new \ReflectionClass($book);
        $slugReflectionProperty = $bookReflectionClass->getProperty('slug');
        $slugReflectionProperty->setAccessible(true);
        $slugReflectionProperty->setValue($book, $slug);

        $bookModelMock
            ->expects($this->once())
            ->method('findBySlug')
            ->with('book-title')
            ->will($this->returnValue($book))
        ;

        $bookModelMock
            ->expects($this->any())
            ->method('merge')
            ->with($book)
            ->will($this->returnValue($book))
        ;
    }

    /**
     * @test
     * @group role_admin
     * @group action_new
     */
    public function role_admin_can_access_New_action()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /new path');
    }

    /**
     * @test
     * @group role_amember
     * @group action_new
     */
    public function role_member_cannot_access_New_action()
    {
        $this->logIn(EntityUser::ROLE_MEMBER);

        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isForbidden(), 'ROLE_MEMEBER can access /new path');
    }

    /**
     * @test
     * @group role_anonymous
     * @group action_new
     */
    public function role_anon_connot_access__New_action()
    {
        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group role_admin
     * @group action_create
     */
    public function role_admin_can_access_Create_action()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /create path');
    }

    /**
     * @test
     * @group role_member
     * @group action_create
     */
    public function role_member_cannot_access_Create_action()
    {
        $this->logIn(EntityUser::ROLE_MEMBER);

        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isForbidden(), 'ROLE_MEMEBER can access /create path');
    }

    /**
     * @test
     * @group role_anonymous
     * @group action_create
     */
    public function role_anon_cannot_access__Create_action()
    {
        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group role_admin
     * @group action_edit
     */
    public function role_admin_can_access_Edit_action()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /edit path');
    }

    /**
     * @test
     * @group role_member
     * @group action_edit
     */
    public function role_member_cannot_access_Edit_action()
    {
        $this->logIn(EntityUser::ROLE_MEMBER);

        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isForbidden(), 'ROLE_MEMEBER can access /edit path');
    }

    /**
     * @test
     * @group role_anonymous
     * @group action_edit
     */
    public function role_anon_cannot_access_Edit_action()
    {
        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group role_admin
     * @group action_update
     */
    public function role_admin_can_access_Update_action()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /update path');
    }

    /**
     * @test
     * @group role_member
     * @group action_update
     */
    public function role_member_cannot_access_Update_action()
    {
        $this->logIn(EntityUser::ROLE_MEMBER);

        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isForbidden(), 'ROLE_MEMEBER can access /update path');
    }

    /**
     * @test
     * @group role_anonymous
     * @group action_update
     */
    public function anon_role_cannot_access_Update_action()
    {
        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group role_admin
     * @group action_delete
     */
    public function role_admin_can_access_Delete_action()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('DELETE', '/delete/book-title');

        // /delete request is always redirected no matter the request is successful or not
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
    }

    /**
     * @test
     * @group role_member
     * @group action_delete
     */
    public function role_member_cannot_access_Delete_action()
    {
        $this->logIn(EntityUser::ROLE_MEMBER);

        $this->client->request('DELETE', '/delete/book-title');

        $this->assertTrue($this->client->getResponse()->isForbidden(), 'ROLE_MEMEBER can access /delete path');
    }

    /**
     * @test
     * @group role_anonymous
     * @group action_delete
     */
    public function role_anon_cannot_access_Delete_action()
    {
        $this->client->request('DELETE', '/delete/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /*
     |---------------------------------------------------------
     | Test data manipulation by controller actions.
     |---------------------------------------------------------
     |
     */

    /**
     * @test
     * @group action_create
     */
    public function it_creates_new_Book_for_valide_input_data()
    {
        $input = [
            'application_book' => [
                'title' => 'Valide Book Title',
                'description' => 'Book Short Description',
                'pages' => 1,
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('application_book')->getValue(),
            ]
        ];

        $slug = 'valide-book-title';

        // Assert Book table is empty.
        $this->assertCount(0, $this->bookRepository->findAll());

        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('POST', '/create', $input);

        // Asset Book table has 1 book.
        $this->assertCount(1, $this->bookRepository->findAll());

        // Assert Book table has the created book
        $book = $this->bookRepository->findBySlug($slug);
        $this->assertInstanceOf(EntityBook::class, $book);
        $this->assertEquals($input['application_book']['title'], $book->getTitle());
        $this->assertEquals($input['application_book']['description'], $book->getDescription());
        $this->assertEquals($input['application_book']['pages'], $book->getPages());

        $this->client->followRedirect();

        // Asset correct url redirect.
        $this->assertSame(
            'http://localhost/show/' . $slug,
            $this->client->getHistory()->current()->getUri()
        );
    }

    /**
     * @test
     * @group action_edit
     */
    public function it_returns_404_response_if_editing_non_existant_book()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        // Assert Book table is empty.
        $this->assertCount(0, $this->bookRepository->findAll());

        $this->client->request('GET', '/edit/non-existant-book');

        // Assert edting non exitant book returns 404 erro page.
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    /**
     * @test
     * @group action_edit
     */
    public function it_returns_404_response_if_updating_non_existant_book()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        // Assert Book table is empty.
        $this->assertCount(0, $this->bookRepository->findAll());

        $this->client->request('PUT', '/update/non-existant-book');

        // Assert edting non exitant book returns 404 erro page.
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    /**
     * @test
     * @group action_update
     */
    public function it_updates_the_Book_for_valide_input_data()
    {
        $this->createBook(['title' => 'Old Book Title']);

        // Asset book table has old Book.
        $books = $this->bookRepository->findAll();
        $this->assertCount(1, $books);
        $this->assertEquals('Old Book Title', $books[0]->getTitle());
        $this->assertEquals('old-book-title', $books[0]->getSlug());

        // Send PUT request to update the Book.
        $input = [
            'application_book' => [
                'title' => 'New Book Title',
                'description' => 'Book Short Description',
                'pages' => 1,
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('application_book')->getValue(),
            ]
        ];

        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->client->request('PUT', '/update/old-book-title', $input);

        // Asset book table has old Book.
        $books = $this->bookRepository->findAll();
        $this->assertCount(1, $books);
        $this->assertEquals('New Book Title', $books[0]->getTitle());
        $this->assertEquals('new-book-title', $books[0]->getSlug());
    }

    /**
     * @test
     * @group action_delete
     */
    public function it_returns_404_response_if_deleting_non_existing_book()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('DELETE', '/delete/non-existant-book');

        // Assert edting non exitant book returns 404 erro page.
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    /**
     * @test
     * @group action_delte
     */
    public function it_delete_existing_book()
    {
        $input = [
            'form' => [
                '_token' => $this->getContainer()->get('security.csrf.token_manager')->getToken('form')->getValue(),
            ]
        ];

        $this->createBook(['title' => 'Delete Me']);

        // Asset Book table has creted book.
        $this->assertCount(1, $this->bookRepository->findAll(), 'Book table has no books');

        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->client->request('DELETE', '/delete/delete-me', $input);

        // Assert Book table is empty.
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertCount(0, $this->bookRepository->findAll(), 'Book table has books.');
    }
}
