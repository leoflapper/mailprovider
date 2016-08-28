<?php

    require '../vendor/autoload.php';
    
    //$service = new MailProvider\Service\SendGrid('API-KEY');
    //$service = new MailProvider\Service\Mailgun('API-KEY');
    $service = new MailProvider\Service\Mandrill('API-KEY');
    $service
        ->addTo('info@myemail.nl', 'Leo Flapper')
        ->addCc('cc@myemail.nl', 'Leo Flapper')
        ->addBcc('bcc@myemail.nl', 'Leo Flapper')
        ->setFrom('info@myhost.nl', 'Leo Flapper')
        ->setSubject('My Subject')
        ->setText('My text')
        ->setHtml('<p>Beautiful content</p>')
        ->addAttachment('../LICENSE.md', 'Attachment.txt')
        ->addHeader('MyHeader', 'Value')
        ->setReplyTo('reply@myemail.nl');

    $response = $service->send();
    echo '<pre>'; print_r($response); echo '</pre>'; die();
    
?>