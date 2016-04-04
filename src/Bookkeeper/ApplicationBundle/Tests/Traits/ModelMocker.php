<?php

namespace Bookkeeper\ApplicationBundle\Tests\Traits;

use Bookkeeper\ApplicationBundle\Model\BookModel;
use Bookkeeper\UserBundle\Model\UserModel;

/**
 * Class ModelMocker
 * @package Bookkeeper\ApplicationBundle\Tests\Traits
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
 * @property \Symfony\Bundle\FrameworkBundle\Client $client
 */
trait ModelMocker
{
    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Bookkeeper\ApplicationBundle\Model\BookModel
     */
    public function getBookModelMock($methods = ['getBooks', 'findBySlug'])
    {
        $mock = $this
            ->getMockBuilder(BookModel::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->client->getContainer()->set('book_model', $mock);

        return $mock;
    }

    /**
     * @param array|null $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Bookkeeper\ApplicationBundle\Model\BookModel
     */
    public function mockUserModel($methods = null)
    {
        $mock = $this
            ->getMockBuilder(UserModel::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->client->getContainer()->set('user_model', $mock);

        return $mock;
    }
}
