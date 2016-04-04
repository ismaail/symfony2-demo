<?php

namespace Bookkeeper\ApplicationBundle\Tests\Traits;

use Bookkeeper\ApplicationBundle\Service\Mailer;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class ServiceMocker
 * @package Bookkeeper\ApplicationBundle\Tests\Traits
 * @mixin \Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase
 */
trait ServiceMocker
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function mockDisabledCacheService()
    {
        $mock = $this
            ->getMockBuilder(FilesystemCache::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetch', 'save'])
            ->getMock()
        ;

        $mock
            ->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false))
        ;

        $mock
            ->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true))
        ;

        $this->getContainer()->set('cache', $mock);

        return $mock;
    }

    /**
     * Create Application Mailer service mock.
     *
     * @param array|null $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Mailer
     */
    public function mockMailer($methods = null)
    {
        $mock = $this
            ->getMockBuilder(Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock()
        ;

        $this->getContainer()->set('app_mailer', $mock);

        return $mock;
    }
}
