<?php

namespace MailProvider\Service;

use MailProvider\Provider\MailProvider;

/**
 * SMTP
 *
 * Sends an e-mail through SMTP.
 * Class based on Snipworks PHP SMTP class.
 *
 * @author SnipWorks <jm.factorin@gmail.com>
 * @author Leo Flapper <info@leoflapper.nl>
 * @version 1.1.0
 * @since 1.1.0
 * @see https://github.com/snipworks/php-smtp Simple PHP SMTP Mail Send Source.
 */
class SMTP extends MailProvider
{
    /**
     * SMTP line break.
     */
    const CRLF = "\r\n";

    /**
     * TLS protocol.
     */
    const TLS = 'tcp';

    /**
     * SSL protocol.
     */
    const SSL = 'ssl';

    /**
     * Success status.
     */
    const OK = 250;

    /**
     * The server address.
     * @var string
     */
    protected $server;

    /**
     * The server port.
     * @var integer
     */
    protected $port = 25;

    /**
     * The localhost server address.
     * @var string
     */
    protected $localhost;

    /**
     * Socket for the server connection.
     * @var resource
     */
    protected $socket;

    /**
     * The protocol to use.
     * @var string
     */
    protected $protocol = 'ssl';
    
    /**
     * If TLS protocol is used
     * @var boolean
     */
    protected $tls = false;

    /**
     * The username required for login.
     * @var string
     */
    protected $username = '';

    /**
     * The password required for login.
     * @var string
     */
    protected $password = '';

    /**
     * The connection timeout time in seconds.
     * @var integer
     */
    protected $connectionTimeout = 30;

    /**
     * The response timeout in seconds.
     * @var integer
     */
    protected $responseTimeout = 8;

    /**
     * The content type
     * @var string
     */
    protected $contentType;

    /**
     * The content character set to use
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Contains log data.
     * @var array
     */
    protected $log = [];

    /**
     * The stream context options.
     * @var array
     */
    protected $options = [];


    /**
     * Sets the default values.
     * @param $server the server address
     * @param int $port optional port number
     */
    public function __construct($server, $port = null)
    {
        $this->setServer($server);
        $this->setLocalhost($this->getServer());
        
        if($port){
            $this->setPort($port);
        }
        
        $this->addHeader('MIME-Version', '1.0');
        $this->addHeader('Content-type', 'text/plain; charset=' . $this->getCharset());
    }

