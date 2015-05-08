<?php

namespace Bookkeeper\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\UserBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $this->markTestSkipped();
        $client = static::createClient();

        $client->request('GET', '/login');
    }
}
