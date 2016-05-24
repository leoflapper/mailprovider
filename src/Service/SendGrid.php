<?php

namespace MailProvider\Service;

use SendGrid as SendGridLibrary;
use SendGrid\Email as SendGridEmail;
use MailProvider\Provider\MailProvider;

/**
 * SendGrid
 *
 * Sends an e-mail with the Mandrill API.
 *
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.0.0
 * @see https://github.com/sendgrid/sendgrid-php The SendGrid PHP library.
 */
class SendGrid extends MailProvider
{
    /**
     * The SendGrid Library.
     * @var SendGridLibrary
     */
    protected $client;

    /**
     * Th SendGrid Email class.
     * @var SendGridEmail.
     */
    protected $email;

    /**
     * Sets the client with the provided API key.
     * @param string $apiKey the client API key.
     */
    public function __construct($apiKey)
    {
        $this->client = new SendGridLibrary($apiKey);
    }

    /**
     * Retrieves the SendGrid Email class.
     * @return SendGridEmail
     */
    public function getEmail()
    {
        if(!$this->email){
            $this->setNewEmail();
        }
        return $this->email;
    }

    /**
     * Sends the email through the SendGrid API.
     * @return SendGrid response object.
     */
    protected function doSend()
    {
        $response = null;

        $this->getEmail()
            ->setSubject($this->getSubject())
            ->setText($this->getText())
            ->setHtml($this->getHtml())
            ->setHeaders($this->getHeaders())
            ->setFrom($this->getFrom())
            ->setFromName($this->getFromName())
            ->setReplyTo($this->getReplyTo())
        ;

        $this->setToData();
        $this->setCcData();
        $this->setBccData();
        $this->setAttachmentData();

        if($response = $this->client->send($this->getEmail())){
            $this->setNewEmail();
        }

        return $response;
    }

    /**
     * Sets the 'to' email addresses to the desired SendGrid format.
     */
    private function setToData()
    {
        foreach($this->getTos() as $to){
            $this->getEmail()->addTo($to['email'], $to['name']);
        }  
    }

    /**
     * Sets the 'cc' email addresses to the desired SendGrid format.
     */
    private function setCcData()
    {
        foreach($this->getCcs() as $cc){
            $this->getEmail()->addCc($cc['email'], $cc['name']);
        }  
    }

    /**
     * Sets the 'bcc' email addresses to the desired SendGrid format.
     */
    private function setBccData()
    {
        foreach($this->getBccs() as $bcc){
            $this->getEmail()->addBcc($bcc['email'], $bcc['name']);
        } 
    }

    /**
     * Sets the attachments to the desired SendGrid format.
     */
    private function setAttachmentData()
    {
        foreach($this->getAttachments() as $attachment){
            $this->getEmail()->addAttachment($attachment['file']->getRealPath(), $attachment['name'], $attachment['type']);
        }  
    }

    /**
     * Sets a new SendGridEmail class.
     */
    public function setNewEmail()
    {
        $this->email = new SendGridEmail(); 
    }

}