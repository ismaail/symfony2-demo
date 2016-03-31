<?php

namespace Bookkeeper\ApplicationBundle\Service;

use Bookkeeper\ApplicationBundle\Exception\ApplicationException;
use Swift_Mailer;

/**
 * Class Mailer
 * @package Bookkeeper\UserBundle\Service
 */
class Mailer
{
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
     * @var bool
     */
    private $pretend = false;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @param array $parameters
     * @param Swift_Mailer $mailer
     */
    public function __construct(array $parameters, Swift_Mailer $mailer)
    {
        $this->parameters = $parameters;
        $this->mailer = $mailer;

        $this->checkEmailParams();
    }

    /**
     * Get email configured params
     *
     * @throws ApplicationException
     */
    protected function checkEmailParams()
    {
        if (! isset($this->parameters['address'])) {
            throw new ApplicationException("Sender email address is not defined");
        }

        if (! isset($this->parameters['name'])) {
            throw new ApplicationException("Sender name is not defined");
        }

        if (isset($this->parameters['pretend'])) {
            $this->pretend = $this->parameters['pretend'];
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
        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message
            ->setSubject($subject)
            ->setFrom($this->parameters['address'], $this->parameters['name'])
            ->setTo($to)
        ;

        $this->setMessageParts($message);

        if ($this->pretend) {
            return;
        }

        $this->mailer->send($message);
    }

    /**
     * @param \Swift_Message $message
     *
     * @throws ApplicationException
     */
    private function setMessageParts($message)
    {
        if (null === $this->htmlBody && null === $this->textBody) {
            throw new ApplicationException("Message body not set");
        }

        if (null !== $this->htmlBody) {
            $message->addPart($this->htmlBody, 'text/html');
        }

        if (null !== $this->textBody) {
            $message->addPart($this->textBody, 'text/plain');
        }
    }
}
