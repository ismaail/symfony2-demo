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
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $security;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContext $security
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct($security, $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->security->getToken()
            || ! $this->security->isGranted('ROLE_PENDING')
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
        if ($this->security->isGranted('ROLE_PENDING')) {
            $url = $this->router->generate('account_activate');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
