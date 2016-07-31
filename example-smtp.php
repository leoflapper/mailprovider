<?php

    require 'vendor/autoload.php';
    
    $service = new MailProvider\Service\SMTP('server', 25);
    $service
        ->setProtocol('ssl')
        ->setLogin('username', 'password')
        ->addTo('info@myemail.nl', 'Leo Flapper')
        ->addCc('cc@myemail.nl', 'Leo Flapper')
        ->addBcc('bcc@myemail.nl', 'Leo Flapper')
        ->setFrom('info@myhost.nl', 'Leo Flapper')
        ->setSubject('My Subject')
        ->setHtml('<p>Beautiful content</p>')
        ->addAttachment('/myattachment.txt', 'Attachment.txt')
        ->addHeader('MyHeader', 'Value')
        ->setReplyTo('reply@myemail.nl');

    $response = $service->send();
    echo '<pre>'; print_r($response); echo '</pre>'; 

?>