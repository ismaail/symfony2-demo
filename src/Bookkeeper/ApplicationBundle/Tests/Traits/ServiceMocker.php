<?php

namespace Bookkeeper\ApplicationBundle\Tests\Traits;

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
}
