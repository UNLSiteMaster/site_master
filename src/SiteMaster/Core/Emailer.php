<?php
namespace SiteMaster\Core;

class Emailer
{
    public $email = null;

    /**
     * @param EmailInterface $email
     */
    public function __construct(EmailInterface $email)
    {
        $this->email = $email;
    }

    /**
     * Get the from address
     * 
     * @return array
     */
    public function getFrom()
    {
        return array(Config::get('EMAIL_FROM') => Config::get('SITE_TITLE'));
    }

    /**
     * Get the body of the message
     * 
     * @return string
     */
    public function getBody()
    {
        $savvy = new OutputController(
            array(
                'format' => 'email',
            )
        );
        $savvy->setTheme(Config::get('THEME'));
        $savvy->initialize();

        return $savvy->render($this);
    }

    /**
     * Get the swiftmailer mail transport to send with
     * 
     * @return \Swift_MailTransport
     */
    public function getTransport()
    {
        return \Swift_MailTransport::newInstance();
    }

    /**
     * Send the email
     * 
     * @return int
     */
    public function send()
    {
        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($this->getTransport());

        // Create a message
        $message = \Swift_Message::newInstance(Config::get('SITE_TITLE') . ': ' . $this->email->getSubject())
            ->setFrom((array)$this->getFrom())
            ->setTo((array)$this->email->getTo())
            ->setBody($this->getBody(), 'text/html')
        ;

        // Send the message
        return $mailer->send($message);
    }
}