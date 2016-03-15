<?php

namespace Bookkeeper\ApplicationBundle\DataFixtures\ORM;

use Bookkeeper\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadUserData
 * @package Bookkeeper\ApplicationBundle\DataFixtures\ORM
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @const int
     */
    const ORDER = 10;

    /**
     * @return int
     */
    public function getOrder()
    {
        return self::ORDER;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createUserAdmin());
        $manager->flush();
    }

    /**
     * @return User
     */
    private function createUserAdmin()
    {
        $user = new User();
        $user
            ->setUsername('admin')
            ->setEmail('admin@example.dev')
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword($user->hash('secret'))
        ;

        return $user;
    }
}
