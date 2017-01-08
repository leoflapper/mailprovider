<?php

namespace MailProvider\Service;

use SendGrid as SendGridLibrary;
use SendGrid\Mail;
use SendGrid\Email;
use SendGrid\Personalization;
use SendGrid\Content;
use SendGrid\Attachment;
use MailProvider\Provider\MailProvider;

/**
 * SendGrid
 *
 * Sends an e-mail with the SendGrid V3 API.
 *
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.1.1
 * @since 1.0.0
 * @see https://github.com/sendgrid/sendgrid-php The SendGrid PHP library.
 */
class SendGrid extends MailProvider
{
    /**
     * The SendGrid Library.
     * @var SendGridLibrary
     */
    protected $SendGrid;

    /**
     * The SendGrid Email class.
     * @var Mail.
     */
    protected $email;

    /**
     * The SendGrid Personalization class.
     * @var Personalization.
     */
    protected $personalization;

    /**
     * Sets the SendGrid class with the provided API key.
     * @param string $apiKey the SendGrid API key.
     */
    public function __construct($apiKey)
    {
       $this->SendGrid = new SendGridLibrary($apiKey);
    }

    /**
     * Retrieves the SendGrid Email class.
     * @return Mail
     */
    public function getEmail()
    {
        if(!$this->email){
            $this->setNewEmail();
        }
        return $this->email;
    }
    
    /**
     * Returns the personalization class.
     * @return Personalization the personalization class.
     */
    public function getPersonalization()
    {
        if(!$this->personalization){
            $this->setNewPersonalization();
        }
        return $this->personalization;
    }

    /**
     * Sends the email through the SendGrid API.
     * @return SendGrid response object.
     */
    protected function doSend()
    {
        $response = null;
        
        $this->getEmail()->setSubject($this->getSubject());

        if($text = $this->getText()){
            $this->getEmail()->addContent(new Content("text/plain", $text));
        }

        if($html = $this->getHtml()){
            $this->getEmail()->addContent(new Content("text/html", $html));
        }
        
        $this->getEmail()->setFrom(new Email($this->getFromName(), $this->getFrom()));
        $this->getEmail()->setReplyTo(new Email('', $this->getReplyTo()));
        $this->setToData();
        $this->setCcData();
        $this->setBccData();
        $this->setAttachmentData();
        $this->setHeaders();
        $this->getEmail()->addPersonalization($this->getPersonalization());
        
        if($response = $this->SendGrid->client->mail()->send()->post($this->getEmail())){
            $this->setNewEmail();
            $this->setNewPersonalization();
        }
        
        return $response;
    }

    /**
     * Sets the 'to' email addresses to the desired SendGrid format.
     */
    private function setToData()
    {
        foreach($this->getTos() as $to){
            $email = new Email($to['name'], $to['email']);
            $this->getPersonalization()->addTo($email);
        }  
    }

    /**
     * Sets the 'cc' email addresses to the desired SendGrid format.
     */
    private function setCcData()
    {
        foreach($this->getCcs() as $cc){
            $email = new Email($cc['name'], $cc['email']);
            $this->getPersonalization()->addCc($email);
        }  
    }

    /**
     * Sets the 'bcc' email addresses to the desired SendGrid format.
     */
    private function setBccData()
    {
        foreach($this->getBccs() as $bcc){
            $email = new Email($bcc['name'], $bcc['email']);
            $this->getPersonalization()->addBcc($email);
        } 
    }

    /**
     * Sets the email headers.
     */
    private function setHeaders()
    {
        foreach($this->getHeaders() as $key => $value){
            $this->getEmail()->addHeader($key, $value);
        } 
    }

    /**
     * Sets the attachments to the desired SendGrid format.
     */
    private function setAttachmentData()
    {
        foreach($this->getAttachments() as $attachmentData){
            $attachment = new Attachment();
            $attachment->setContent(base64_encode(file_get_contents($attachmentData['file']->getRealPath())));
            $attachment->setType($attachmentData['type']);
            $attachment->setFilename($attachmentData['name']);
            $attachment->setDisposition("attachment");
            $this->getEmail()->addAttachment($attachment);
        }  
    }

    /**
     * Sets a new Mail class.
     */
    public function setNewEmail()
    {
        $this->email = new Mail(); 
    }

    /**
     * Sets a new Personalization class.
     * @see https://sendgrid.com/docs/Classroom/Send/v3_Mail_Send/personalizations.html Explanation of Personalizations.
     */
    public function setNewPersonalization()
    {
        $this->personalization = new Personalization(); 
    }

}