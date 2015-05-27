<?php

namespace Bookkeeper\ManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;
use Bookkeeper\UserBundle\Entity\User as EntityUser;

/**
 * Class BookControllerTest
 * @package Bookkeeper\ManagerBundle\Tests\Controller
 */
class BookControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testNewActionIsAccessibleByRoleAdmin()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /new path');
    }

    public function testNewActionNotAccessibleByAnonymousUser()
    {
        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    public function testCreateActionIsAccessibleByRoleAdmin()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);

        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /create path');
    }

    public function testCreateActionNotAccessibleByAnonymousUser()
    {
        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    public function testEditActionIsAccessibleByRoleAdmin()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /edit path');
    }

    public function testEditActionNotAccessibleByAnonymousUser()
    {
        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    public function testUpdateActionIsAccessibleByRoleAdmin()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'ROLE_ADMIN cannot access /update path');
    }

    public function testUpdateActionNotAccessibleByAnonymousUser()
    {
        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    public function testDeleteActionIsAccessibleByRoleAdmin()
    {
        $this->logIn(EntityUser::ROLE_ADMIN);
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('DELETE', '/delete/book-title');

        // /delete request is always redirected no matter the request is successful or not
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
    }

    public function testDeleteActionNotAccessibleByAnonymousUser()
    {
        $this->client->request('DELETE', '/delete/book-title');

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @param string $role
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function mockBookkeeperEntityUser($role)
    {
        $mockUser = $this
            ->getMockBuilder('\Bookkeeper\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->setMethods(array('serialize'))
            ->getMock();

        $mockUser
            ->expects($this->atLeastOnce())
            ->method('serialize')
            ->will($this->returnValue(sprintf('a:3:{i:0;i:1;i:1;s:4:"user";i:2;s:%d:"%s";}', strlen($role), $role)));

        $reflectionClass = new \ReflectionClass('\Bookkeeper\UserBundle\Entity\User');
        // id property
        $reflectionIdProperty = $reflectionClass->getProperty('id');
        $reflectionIdProperty->setAccessible(true);
        $reflectionIdProperty->setValue($mockUser, 1);
        // username property
        $reflectionUsernameProperty = $reflectionClass->getProperty('username');
        $reflectionUsernameProperty->setAccessible(true);
        $reflectionUsernameProperty->setValue($mockUser, 'user');
        // roles property
        $reflectionRolesProperty = $reflectionClass->getProperty('roles');
        $reflectionRolesProperty->setAccessible(true);
        $reflectionRolesProperty->setValue($mockUser, $role);

        return $mockUser;
    }

    public function mockEntityUserProvider($mockUser)
    {
        $mockEntityUserProvider = $this
            ->getMockBuilder('\Symfony\Bridge\Doctrine\Security\User\EntityUserProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEntityUserProvider
            ->expects($this->any())
            ->method('refreshUser')
            ->will($this->returnValue($mockUser));

        $this->client->getContainer()->set('security.user.provider.concrete.administrators', $mockEntityUserProvider);
    }

    /**
     * @param string $role
     *
     * Mock loggedIn user with role
     */
    private function logIn($role)
    {
        $user = $this->mockBookkeeperEntityUser($role);
        $this->mockEntityUserProvider($user);

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token    = new UsernamePasswordToken($user, null, $firewall, array($role));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param string $slug
     */
    private function mockBookModelGetBookBySlug($slug)
    {
        $bookModelMock = $this->getMockBuilder('Bookkeeper\ApplicationBundle\Model\BooModel')
            ->setMethods(array('getBooks', 'findBySlug'))
            ->disableOriginalConstructor()
            ->getMock();

        $book                   = new EntityBook();
        $bookReflectionClass    = new \ReflectionClass($book);
        $slugReflectionProperty = $bookReflectionClass->getProperty('slug');
        $slugReflectionProperty->setAccessible(true);
        $slugReflectionProperty->setValue($book, $slug);

        $bookModelMock->expects($this->once())
            ->method('findBySlug')
            ->with('book-title')
            ->will($this->returnValue($book));

        $this->client->getContainer()->set('book_model', $bookModelMock);
    }
}
