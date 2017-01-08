<?php

namespace MailProvider\Provider;

/**
 * The Mail Interface
 *
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.1.1
 * @since 1.0.0
 */
interface MailInterface
{

    /**
     * Sends the e-mail.
     * @return mixed
     */
    public function send(); 

    /**
     * Retrieves the 'to' email addresses.
     * @return array
     */
    public function getTos();

    /**
     * Returns the from email address.
     * @return string
     */
    public function getFrom();

    /**
     * Returns the name of the from email address.
     * @return string
     */
    public function getFromName();

    /**
     * The email address to reply to.
     * @return string
     */
    public function getReplyTo();

    /**
     * Returns the 'cc' email addresses.
     * @return array
     */
    public function getCcs();

    /**
     * Returns the 'bcc' email addresses.
     * @return array
     */
    public function getBccs();

    /**
     * Returns the subject of the email.
     * @return string
     */
    public function getSubject();

    /**
     * Returns the email text.
     * @return string
     */
    public function getText();

    /**
     * Returns the HTML content of the email.
     * @return string
     */
    public function getHtml();

    /**
     * Returns the email attachments
     * @return array
     */
    public function getAttachments();

    /**
     * Returns the headers of the email.
     * @return array
     */
    public function getHeaders();

}