<?php

    require 'vendor/autoload.php';
    
    //$service = new MailProvider\Service\SendGrid('API-KEY');
    //$service = new MailProvider\Service\Mailgun('API-KEY');
    $service = new MailProvider\Service\Mandrill('API-KEY');
    $service
        ->addTo('info@myemail.nl', 'Leo Flapper')
        ->addCc('cc@myemail.nl', 'Leo Flapper')
        ->addBcc('bcc@myemail.nl', 'Leo Flapper')
        ->setFrom('info@myhost.nl')
        ->setFromName('Leo Flapper')
        ->setSubject('My Subject')
        ->setHtml('<p>Beautiful content</p>')
        ->addAttachment('/myattachment.txt', 'Attachment.txt')
    ;

    echo "<pre>"; print_r($service->send()); echo "</pre>";

?>