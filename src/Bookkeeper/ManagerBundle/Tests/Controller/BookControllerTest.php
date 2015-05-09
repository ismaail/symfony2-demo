<?php

namespace Bookkeeper\ManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;

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
        $this->logIn();

        $this->client->request('GET', '/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testCreateActionIsAccessibleByRoleAdmin()
    {
        $this->logIn();

        $this->client->request('POST', '/create');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testEditActionIsAccessibleByRoleAdmin()
    {
        $this->logIn();
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('GET', '/edit/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testUpdateActionIsAccessibleByRoleAdmin()
    {
        $this->logIn();
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('PUT', '/update/book-title');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testDeleteActionIsAccessibleByRoleAdmin()
    {
        $this->logIn();
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->request('DELETE', '/delete/book-title');

        // /delete request is always redirected no matter the request is successful or not
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
    }

    public function testDeleteActionRedirectAfterFailedRequest()
    {
        $this->logIn();
        $this->mockBookModelGetBookBySlug('book-title');

        $this->client->followRedirects();
        $this->client->request('DELETE', 'delete/book-title');

        $this->assertStringEndsWith('/show/book-title', $this->client->getHistory()->current()->getUri());
    }

    /**
     * Mock loggedIn user with role "ROLE_ADMIN"
     */
    private function logIn()
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token    = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
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
            ->setMethods(array('getBooks', 'getBookBySlug'))
            ->disableOriginalConstructor()
            ->getMock();

        $book                   = new EntityBook();
        $bookReflectionClass    = new \ReflectionClass($book);
        $slugReflectionProperty = $bookReflectionClass->getProperty('slug');
        $slugReflectionProperty->setAccessible(true);
        $slugReflectionProperty->setValue($book, $slug);

        $bookModelMock->expects($this->once())
            ->method('getBookBySlug')
            ->with('book-title')
            ->will($this->returnValue($book));

        $this->client->getContainer()->set('book_model', $bookModelMock);
    }
}
