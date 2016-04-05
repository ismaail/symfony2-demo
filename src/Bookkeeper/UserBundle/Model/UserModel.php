<?php

namespace Bookkeeper\UserBundle\Model;

use Bookkeeper\UserBundle\Security\Token;
use Bookkeeper\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class UserModel
 * @package Bookkeeper\UserBundle\Model
 */
class UserModel
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get Doctrine entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
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
     * @param int $id
     *
     * @return User
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findById($id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('u')
            ->from('BookkeeperUserBundle:User', 'u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Activate user account by setting role to MEMBER
     *
     * @param int $id
     *
     * @return User
     *
     * @throws ModelException
     */
    public function activate($id)
    {
        try {
            $em = $this->getEntityManager();
            $em->beginTransaction();

            $user = $this->findById($id);
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
