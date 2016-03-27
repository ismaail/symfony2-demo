<?php

namespace Bookkeeper\ApplicationBundle\Tests\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Bookkeeper\ApplicationBundle\Form\BookType;
use Bookkeeper\ApplicationBundle\Entity\Book;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class BookTypeTest
 * @package Bookkeeper\ApplicationBundle\Tests\Form
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class BookTypeTest extends TypeTestCase
{
    /**
     * @test
     */
    public function it_exchange_data_with_book_entity_object()
    {
        $formData = [
            'title' => 'Book title',
            'description' => 'some description',
            'pages' => 550,
        ];

        $type = new BookType();
        $form = $this->factory->create($type);

        $object = new Book();
        $object->populate($formData);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData(), "Book form data and Book entity object are the same type");
    }

    /**
     * @test
     */
    public function it_has_all_defined_elements()
    {
        $formElements = array('title', 'description', 'pages', 'submit');

        $type = new BookType();
        $form = $this->factory->create($type);

        // Add Submit button
        $form->add('submit', SubmitType::class);

        foreach ($formElements as $element) {
            $this->assertTrue($form->has($element), sprintf("Book form don't have element '%s'", $element));
        }
    }

    /**
     * @test
     */
    public function view_has_all_defined_elements()
    {
        $formElements = array('title', 'description', 'pages', 'submit');

        $type = new BookType();
        $form = $this->factory->create($type);

        // Add Submit button
        $form->add('submit', SubmitType::class);

        $view     = $form->createView();
        $children = $view->children;

        foreach ($formElements as $element) {
            $this->assertArrayHasKey($element, $children, sprintf("Book form view don't have element '%s'", $element));
        }
    }
}
