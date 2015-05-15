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

    /** @var \Doctrine\Common\Cache\FilesystemCache $cache */
    protected $cache;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->setCache();
    }

    /**
     * Set cache options
     */
    protected function setCache()
    {
        $params = $this->container->getParameter('cache');

        $this->cache = $this->container->get('cache');
        $this->cache->setNamespace($params['namespace']);

        $this->cacheTtl = $params['ttl'];
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
        $key = sprintf("books_slug_%s", $slug);

        // Get from cache
        $cachedBook = $this->cache->fetch($key);
        if (false !== $cachedBook) {
            return $cachedBook;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
            ->from('BookkeeperApplicationBundle:Book', 'b')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug);

        $book = $qb->getQuery()->getSingleResult();

        // Save to cache
        $this->cache->save($key, $book, $this->cacheTtl);

        return $book;
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
     * Remove book record
     *
     * @param Book $book
     */
    public function remove(Book $book)
    {
        $this->getEntityManager()->remove($book);
        $this->getEntityManager()->flush();
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
