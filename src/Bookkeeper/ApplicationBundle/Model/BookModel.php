<?php

namespace Bookkeeper\ApplicationBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Bookkeeper\ApplicationBundle\Entity\Book;

/**
 * Class BookModel
 * @package Bookkeeper\ApplicationBundle\Model
 */
class BookModel
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
     * Get all books using pagination
     *
     * @param int $page
     * @param int $limit
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\Book[]
     */
    public function getBooks($page, $limit)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
           ->from('BookkeeperApplicationBundle:Book', 'b')
           ->orderBy('b.id', 'asc');

        /** @var \Knp\Component\Pager\Paginator $paginator */
        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $page,
            $limit
        );

        return $pagination;
    }

    /**
     * Get single book by slug
     *
     * @param string $slug
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\Book
     */
    public function getBookBySlug($slug)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
            ->from('BookkeeperApplicationBundle:Book', 'b')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Create new Book in database
     *
     * @param Book $book
     */
    public function createNewBook(Book $book)
    {
        $em = $this->getEntityManager();
        $em->persist($book);
        $em->flush();
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
}