<?php

namespace Bookkeeper\ApplicationBundle\Tests\Traits;

/**
 * Class ModelMocker
 * @package Bookkeeper\ApplicationBundle\Tests\Traits
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
 */
trait ModelMocker
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Bookkeeper\ApplicationBundle\Model\BookModel
     */
    public function getBookModelMock()
    {
        $mock = $this
            ->getMockBuilder('Bookkeeper\ApplicationBundle\Model\BooModel')
            ->setMethods(array('getBooks', 'findBySlug'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->client->getContainer()->set('book_model', $mock);

        return $mock;
    }
}
