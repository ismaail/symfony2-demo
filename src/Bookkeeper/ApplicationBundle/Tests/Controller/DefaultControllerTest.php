<?php

namespace Bookkeeper\ApplicationBundle\Tests\Controller;

use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;
use Bookkeeper\ApplicationBundle\Tests\Traits\ModelMocker;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\NoResultException;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\ApplicationBundle\Tests\Controller
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class DefaultControllerTest extends WebTestCase
{
    use ModelMocker;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     * @group action_index
     */
    public function indexAction_return_OK_response()
    {
        $bookModelMock = $this->getBookModelMock();

        /** @var \Knp\Component\Pager\Paginator $paginator $paginator */
        $paginator = $this->client->getContainer()->get('knp_paginator');

        $bookModelMock
            ->expects($this->once())
            ->method('getBooks')
            ->with(1, 20)
            ->will($this->returnValue($paginator->paginate([], 1, 20)))
        ;

        // Send the request
        $this->client->request('GET', '/');

        // Assertions
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Get request to "/" was not successful');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));
    }

    /**
     * @test
     * @group action_show
     */
    public function show_action_return_OK_response()
    {
        $bookModelMock = $this->getBookModelMock(['findBySlugOrFail']);

        // Create Book Entity
        $book = new EntityBook();
        $bookReflectionClass = new \ReflectionClass($book);
        $slugReflectionProperty = $bookReflectionClass->getProperty('slug');
        $slugReflectionProperty->setAccessible(true);
        $slugReflectionProperty->setValue($book, 'title-book');

        $bookModelMock
            ->expects($this->once())
            ->method('findBySlugOrFail')
            ->with('book-title')
            ->will($this->returnValue($book))
        ;

        // Send the request
        $this->client->request('GET', '/show/book-title');

        // Assertions
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'GET request to "/show/book-title" was not successful'
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));
    }

    /**
     * @test
     * @group action_show
     */
    public function Show_action_returns_404_error_if_book_is_not_found()
    {
        $bookModelMock = $this->getBookModelMock();

        $bookModelMock
            ->expects($this->once())
            ->method('findBySlug')
            ->with('no-exists-book')
            ->will($this->throwException(new NoResultException()))
        ;

        // Send the request
        $this->client->request('GET', '/show/no-exists-book');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
}
