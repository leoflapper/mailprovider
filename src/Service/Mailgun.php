<?php

namespace MailProvider\Service;

use Mailgun\Mailgun as MailgunLibrary;
use Http\Adapter\Guzzle6\Client as Guzzle6Client;
use MailProvider\Provider\MailProvider;

/**
 * Mailgun
 *
 * Sends an e-mail with the Mailgun API.
 *
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.0.0
 * @see https://github.com/mailgun/mailgun-php.
 */
class Mailgun extends MailProvider
{
    
    /**
     * The Mailgun Library.
     * @var MailgunLibrary
     */
    protected $client;

    /**
     * The domain to send the email from.
     * @var string
     */
    protected $domain;

    /**
     * Custom parameters which can be provided to Mailgun.
     * @var array
     */
    public $customParameters = [];

    /**
     * Sets the client with the provided API key.
     * Because of a Puli factory issue the Guzzle6Client is given
     * as HTTP request object.
     * @see https://github.com/mailgun/mailgun-php/issues/116 Puli factory issue
     * @param string $apiKey the client API key.
     */
    public function __construct($apiKey)
    {
        $this->client = new MailgunLibrary($apiKey, new Guzzle6Client());
    }

    /**
     * Returns the domain to send the email from.
     * @return string $domain the domain.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the domain to send the emails from.
     * @param string $domain the domain.
     * @return Mailgun
     */
    public function setDomain($domain)
    {
        if (!is_string($domain)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($domain) ? get_class($domain) : gettype($domain))
            ));
        }

        $this->domain = $domain;

        return $this;
    }

    /**
     * Sends the email through the Mailgun API.
     * @return \stdClass the Mailgun API response object.
     */
    public function doSend()
    {
        if(!$this->getDomain()){
            throw new \Exception('Domain not provided for sending a message through Mailgun. Please set a domain by using the "setDomain($domain)" method.');
        }
        
        return $this->client->sendMessage($this->getDomain(), $this->getMessage(), $this->getFiles());
    }

    /**
     * Converts the email data to the Mailgun API message format
     * and adds the custom parameters.
     * @return array containing the Mailgun API message.
     * @see https://github.com/mailgun/mailgun-php#user-content-usage the usage of the library contains the parameters.
     */
    public function getMessage()
    {
        $args = [
            'from' => $this->setFromData(),
            'to' => $this->setToData(),
            'subject' => $this->getSubject()
        ];

        if($ccData = $this->setCcData()){
            $args['cc'] = $ccData;
        }

        if($bccData = $this->setBccData()){
            $args['bcc'] = $bccData;
        }

        if($this->getText()){
            $args['text'] = $this->getText();
        }

        if($this->getHtml()){
            $args['html'] = $this->getHtml();
        }

        return array_merge_recursive($args, $this->getCustom());
    }

    /**
     * Adds the attachments to the email.
     * @return array containg the attachment files.
     */
    public function getFiles()
    {
        $files = [];
        if($attachmentData = $this->setAttachmentData()){
            $files['attachment'] = $this->setAttachmentData();
        }
        return $files;
    }

    /**
     * Retrieves the custom parameters.
     * @return array the customer parameters.
     */
    public function getCustom()
    {
        return $this->customParameters;
    }

    /**
     * Adds a custom parameter by key value
     * @param string $key  the parameter name.
     * @param mixed $value the parameter value(s).
     * @return Mailgun
     */
    public function addCustom($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($key) ? get_class($key) : gettype($key))
            ));
        }

        $this->customParameters[$key] = $value;

        return $this;
    }

    /**
     * Sets the from email and from name in the desired Mailgun format.
     */
    private function setFromData()
    {
        return $this->setEmailNameString($this->getFrom(), $this->getFromName());
    }    

    /**
     * Sets the 'to' email addresses in the desired Mailgun format.
     */
    private function setToData()
    {
        if($this->getTos()){
            $toArray = [];
            foreach($this->getTos() as $to){
                $toArray[] = $this->setEmailNameString($to['email'], $to['name']);
            } 

            return implode(',', $toArray);  
        }
        
        return '';
    }

    /**
     * Sets the 'cc' email addresses in the desired Mailgun format.
     */
    private function setCcData()
    {
        if($this->getCcs()){
            $ccArray = [];
            foreach($this->getCcs() as $cc){
                $ccArray[] = $this->setEmailNameString($cc['email'], $cc['name']);
            } 

            return implode(',', $ccArray);  
        }
        
        return '';
    }

    /**
     * Sets the 'bcc' email addresses in the desired Mailgun format.
     */
    private function setBccData()
    {
        if($this->getBccs()){
            $bccArray = [];
            foreach($this->getBccs() as $bcc){
                $bccArray[] = $this->setEmailNameString($bcc['email'], $bcc['name']);
            }
            return implode(',', $bccArray);   
        }

        return '';
        
    }

    /**
     * Sets the given email and name in a string format.
     * Example: Leo Flapper <info@leoflapper.nl>.
     * @param string $email the email address.
     * @param string $name  the name.
     */
    private function setEmailNameString($email, $name = null)
    {
        if (!is_string($email)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($email) ? get_class($email) : gettype($email))
            ));
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($name) ? get_class($name) : gettype($name))
            ));
        }

        $result = $email;

        if($name){
            $result = sprintf('%s <%s>', $name, $email);
        }

        return $result;
    }

    /**
     * Sets the attachment data in the desired Mailgun format.
     */
    private function setAttachmentData()
    {
        $attachments = [];
        if($this->attachments){
            foreach($this->attachments as $attachment){
                $attachments[] = $attachment['file']->getRealPath();
            }
        }
        return $attachments;
    }

}