<?php

namespace Bookkeeper\UserBundle\Tests\Controller;

use Bookkeeper\ApplicationBundle\Tests\Traits\ServiceMocker;
use Bookkeeper\ApplicationBundle\Tests\Traits\ModelMocker;
use Bookkeeper\ApplicationBundle\Tests\DoctrineTestCase;
use Bookkeeper\UserBundle\Tests\Traits\UserTrait;
use Bookkeeper\UserBundle\Entity\User;

/**
 * Class UserControllerTest
 * @package Bookkeeper\UserBundle\Tests\Controller
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @codingStandardsIgnoreFile
 */
class UserControllerTest extends DoctrineTestCase
{
    use UserTrait;
    use ModelMocker;
    use ServiceMocker;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();

        parent::setUp();
    }

    /**
     * @test
     * @group action_signup
     */
    public function anonymous_user_can_access_signup_action()
    {
        $this->client->request('GET', '/signup');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    /**
     * @test
     * @group action_signup
     */
    public function it_redirect_logged_user_to_home_page()
    {
        $this->logIn(User::ROLE_MEMBER);

        $this->client->request('GET', '/signup');

        // Assert returned response is redirection.
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        // Assert redirection to home page "/".
        $this->assertStringEndsWith('/', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group action_signup
     */
    public function it_registers_new_user_with_valide_input()
    {
        $input = [
            'user_signup' => [
                'username' => 'jhonedoe',
                'password' => 123456,
                'email' => 'doe@example.com',
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('user_signup')->getValue(),
            ],
        ];

        /** @var \Bookkeeper\UserBundle\Entity\UserRepository $userRepository */
        $userRepository =  $this->getEntityManager()->getRepository('BookkeeperUserBundle:User');

        // Assert user table has no users.
        $this->assertCount(0, $userRepository->findAll(), 'User table has users.');

        $mailerMock = $this->mockMailer(['setTextBody', 'send']);

        $mailerMock
            ->expects($this->once())
            ->method('send')
        ;

        // Send POST request
        $this->client->request('POST', 'signup', $input);

        $users = $userRepository->findAll();

        // Assert user table has no users.
        $this->assertCount(1, $users, 'User table has no users.');

        // Assert user created with the provided input data.
        $this->assertEquals($input['user_signup']['username'], $users[0]->getUsername());
        $this->assertEquals($input['user_signup']['email'], $users[0]->getEmail());
        // Assert new created user has the default role.
        $this->assertEquals([User::ROLE_PENDING], $users[0]->getRoles());

        // Assert returned response is redirection.
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->client->getContainer()->get('session');

        // Assert Session FlashBag has success message.
        $this->assertTrue($session->getFlashBag()->has('success'), 'FlashBag has no "success" entry.');
        $this->assertEquals($session->getFlashBag()->get('success'), ['Your account successfully created']);

        $this->client->followRedirect();

        // Assert redirection to login page.
        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }
}
