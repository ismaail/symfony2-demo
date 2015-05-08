<?php

namespace Bookkeeper\ManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class BookControllerTest
 * @package Bookkeeper\ManagerBundle\Tests\Controller
 */
class BookControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        /** @todo Mock Login */
        $this->markTestIncomplete("Test skiped, it need mock loged-in user");

        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
