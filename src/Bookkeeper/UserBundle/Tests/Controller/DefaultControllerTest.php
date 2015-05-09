<?php

namespace Bookkeeper\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest
 * @package Bookkeeper\UserBundle\Tests\Controller
 */
class DefaultControllerTest extends WebTestCase
{
    public function testLoginAction()
    {
        $client = static::createClient();

        $client->request('GET', '/login');
    }

    public function testLogoutAction()
    {
        $client = static::createClient();

        $client->request('GET', '/logout');
    }
}
