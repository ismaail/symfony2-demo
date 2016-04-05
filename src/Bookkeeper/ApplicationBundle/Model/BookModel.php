<?php

namespace Bookkeeper\ApplicationBundle\Model;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityManager;

/**
 * Class BookModel
 * @package Bookkeeper\ApplicationBundle\Model
 */
class BookModel
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /** @var \Doctrine\Common\Cache\FilesystemCache $cache */
    protected $cache;

    /**
     * @var \Bookkeeper\ApplicationBundle\Entity\BookRepository
     */
    protected $repository;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * @var \Knp\Component\Pager\Paginator
     */
    protected $paginator;

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set cache options
     *
     * @param \Doctrine\Common\Cache\FilesystemCache $cache
     * @param array $parameters
     */
    public function setCache($cache, $parameters)
    {
        $this->cache = $cache;
        $this->cache->setNamespace($parameters['namespace']);

        $this->cacheTtl = $parameters['ttl'];
    }

    /**
     * @param \Knp\Component\Pager\Paginator $paginator
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;
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
        $qb
           ->select('b')
           ->from('BookkeeperApplicationBundle:Book', 'b')
           ->orderBy('b.id', 'asc')
        ;

        $pagination = $this->paginator->paginate(
            $qb->getQuery(),
            $page,
            $limit
        );

        return $pagination;
    }

    /**
     * Find single book by slug
     *
     * @param string $slug
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\Book
     */
    public function findBySlug($slug)
    {
        $key = sprintf("book_slug_%s", $slug);

        // Get from cache
        $cachedBook = $this->cache->fetch($key);
        if (false !== $cachedBook) {
            return $cachedBook;
        }

        $book = $this->getReposiroty()->findBySlug($slug);

        // Save to cache
        $this->cache->save($key, $book, $this->cacheTtl);

        return $book;
    }

    /**
     * Find single Book by slug or fail to 404 error if not found.
     *
     * @param string $slug
     *
     * @return Book
     *
     * @throws NotFoundHttpException    If Book not found.
     */
    public function findBySlugOrFail($slug)
    {
        try {
            return $this->findBySlug($slug);

        } catch (NoResultException $e) {
            throw new NotFoundHttpException("Book not found", $e);
        }
    }

    /**
     * Create new Book
     *
     * @param Book $book
     */
    public function create(Book $book)
    {
        $em = $this->getEntityManager();
        $em->persist($book);
        $em->flush();
    }

    /**
     * @param Book $book
     * @param string $slug
     *
     * @return Book
     *
     * @throws ModelException
     */
    public function update(Book $book, $slug)
    {
        try {
            $em = $this->getEntityManager();
            $em->beginTransaction();
            $em->flush();
            $em->commit();

            $this->removeFromCache($slug);

            return $book;

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();

            throw new ModelException("Error updating book", 0, $e);
        }
    }

    /**
     * Remove book record
     *
     * @param Book $book
     *
     * @throws ModelException
     */
    public function remove(Book $book)
    {
        try {
            $em = $this->getEntityManager();
            $em->beginTransaction();

            /** @var Book $book */
            $book = $this->merge($book);
            $em->remove($book);
            $em->flush();
            $em->commit();

            $this->removeFromCache($book->getSlug());

        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();

            throw new ModelException("Error removing book", 0, $e);
        }
    }

    /**
     * @param string $slug
     */
    public function removeFromCache($slug)
    {
        $this->cache->delete(sprintf("book_slug_%s", $slug));
    }

    /**
     * Get Doctrine entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get Book Repository
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\BookRepository|\Doctrine\ORM\EntityRepository
     */
    protected function getReposiroty()
    {
        if (null === $this->repository) {
            $this->repository = $this->getEntityManager()->getRepository('BookkeeperApplicationBundle:Book');
        }

        return $this->repository;
    }

    /**
     * Merge entity object
     *
     * @param Book $object
     *
     * @return Book
     */
    public function merge($object)
    {
        return $this->getEntityManager()->merge($object);
    }
}
