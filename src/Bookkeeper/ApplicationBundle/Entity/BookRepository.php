<?php

namespace Bookkeeper\ApplicationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\NoResultException;

/**
 * Class BookRepository
 * @package Bookkeeper\ApplicationBundle\Entity
 */
class BookRepository extends EntityRepository
{
    /**
     * Find Book by slug
     *
     * @param string $slug
     *
     * @return \Bookkeeper\ApplicationBundle\Entity\Book
     *
     * @throws NoResultException    If no book found
     */
    public function findBySlug($slug)
    {
        $book = $this->findOneBy(array('slug' => $slug));

        if (null === $book) {
            throw new NoResultException();
        }

        return $book;
    }
}