    /**
     * Returns the server address.
     * @return string the server address
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Sets the server address.
     * @param string $server the server address
     */
    public function setServer($server)
    {
        if (!is_string($server)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($server) ? get_class($server) : gettype($server))
            ));
        }

        $this->server = $server;

        return $this;
    } 

    /**
     * Returns the server url with protocol.
     * @return string the server url with protocol
     */
    protected function getServerUrl()
    {
        return ($this->getProtocol()) ? $this->getProtocol() . '://' . $this->getServer() : $this->getServer();
    }

    /**
     * Returns the port number.
     * @return integer the port number.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the port number.
     * @param integer $port the port number.
     */
    public function setPort($port)
    {
        if (!is_numeric($port)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a numeric argument; received "%s"',
                __METHOD__,
                (is_object($port) ? get_class($port) : gettype($port))
            ));
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Returns the localhost server address.
     * @return string the localhost server address
     */
    public function getLocalhost()
    {
        return $this->localhost;
    }

    /**
     * Sets the localhost server address.
     * @param string $localhost the localhost server address
     */
    public function setLocalhost($localhost)
    {
        if (!is_string($localhost)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($localhost) ? get_class($localhost) : gettype($localhost))
            ));
        }

        $this->localhost = $localhost;

        return $this;

    }

    /**
     * Returns the connection timeout.
     * @return integer the connection timeout in seconds
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * Sets the connection timeout
     * @param integer $connectionTimeout the connection timeout in seconds
     */
    public function setConnectionTimeout($connectionTimeout)
    {
        if (!is_numeric($connectionTimeout)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a numeric argument; received "%s"',
                __METHOD__,
                (is_object($connectionTimeout) ? get_class($connectionTimeout) : gettype($connectionTimeout))
            ));
        }

        $this->connectionTimeout = (int)$connectionTimeout;

        return $this;
    }

    /**
     * Returns the response timeout.
     * @return integer the response timeout in seconds
     */
    public function getResponseTimeout()
    {
        return $this->responseTimeout;
    }

    /**
     * Sets the response timeout.
     * @param integer $responseTimeout the response timeout in seconds
     */
    public function setResponseTimeout($responseTimeout)
    {
        if (!is_numeric($responseTimeout)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a numeric argument; received "%s"',
                __METHOD__,
                (is_object($responseTimeout) ? get_class($responseTimeout) : gettype($responseTimeout))
            ));
        }

        $this->responseTimeout = (int)$responseTimeout;

        return $this;
    }

    /**
     * Returns the content type.
     * @return string the content type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sets the content type.
     * @param string $contentType the content type
     */
    public function setContentType($contentType)
    {
        if (!is_string($contentType)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($contentType) ? get_class($contentType) : gettype($contentType))
            ));
        }

        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Sets SMTP Login authentication
     * @param string $username the username
     * @param string $password the password
     */
    public function setLogin($username, $password)
    {
        if (!is_string($username)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($username) ? get_class($username) : gettype($username))
            ));
        }

        if (!is_string($password)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($password) ? get_class($password) : gettype($password))
            ));
        }

        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * Returns the content character set.
     * @return string the content character set
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Sets the content character set.
     * @param string $charset the content character set
     */
    public function setCharset($charset)
    {
        if (!is_string($charset)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($charset) ? get_class($charset) : gettype($charset))
            ));
        }

        $this->charset = $charset;

        return $this;
    }

    /**
     * Returns the server protocol.
     * @return string the server protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Sets the server protocol.
     * @param string $protocol the server protocol
     */
    public function setProtocol($protocol)
    {
        if (!is_string($protocol)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($protocol) ? get_class($protocol) : gettype($protocol))
            ));
        }

        if($protocol === self::TLS){
            $this->tls = true;
        }
        
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * {@inheritdoc }. Sets the message of the email.
     * @param @param string $html the HTML of the email.
     */
    public function setHtml($html)
    {
        if (!is_string($html)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($html) ? get_class($html) : gettype($html))
            ));
        }

        $this->html = $html;
        
        $this->setMessage($html, true);
        
        return $this;
    }

    /**
     * {@inheritdoc }. Sets the message of the email.
     * @param string $text the text of the email.
     */
    public function setText($text)
    {
        if (!is_string($text)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($text) ? get_class($text) : gettype($text))
            ));
        }

        $this->text = $text;
        
        $this->setMessage($text, false);
        
        return $this;
    }

    /**
     * Returns the message of the email.
     * @return string the message of the email
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message of the email.
     * @param string $message the message of the email
     * @param bool $html if html or not
     */
    protected function setMessage($message, $html = false)
    {
        $this->message = $message;
        if ($html) {
            $this->addHeader('Content-type', 'text/html; charset=' . $this->getCharset());
        }
    }

    /**
     * Returns the log data. 
     * Contains commands and responses from SMTP server
     * @return array
     */
    public function getLog($key = null)
    {
        $result = $this->log;
        if($key){
            if(isset($this->log[$key])){
                $result = $this->log[$key];
            }
        }
        return $result;
    }

    /**
     * Logs a value.
     * @param  string $key  the log key
     * @param  mixed $value the log value
     */
    protected function log($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($key) ? get_class($key) : gettype($key))
            ));
        }

        $this->log[$key] = $value;
    }

    /**
     * Returns the stream context options.
     * @return array the stream context options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the stream context options.
     * @param array $options the stream context options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Send email to recipient via mail server
     * @return array the log array or an empty array if not send
     */
    public function doSend()
    {
        if (!$this->getSocket()) {
            return [];
        }

        $this->startConnection();
        $this->sendConnectionCredentials();
        $this->sendFrom();    
        $this->sendHeaders();
        $this->log('QUIT', $this->sendCommand('QUIT'));
        $this->closeSocket();
        
        return $this->getLog();
    }

    /**
     * Returns the server socket connection.
     * @return resource the server server connection
     */
    public function getSocket()
    {
        if(!$this->socket){
            $this->openSocket();
        }
        return $this->socket;
    }

    /**
     * Open Internet or Unix domain socket connection.
     * @return void
     */
    protected function openSocket()
    {
        $errorNumber = 0;
        $errorString = '';

        $this->socket = stream_socket_client(
            $this->getServerUrl().':'.$this->port, 
            $errorNumber, 
            $errorString, 
            $this->getConnectionTimeout(),
            STREAM_CLIENT_CONNECT,
            stream_context_create($this->getOptions())
        );
    }

    /**
     * Closes the Internet or Unix domain socket connection 
     * @return void
     */
    protected function closeSocket()
    {
        if($this->socket){
            fclose($this->socket);
        }
    }

    /**
     * Starts the connection with the server.
     * @return void
     */
    protected function startConnection()
    {
        $this->log('CONNECTION', $this->getResponse());
        $this->log('HELLO', $this->sendCommand('EHLO ' . $this->getLocalhost()));

        if($this->tls){
            $this->log('STARTTLS', $this->sendCommand('STARTTLS'));
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->log('HELLO 2', $this->sendCommand('EHLO ' . $this->getLocalhost()));
        }
    }

    /**
     * Sends the connection credentials if set.
     * @return void
     */
    protected function sendConnectionCredentials()
    {
        if(isset($this->username) && isset($this->password)){
            $this->log('AUTH', $this->sendCommand('AUTH LOGIN'));
            $this->log('USERNAME', $this->sendCommand(base64_encode($this->username)));
            $this->log('PASSWORD', $this->sendCommand(base64_encode($this->password)));
        }
    }

    /**
     * Sends the sender of the email
     * @return void
     */
    protected function sendFrom()
    {
        $options = $this->getOptions();
        
        $verifyPeer = false;
        if(isset($options['ssl']['verify_peer'])){
            $verifyPeer = $options['ssl']['verify_peer'];
        }

        $verifyPeerName = false;
        if(isset($options['ssl']['verify_peer_name'])){
            $verifyPeerName = $options['ssl']['verify_peer_name'];
        }
        
        $useVerifyPeer = ($verifyPeer ? ' XVERP' : '');
        $this->log('MAIL_FROM', $this->sendCommand('MAIL FROM:<' . $this->getFrom() . '> ' . $useVerifyPeer));
        $this->log('VRFY', $this->sendCommand('VRFY ' . $verifyPeerName));

        $recipients = [];
        foreach (array_merge($this->getTos(), $this->getCcs()) as $address) {
           $recipients[] = $this->sendCommand('RCPT TO: <' . $address['email'] . '>');
        }
        $this->log('RECIPIENTS', $recipients);

    }   

    /**
     * Sends the email headers.
     * @return void
     */
    protected function sendHeaders()
    {
        $data = [];
        $data[1] = $this->sendCommand('DATA');

        if ($this->getContentType()) {
            $this->addHeader('Content-type', $this->getContentType());
        }

        $this->addHeader('From', $this->formatAddress($this->getFrom(), $this->getFromName()));
        $this->addHeader('To', $this->formatAddressList($this->getTos()));
        
        if ($this->getCcs()) {
            $this->addHeader('Cc', $this->formatAddressList($this->getCcs()));
        }

        if ($this->getBccs()) {
            $this->addHeader('Bcc', $this->formatAddressList($this->getBccs()));
        }

        if ($this->getReplyTo()) {
            $this->addHeader('Reply-To', $this->getReplyTo());
        }

        $this->addHeader('Subject', $this->getSubject());
        $this->addHeader('Date', date('r'));
       
        $headers = '';
        foreach ($this->getHeaders() as $key => $val) {
            $headers .= $key . ': ' . $val . self::CRLF;
        }

        $data[2] = $this->sendCommand($headers . self::CRLF . $this->getMessage() . self::CRLF . '.');
        $this->log('DATA', $data);
    }

    /**
     * Sends a command to the mail server.
     * @param string $command the command to send
     * @return string the response
     */
    protected function sendCommand($command)
    {
        if (!is_string($command)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($command) ? get_class($command) : gettype($command))
            ));
        }

        // TODO: Error checking
        fputs($this->getSocket(), $command . self::CRLF);
        return $this->getResponse();
    }

    /**
     * Returns the mail server response.
     * @return string the response
     */
    protected function getResponse()
    {
        stream_set_timeout($this->getSocket(), $this->getResponseTimeout());
        $response = '';
        while (($line = fgets($this->getSocket(), 515)) != false) {
            $response .= trim($line) . "\n";
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return trim($response);
    }

    /**
     * Formats a list of email addresses
     * @param array list of email addresses
     * @return string the formatted email addresses
     */
    protected function formatAddressList(array $addresses)
    {
        $list = '';
        foreach ($addresses as $address) {
            if ($list){
                $list .= ', ' . self::CRLF . "\t";
            }
            $list .= $this->formatAddress($address['email'], $address['name']);
        }
        return $list;
    }

    /**
     * Format the email address by the email and name provided.
     * @param string $email the email address
     * @param string $name the optional name
     * @return string the formatted email address
     */
    protected function formatAddress($email, $name = '')
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

        if($name){
            return sprintf('%s <%s>', $name, $email);
        }

        return $email;
    }

}