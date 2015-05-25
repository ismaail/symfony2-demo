<?php

namespace Bookkeeper\ApplicationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bookkeeper\ApplicationBundle\Controller\DefaultController;
use Bookkeeper\ApplicationBundle\Entity\Book as EntityBook;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\ApplicationBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBookModelMock()
    {
        return $this->getMockBuilder('Bookkeeper\ApplicationBundle\Model\BooModel')
                    ->setMethods(array('getBooks', 'findBySlug'))
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    public function testIndexAction()
    {
        // Mock BookModel
        $bookModelMock = $this->getBookModelMock();

        /** @var \Knp\Component\Pager\Paginator $paginator $paginator */
        $paginator = $this->client->getContainer()->get('knp_paginator');

        $bookModelMock->expects($this->once())
                      ->method('getBooks')
                      ->with(1, 20)
                      ->will($this->returnValue($paginator->paginate(array(), 1, 20)));

        $this->client->getContainer()->set('book_model', $bookModelMock);

        // Send the request
        $this->client->request('GET', '/');

        // Assertions
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Get request to "/" was not successful');

        $this->assertEquals('text/html; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testShowAction()
    {
        // Mock BookModel
        $bookModelMock = $this->getBookModelMock();

        $book                   = new EntityBook();
        $bookReflectionClass    = new \ReflectionClass($book);
        $slugReflectionProperty = $bookReflectionClass->getProperty('slug');
        $slugReflectionProperty->setAccessible(true);
        $slugReflectionProperty->setValue($book, 'title-book');

        $bookModelMock->expects($this->once())
                      ->method('findBySlug')
                      ->with('book-title')
                      ->will($this->returnValue($book));

        $this->client->getContainer()->set('book_model', $bookModelMock);

        // Send the request
        $this->client->request('GET', '/show/book-title');

        // Assertions
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful(),
            'GET request to "/show/book-title" was not successful'
        );

        $this->assertEquals('text/html; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testShowActionReturns404ErrorIfBookNotFound()
    {
        // Mock BookModel
        $bookModelMock = $this->getBookModelMock();

        $bookModelMock->expects($this->once())
                      ->method('findBySlug')
                      ->with('no-exists-book')
                      ->will($this->throwException(new \Doctrine\ORM\NoResultException()));

        $this->client->getContainer()->set('book_model', $bookModelMock);

        // Send the request
        $this->client->request('GET', '/show/no-exists-book');

        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testEmailSendMessageThrowsExceptionIfSenderParamsNotDefined()
    {
        $this->setExpectedException('\Bookkeeper\ApplicationBundle\Exception\ApplicationException');

        $containerMock = $this->getMockBuilder('appDevDebugProjectContainer')
            ->setMethods(array('getParameter', 'get'))
            ->getMock();

        $containerMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(array())); // all email sender params not defined

        $object             = new DefaultController();
        $reflectionClass    = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty('container');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $containerMock);

        $method = new \ReflectionMethod('Bookkeeper\ApplicationBundle\Controller\DefaultController', 'sendMessage');
        $method->setAccessible(true);
        $method->invoke($object, 'subject test', 'message body', 'test@test.com');
    }

    public function testEmailSendMessageThrowsExceptionIfSenderNameParamsNotDefined()
    {
        $this->setExpectedException('\Bookkeeper\ApplicationBundle\Exception\ApplicationException');

        $containerMock = $this->getMockBuilder('appDevDebugProjectContainer')
            ->setMethods(array('getParameter', 'get'))
            ->getMock();

        $containerMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(array('address' => 'foo@example.com'))); // email sender name param not defined

        $object             = new DefaultController();
        $reflectionClass    = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty('container');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $containerMock);

        $method = new \ReflectionMethod('Bookkeeper\ApplicationBundle\Controller\DefaultController', 'sendMessage');
        $method->setAccessible(true);
        $method->invoke($object, 'subject test', 'message body', 'test@test.com');
    }

    public function testEmailSendMessageThrowsExceptionIfSenderAddressParamsNotDefined()
    {
        $this->setExpectedException('\Bookkeeper\ApplicationBundle\Exception\ApplicationException');

        $containerMock = $this->getMockBuilder('appDevDebugProjectContainer')
            ->setMethods(array('getParameter', 'get'))
            ->getMock();

        $containerMock->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(array('name' => 'foobar'))); // email sender address param not defined

        $object             = new DefaultController();
        $reflectionClass    = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty('container');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $containerMock);

        $method = new \ReflectionMethod('Bookkeeper\ApplicationBundle\Controller\DefaultController', 'sendMessage');
        $method->setAccessible(true);
        $method->invoke($object, 'subject test', 'message body', 'test@test.com');
    }
}
