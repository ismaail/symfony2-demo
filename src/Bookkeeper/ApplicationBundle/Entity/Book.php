<?php

namespace Bookkeeper\ApplicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Book
 * @package Bookkeeper\ApplicationBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Bookkeeper\ApplicationBundle\Entity\BookRepository")
 * @ORM\Table(name="book")
 *
 * @UniqueEntity("title")
 */
class Book
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, unique=true)
     *
     * @Gedmo\Slug(fields={"title"}, separator="-", unique=true)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=500)
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(value=0)
     */
    protected $pages;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Book
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Book
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param int $pages
     * @return Book
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param array $data
     */
    public function populate(array $data)
    {
        $this->setTitle($data['title'])
             ->setDescription($data['description'])
             ->setPages($data['pages']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Book';
    }
}
