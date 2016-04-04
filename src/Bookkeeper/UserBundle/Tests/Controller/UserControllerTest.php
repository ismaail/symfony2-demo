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
     * @param User $source
     */
    private function createUser(User $source)
    {
        $user = new User();
        $user
            ->setUsername($source->getUsername())
            ->setPassword('password')
            ->setRoles($source->getRoles())
            ->setToken($source->getToken())
            ->setEmail('test@example.com')
        ;

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
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

    /**
     * @test
     * @group action_signup
     */
    public function it_returns_signup_page_with_errors_if_invalid_input()
    {
        $input = [
            'user_signup' => [
                'username' => '',
                'password' => [
                    'first' => 123456789,
                ],
                'email' => 'doe@example',
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('user_signup')->getValue(),
            ],
        ];

        // BookModel Mock
        $bookModel = $this->getBookModelMock(['create']);
        $bookModel
            ->expects($this->never())
            ->method('create')
        ;

        // Mailer Mock
        $mailerMock = $this->mockMailer(['setTextBody', 'send']);
        $mailerMock
            ->expects($this->never())
            ->method('send')
        ;

        // Send POST request
        $crawler = $this->client->request('POST', 'signup', $input);

        // Assert returned response is redirection.
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->client->getResponse()->isRedirect());

        /** @var \DOMElement $item */
        $errors = $crawler->filter('form .alert.alert-danger');

        // Assert error messages.
        $this->assertEquals(
            $errors->getNode(0)->nodeValue,
            'This value should not be blank.',
            'Wrong error message for username field.'
        );
        $this->assertEquals(
            $errors->getNode(1)->nodeValue,
            'Passwords do not match',
            'Wrong error message for password field.'
        );
        $this->assertEquals(
            $errors->getNode(3)->nodeValue,
            'This value is not a valid email address.',
            'Wrong error message for email field.'
        );
    }

    /**
     * @test
     * @group action_activate
     */
    public function activate_action_redirect_anonymous_user_to_login()
    {
        $this->client->request('GET', '/user/activate?token=valide_token');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertStringEndsWith('/login', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group action_activate
     */
    public function role_member_can_not_access_activate_action()
    {
        $this->logIn(User::ROLE_MEMBER);

        $this->client->request('GET', '/user/activate?token=valide_token');

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group action_activate
     */
    public function role_admin_can_not_access_activate_action()
    {
        $this->logIn(User::ROLE_ADMIN);

        $this->client->request('GET', '/user/activate?token=valide_token');

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @group action_activate
     */
    public function it_returns_normal_page_if_no_activation_token_is_provided()
    {
        $this->logIn(User::ROLE_PENDING, 'valide-token');

        $crawler = $this->client->request('GET', '/user/activate');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertCount(0, $crawler->filter('.alert.alert-danger'));
    }

    /**
     * @test
     * @group action_activate
     */
    public function it_activates_pending_user()
    {
        $user = $this->logIn(User::ROLE_PENDING, 'valide-token');
        $this->createUser($user);

        /** @var \Bookkeeper\UserBundle\Model\UserModel $userModel */
        $userModel = $this->getContainer()->get('user_model');

        // Assert logged user has role pending before activation
        $user = $userModel->findById(1);
        $this->assertEquals([User::ROLE_PENDING], $user->getRoles(), 'User has not the role PENDING.');

        $this->client->request('GET', '/user/activate?token=valide-token');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/'));

        // Assert logged role has been activated.
        $user = $userModel->findById(1);
        $this->assertEquals([User::ROLE_MEMBER], $user->getRoles(), 'User has not the role MEMBER.');

        $this->client->followRedirect();

        $this->assertStringEndsWith('/', $this->client->getHistory()->current()->getUri());
    }

    /**
     * @test
     * @group action_activate
     */
    public function it_returns_error_if_wrong_token_activation()
    {
        $this->logIn(User::ROLE_PENDING, 'valide-token');

        $crawler = $this->client->request('GET', '/user/activate?token=wrong-token');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(
            'Wrong token value',
            $crawler->filter('.alert.alert-danger')->getNode(0)->nodeValue
        );
    }
}
