<?php

namespace Bookkeeper\UserBundle\Tests\Traits;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Bookkeeper\UserBundle\Entity\User as EntityUser;
use Symfony\Component\BrowserKit\Cookie;

/**
 * Class UserTrait
 * @package Bookkeeper\UserBundle\Tests\Traits
 *
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
 * @property \Symfony\Bundle\FrameworkBundle\Client $client
 */
trait UserTrait
{
    /**
     * @param string $role
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityUser
     */
    public function mockEntityUser($role)
    {
        $mockUser = $this
            ->getMockBuilder(EntityUser::class)
            ->disableOriginalConstructor()
            ->setMethods(array('serialize'))
            ->getMock()
        ;

        $mockUser
            ->expects($this->atLeastOnce())
            ->method('serialize')
            ->will($this->returnValue(sprintf(
                'a:3:{i:0;i:1;i:1;s:4:"user";i:2;s:%d:"%s";}',
                strlen($role),
                $role
            )))
        ;

        $reflectionClass = new \ReflectionClass(EntityUser::class);
        // id property
        $reflectionIdProperty = $reflectionClass->getProperty('id');
        $reflectionIdProperty->setAccessible(true);
        $reflectionIdProperty->setValue($mockUser, 1);
        // username property
        $reflectionUsernameProperty = $reflectionClass->getProperty('username');
        $reflectionUsernameProperty->setAccessible(true);
        $reflectionUsernameProperty->setValue($mockUser, 'user');
        // roles property
        $reflectionRolesProperty = $reflectionClass->getProperty('roles');
        $reflectionRolesProperty->setAccessible(true);
        $reflectionRolesProperty->setValue($mockUser, $role);

        return $mockUser;
    }

    /**
     * @param EntityUser $mockUser
     */
    public function mockEntityUserProvider(EntityUser $mockUser)
    {
        $mockEntityUserProvider = $this
            ->getMockBuilder(EntityUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mockEntityUserProvider
            ->expects($this->any())
            ->method('refreshUser')
            ->will($this->returnValue($mockUser))
        ;

        $this->client->getContainer()->set('security.user.provider.concrete.administrators', $mockEntityUserProvider);
    }

    /**
     * @param string $role
     *
     * Mock loggedIn user with role
     */
    public function logIn($role)
    {
        $user = $this->mockEntityUser($role);
        $this->mockEntityUserProvider($user);

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token    = new UsernamePasswordToken($user, null, $firewall, array($role));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
