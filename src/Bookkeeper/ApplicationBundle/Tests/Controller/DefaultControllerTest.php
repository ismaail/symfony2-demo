<?php

namespace Bookkeeper\ApplicationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bookkeeper\ApplicationBundle\Controller\DefaultController;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\ApplicationBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful(), 'Request to "/" was not successful');

        $this->assertEquals('text/html; charset=UTF-8', $client->getResponse()->headers->get('Content-Type'));
    }

    /**
     * @expectedException \Bookkeeper\ApplicationBundle\Exception\ApplicationException
     */
    public function testEmailSendMessageThrowsExceptionIfSenderParamsNotDefined()
    {
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

    /**
     * @expectedException \Bookkeeper\ApplicationBundle\Exception\ApplicationException
     */
    public function testEmailSendMessageThrowsExceptionIfSenderNameParamsNotDefined()
    {
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

    /**
     * @expectedException \Bookkeeper\ApplicationBundle\Exception\ApplicationException
     */
    public function testEmailSendMessageThrowsExceptionIfSenderAddressParamsNotDefined()
    {
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
