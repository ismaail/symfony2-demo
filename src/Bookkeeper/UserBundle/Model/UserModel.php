<?php

namespace Bookkeeper\UserBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Bookkeeper\UserBundle\Security\Token;
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
            $user->setRoles([$user::ROLE_PENDING]);
            $user->setToken($this->generateToken());

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

    /**
     * Activate user account by setting role to MEMBER
     *
     * @param User $user
     *
     * @return User
     *
     * @throws ModelException
     */
    public function activate(User $user)
    {
        try {
            $em = $this->getEntityManager();
            $em->beginTransaction();

            $user->setRoles([User::ROLE_MEMBER]);
            $user->setToken(null);

            $em->flush();
            $em->commit();

            return $user;

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();

            throw new ModelException("Error activating user account", 0, $e);
        }
    }

    /**
     * @return string
     */
    protected function generateToken()
    {
        $token = new Token();
        return $token->generate(21);
    }
}
