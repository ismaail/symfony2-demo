<?php

namespace Bookkeeper\UserBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AccessListener
 * @package Bookkeeper\UserBundle\Security
 */
class AccessListener
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $authorizationCecker;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationCecker
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct($tokenStorage, $authorizationCecker, $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationCecker = $authorizationCecker;
        $this->router = $router;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->tokenStorage->getToken()
            || ! $this->authorizationCecker->isGranted('ROLE_PENDING')
        ) {
            return;
        }

        $skippedpaths = [
            '/logout',
            '/user/activate',
        ];

        if (in_array($this->router->getContext()->getPathInfo(), $skippedpaths)) {
            return;
        }

        // Redirect if role is "Pending"
        if ($this->authorizationCecker->isGranted('ROLE_PENDING')) {
            $url = $this->router->generate('account_activate');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
