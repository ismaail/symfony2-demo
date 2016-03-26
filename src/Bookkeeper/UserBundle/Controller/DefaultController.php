<?php

namespace Bookkeeper\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package Bookkeeper\UserBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * Login action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        if ($this->isLoggedIn()) {
            return $this->redirectToRoute('home');
        }

        /** @var \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils */
        $authenticationUtils = $this->get('security.authentication_utils');

        // Get login error
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user
        $lastUserName = $authenticationUtils->getLastUsername();

        return $this->render('BookkeeperUserBundle:Default:login.html.twig', [
            'last_username' => $lastUserName,
            'error' => $error,
        ]);
    }

    /**
     * LoginCheck action
     *
     * Not executed as the route is handled by the Security system
     */
    public function loginCheckAction()
    {
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
}
