# MailProvider #

MailProvider allows you to quickly send mails with different mail services like Mandrill, SendGrind and Mailgun. 

## Usage ##
The MailProvider allows you to compose an email in a single format. You only have to choose the desired service.

```php
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
        ->addAttachment('/myattachment.txt', 'Attachment.txt')
        ->addHeader('MyHeader', 'Value')
        ->setReplyTo('reply@myemail.nl')
    ;
    $service->send();
```

## Installation ##
MailProvider uses a `composer.json` file to require it's dependencies. Run `composer install` to require those dependencies.

## Example ##
The repository is provided with an example PHP file (`example.php`) in the root of this repository. 