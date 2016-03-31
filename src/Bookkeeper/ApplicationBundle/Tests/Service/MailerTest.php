<?php

namespace Bookkeeper\ApplicationBundle\Tests\Service;

use Bookkeeper\ApplicationBundle\Exception\ApplicationException;
use Bookkeeper\ApplicationBundle\Service\Mailer;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class MailerTest
 * @package Bookkeeper\ApplicationBundle\Tests\Service
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class MailerTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Swift_Mailer
     */
    public function mockSwiftMailer()
    {
        $mock = $this
            ->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock()
        ;

        return $mock;
    }
    /**
     * @test
     */
    public function it_throws_exception_if_all_sender_params_are_not_defined()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Sender email address is not defined');

        // all email sender params not defined.
        new Mailer([], $this->mockSwiftMailer());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_sender_name_param_is_not_defined()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Sender name is not defined');

        // email sender name param not defined.
        new Mailer(['address' => 'jhon@example.com'], $this->mockSwiftMailer());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_sender_address_param_is_not_defined()
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('Sender email address is not defined');

        // sender email address param not defined.
        new Mailer(['name' => 'Jhon Doe'], $this->mockSwiftMailer());
    }

    /**
     * @test
     */
    public function pretend_param_initial_value_is_false()
    {
        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
        ], $this->mockSwiftMailer());

        $reflectedClass = new \ReflectionClass(Mailer::class);
        $reflectedProperty = $reflectedClass->getProperty('pretend');
        $reflectedProperty->setAccessible(true);

        $this->assertFalse($reflectedProperty->getValue($mailer));
    }

    /**
     * @test
     */
    public function pretend_param_is_updated()
    {
        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
            'pretend' => true,
        ], $this->mockSwiftMailer());

        $reflectedClass = new \ReflectionClass(Mailer::class);
        $reflectedProperty = $reflectedClass->getProperty('pretend');
        $reflectedProperty->setAccessible(true);

        $this->assertTrue($reflectedProperty->getValue($mailer));
    }
}
