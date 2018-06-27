<?php

namespace MailProvider\Service;

use Mandrill as MandrillLibrary;
use MailProvider\Provider\MailProvider;

/**
 * Mandrill
 *
 * Sends an e-mail with the Mandrill API.
 *
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.1.1
 * @since 1.0.0
 * @see https://mandrillapp.com/api/docs/messages.php.html#method-send the Mandrill API documentation.
 */
class Mandrill extends MailProvider
{
    
    /**
     * The Mandrill API.
     * @var MandrillLibrary
     */
    protected $client;

    /**
     * Custom parameters which can be provided to Mandrill.
     * @var array
     */
    private $customParameters = [];

    /**
     * Enable background sending mode.
     * @var boolean
     */
    private $aSync = false;

    /**
     * The name of the dedicated ip pool that should be used to send the message
     * @var string
     */
    private $ipPool;

    /**
     * When this message should be sent as a UTC timestamp in YYYY-MM-DD HH:MM:SS format.
     * @var string
     */
    private $sendAt;

    /**
     * The name of the mail service
     * @var string
     */
    protected $name = 'Mandrill';

    /**
     * Sets the client with the provided API key.
     * @param string $apiKey the client API key.
     */
    public function __construct($apiKey)
    {
        $this->client = new MandrillLibrary($apiKey);
    }

    /**
     * Sends the email through the Mandrill API.
     * @return array of structs for each recipient containing the key "email" with the email address, and details of the message status for that recipient.
     */
    protected function doSend()
    {
        return $this->client->messages->send($this->getMessage(), $this->getAsync(), $this->getIpPool(), $this->getSendAt());
    }

    /**
     * Converts the email data to the Mandrill API message format
     * and adds the custom parameters.
     * @return array containing the Mandrill API message.
     * @see https://mandrillapp.com/api/docs/messages.php.html#method-send section 'Parameters'.
     */
    public function getMessage()
    {
        $args = [
            'html' => $this->getHtml(),
            'text' => $this->getText(),
            'subject' => $this->getSubject(),
            'from_email' => $this->getFrom(),
            'from_name' => $this->getFromName(),
            'to' => $this->setToData(),
            'headers' => $this->getHeaders(),
            'attachments' => $this->setAttachmentData()
        ];

        return array_merge_recursive($args, $this->getCustom());
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
     * @return Mandrill
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
     * Converts the 'to', 'cc' and 'bcc' email addresses
     * to the desired format of the Mandril API.
     */
    private function setToData()
    {
        return array_merge (
            $this->setToOfType($this->getTos(), 'to'),
            $this->setToOfType($this->getCcs(), 'cc'),
            $this->setToOfType($this->getBccs(), 'bcc') 
        );
    }

    /**
     * Sets the email addresses with names of a given type
     * to the desired format of the Mandrill API.
     * @param array $addresses the email addresses.
     * @param strimg $type the email type ('to', 'cc', 'bcc').
     * @return array $to the formated email addresses.
     */
    public function setToOfType($addresses, $type){
        $to = [];

        foreach($addresses as $key => $address){
            $to[$key] = [
                'email' => $address['email'],
                'type' => $type
            ];

            if($address['name']){
                $to[$key]['name'] = $address['name'];
            }
        } 

        return $to; 
    }

    /**
     * Sets the attachment data for the email
     * in the desired format of the Mandrill API.
     */
    private function setAttachmentData()
    {
        $attachments = [];
        foreach($this->getAttachments() as $attachment){
            $attachments[] = [
                'content' => base64_encode(file_get_contents($attachment['file']->getRealPath())),
                'name' => $attachment['name'],
                'type' => $attachment['type']
            ];
        }
        return $attachments;
    }

    /**
     * Returns if the background sending mode is enabled.
     * @return boolean true if enabled, false if not.
     */
    public function getAsync()
    {
        return $this->aSync;
    }
    
    /**
     * Sets if the background sending mode is enabled.
     * @return Mandrill
     */
    public function setAsync(bool $aSync)
    {
        $this->aSync = $aSync;
        
        return $this;
    }

    /**
     * Returns the name of the dedicated ip pool that should be used to send the message.
     * @return string the dedicated ip pool that should be used to send the message.
     */
    public function getIpPool()
    {
        return $this->ipPool;
    }
    
    /**
     * Sets the name of the dedicated ip pool that should be used to send the message.
     * @return Mandrill
     */
    public function setIpPool($ipPool)
    {
        if (!is_string($ipPool)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($ipPool) ? get_class($ipPool) : gettype($ipPool))
            ));
        }

        $this->ipPool = $ipPool;

        return $this;
    }

    /**
     * Returns if this message should be sent as a UTC timestamp in YYYY-MM-DD HH:MM:SS format.
     * @return string $sendAt the timestamp.
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }
    
    /**
     * Sets if this message should be sent as a UTC timestamp in YYYY-MM-DD HH:MM:SS format
     * @return Mandrill
     */
    public function setSendAt($sendAt)
    {
        if (!is_string($sendAt)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($sendAt) ? get_class($sendAt) : gettype($sendAt))
            ));
        }

        $this->sendAt = $sendAt;

        return $this;

    }

}