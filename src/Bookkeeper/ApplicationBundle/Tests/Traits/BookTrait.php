<?php

namespace Bookkeeper\ApplicationBundle\Tests\Traits;

use Bookkeeper\ApplicationBundle\Entity\Book;

/**
 * Class BookTrait
 * @package Bookkeeper\ApplicationBundle\Tests\Traits
 * @mixin \Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase;
 * @method \Doctrine\ORM\EntityManager getEntityManager()
 */
trait BookTrait
{
    /**
     * Create & Persist Single Book Entity.
     *
     * @param array $data
     *
     * @return Book
     */
    private function createBook($data = [])
    {
        $data = array_merge([
            'title' => 'Book Title',
            'description' => 'Book Description Sample',
            'pages' => 1000,
        ], $data);

        $book = new Book();
        $book
            ->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setPages($data['pages'])
        ;

        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();

        return $book;
    }
}
