<?php

namespace Bookkeeper\ManagerBundle\Tests\Controller;

use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;
use Bookkeeper\ApplicationBundle\Tests\Traits\ModelMocker;
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
class BookControllerTest extends WebTestCase
{
    use UserTrait;
    use ModelMocker;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

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
}
