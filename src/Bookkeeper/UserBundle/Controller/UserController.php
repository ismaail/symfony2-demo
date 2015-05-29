<?php

namespace Bookkeeper\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bookkeeper\UserBundle\Form\RegistrationType;
use Bookkeeper\UserBundle\Entity;

/**
 * Class UserController
 * @package Bookkeeper\UserBundle\Controller
 */
class UserController extends Controller
{
    /**
     * @var \Bookkeeper\UserBundle\Model\UserModel
     */
    protected $userModel;

    /**
     * SignUp Action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signUpAction(Request $request)
    {
        // If user is logged in,
        // then redirect to "home" page
        if ($this->isLoggedIn()) {
            return $this->redirectToRoute('home');
        }

        $user = new Entity\User();
        $form = $this->createSignupTypeForm($user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Create new user account
                $this->getUserModel()->create($user);
                $this->sendActivationEmail($user);

                $this->get('session')->getFlashBag()->add('success', 'Your account successfully created');
                return $this->redirectToRoute('login');

            } else {
                $this->get('session')->getFlashBag()->add('error', 'Invalid form inputs');
            }
        }

        return $this->render('BookkeeperUserBundle:User:signup.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Activate pending user account
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function activateAction(Request $request)
    {
        $token = $request->get('token');

        if (null !== $token) {
            /** @var Entity\User $user */
            $user = $this->get('security.context')->getToken()->getUser();

            if ($token === $user->getToken()) {
                // Activate user account
                $this->getUserModel()->activate($user);

                $this->get('session')->getFlashBag()->add('success', 'Your account has been successfully activated');

                if ($this->isLoggedIn()) {
                    return $this->redirectToRoute('home');
                } else {
                    return $this->redirectToRoute('login');
                }

            } else {
                $this->get('session')->getFlashBag()->add('error', 'Wrong token value');
            }
        }

        return $this->render('BookkeeperUserBundle:User:activate.html.twig');
    }

    /**
     * Create SignUp form
     *
     * @param Entity\User $user
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createSignupTypeForm(Entity\User $user)
    {
        $form = $this->createForm(new RegistrationType(), $user, array(
            'action' => $this->generateUrl('signup'),
            'method' => 'post',
        ));

        $form->add('submit', 'submit', array('label' => 'Sign-up'));

        return $form;
    }

    /**
     * @return \Bookkeeper\UserBundle\Model\UserModel
     */
    private function getUserModel()
    {
        if (null === $this->userModel) {
            $this->userModel = $this->get('user_model');
        }

        return $this->userModel;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    private function isLoggedIn()
    {
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')
            || $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     * @param Entity\User $user
     *
     * @throws \Bookkeeper\ApplicationBundle\Exception\ApplicationException
     */
    private function sendActivationEmail($user)
    {
        // Send email activation
        /** @var \Bookkeeper\ApplicationBundle\Service\Mailer $mailer */
        $mailer = $this->get('app_mailer');

        $mailer->setTextBody($this->renderView(
            'BookkeeperUserBundle:Email:activate_account.html.twig',
            array('user' => $user)
        ));

        $mailer->send($user->getEmail(), 'Bookkeeper.dev - Activate your account');
    }
}
