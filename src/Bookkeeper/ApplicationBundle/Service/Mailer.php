<?php

namespace Bookkeeper\ApplicationBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Bookkeeper\ApplicationBundle\Exception\ApplicationException;

/**
 * Class Mailer
 * @package Bookkeeper\UserBundle\Service
 */
class Mailer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    protected $emailParams;

    /**#@+
     * @var string
     */
    private $htmlBody;
    private $textBody;
    /**#@-*/

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->prepareEmailParams();
    }

    /**
     * Get email configured params
     *
     * @throws ApplicationException
     */
    protected function prepareEmailParams()
    {
        $this->emailParams = $this->container->getParameter('email');

        if (! isset($this->emailParams['address'])) {
            throw new ApplicationException("Email sender address param no defined");
        }

        if (! isset($this->emailParams['name'])) {
            throw new ApplicationException("Email sender name param no defined");
        }
    }

    /**
     * @param string $body
     *
     * @return Mailer
     */
    public function setHtmlBody($body)
    {
        $this->htmlBody = $body;

        return $this;
    }

    /**
     * @param string $body
     *
     * @return Mailer
     */
    public function setTextBody($body)
    {
        $this->textBody = $body;

        return $this;
    }

    /**
     * Send the email
     *
     * @param string $to
     * @param string $subject
     *
     * @throws ApplicationException
     */
    public function send($to, $subject)
    {
        /** @var \Swift_Mailer $mailer */
        $mailer  = $this->container->get('mailer');

        /** @var \Swift_Message $message */
        $message = $mailer->createMessage();
        $message->setSubject($subject)
                ->setFrom($this->emailParams['address'], $this->emailParams['name'])
                ->setTo($to);

        if (null === $this->htmlBody && null === $this->textBody) {
            throw new ApplicationException("Message body not set");
        }

        if (null !== $this->htmlBody) {
            $message->addPart($this->htmlBody, 'text/html');
        }

        if (null !== $this->textBody) {
            $message->addPart($this->textBody, 'text/plain');
        }

        $mailer->send($message);
    }
}
