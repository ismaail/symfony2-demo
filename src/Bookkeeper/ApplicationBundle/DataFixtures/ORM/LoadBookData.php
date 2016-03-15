<?php

namespace Bookkeeper\ApplicationBundle\DataFixtures\ORM;

use Bookkeeper\ApplicationBundle\Entity\Book;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

/**
 * Class LoadBookData
 * @package Bookkeeper\ApplicationBundle\DataFixtures\ORM
 */
class LoadBookData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @const int
     */
    const ORDER = 20;

    /**
     * @var Faker\Generator
     */
    private $faker;

    /**
     * LoadBookData constructor.
     */
    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

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
        foreach ($this->createBooks(500) as $book) {
            $manager->persist($book);
        }

        $manager->flush();
    }

    /**
     * @param int $max
     *
     * @return \Generator|Book
     */
    private function createBooks($max)
    {
        for ($i = 0; $i < $max; $i++) {
            $book = new Book();
            $book
                ->setTitle($this->faker->sentence(mt_rand(2, 4)))
                ->setDescription(implode('', $this->faker->paragraphs(mt_rand(1, 6))))
                ->setPages($this->faker->numberBetween(4, 1000))
            ;

            yield $book;
        }
    }
}
