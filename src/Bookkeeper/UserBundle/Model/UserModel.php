<?php

namespace Bookkeeper\UserBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Bookkeeper\UserBundle\Entity\User;

/**
 * Class UserModel
 * @package Bookkeeper\UserBundle\Model
 */
class UserModel
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get Doctrine entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        if (! $this->entityManager) {
            $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        }

        return $this->entityManager;
    }

    /**
     * Create new user account
     *
     * @param User $user
     *
     * @return User
     *
     * @throws ModelException
     */
    public function create(User $user)
    {
        try {
            // Prepare user data
            $user->setPassword($user->hash($user->getPassword()));
            $user->setRoles(array($user::ROLE_MEMBER));

            $em = $this->getEntityManager();
            $em->beginTransaction();
            $em->persist($user);
            $em->flush();
            $em->commit();

            return $user;

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();

            throw new ModelException("Error creating new user account", 0, $e);
        }
    }
}
