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
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Swift_Mailer
     */
    public function mockSwiftMailer($methods = [])
    {
        $mock = $this
            ->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
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

    /**
     * @test
     */
    public function it_returns_false_if_pretend_id_true()
    {
        $swiftMailerMock = $this->mockSwiftMailer(['createMessage', 'send']);

        $swiftMailerMock
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue(new \Swift_Message()))
        ;

        $swiftMailerMock
            ->expects($this->never())
            ->method('send')
        ;

        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
            'pretend' => true,
        ], $swiftMailerMock);

        $mailer->setHtmlBody('<div>html body sample</div>');

        $response = $mailer->send('test@example.com', 'Test Subject');

        $this->assertFalse($response);
    }

    /**
     * @test
     */
    public function it_throws_if_message_part_is_not_set()
    {
        $this->expectException(ApplicationException::class);

        $swiftMailerMock = $this->mockSwiftMailer(['createMessage']);

        $swiftMailerMock
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue(new \Swift_Message()))
        ;

        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
            'pretend' => true,
        ], $swiftMailerMock);

        $mailer->send('test@example.com', 'Test Subject');
    }

    /**
     * @test
     */
    public function it_sends_message_with_html_body()
    {
        $swiftMailerMock = $this->mockSwiftMailer(['createMessage', 'send']);

        $swiftMailerMock
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue(new \Swift_Message()))
        ;

        $swiftMailerMock
            ->expects($this->once())
            ->method('send')
            ->willReturn('Message sent')
        ;

        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
        ], $swiftMailerMock);

        $mailer->setHtmlBody('<div>html body sample</div>');

        $response = $mailer->send('test@example.com', 'Test Subject');

        $this->assertEquals('Message sent', $response);
    }

    /**
     * @test
     */
    public function it_sends_message_with_text_body()
    {
        $swiftMailerMock = $this->mockSwiftMailer(['createMessage', 'send']);

        $swiftMailerMock
            ->expects($this->once())
            ->method('createMessage')
            ->will($this->returnValue(new \Swift_Message()))
        ;

        $swiftMailerMock
            ->expects($this->once())
            ->method('send')
            ->willReturn('Message sent')
        ;

        $mailer = new Mailer([
            'address' => 'jhon@example.com',
            'name' => 'Jhone Doe',
        ], $swiftMailerMock);

        $mailer->setTextBody('text body sample');

        $response = $mailer->send('test@example.com', 'Test Subject');

        $this->assertEquals('Message sent', $response);
    }
}
