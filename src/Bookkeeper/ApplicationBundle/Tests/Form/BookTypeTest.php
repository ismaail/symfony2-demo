<?php

namespace Bookkeeper\ApplicationBundle\Tests\Form;

use Symfony\Component\Form\Test\TypeTestCase;
use Bookkeeper\ApplicationBundle\Form\BookType;
use Bookkeeper\ApplicationBundle\Entity\Book;

/**
 * Class BookTypeTest
 * @package Bookkeeper\ApplicationBundle\Tests\Form
 */
class BookTypeTest extends TypeTestCase
{
    public function testFormDataExchangeWithBookEntityObject()
    {
        $formData = array(
            'title'       => 'Book title',
            'description' => 'some description',
            'pages'       => 550,
        );

        $type = new BookType();
        $form = $this->factory->create($type);

        $object = new Book();
        $object->populate($formData);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData(), "Book form data and Book entity object are the same type");
    }

    public function testFormViewHasAllDefinedElements()
    {
        $formElements = array('title', 'description', 'pages', 'submit');

        $type = new BookType();
        $form = $this->factory->create($type);

        // Add Submit button
        $form->add('submit', 'submit');

        $view     = $form->createView();
        $children = $view->children;

        foreach ($formElements as $element) {
            $this->assertArrayHasKey($element, $children, sprintf("Book form view don't have element '%s'", $element));
        }
    }
}
