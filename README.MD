# MailProvider #
MailProvider allows you to quickly send mails with PHPMailer or different mail services like Mandrill, SendGrind or Mailgun. 

## Usage ##
The MailProvider allows you to compose an email in a single format. You only have to choose the desired service.

### PHPMailer ###
```php
    $service = new MailProvider\Service\PHPMailer();
    $service
        ->setProtocol('smtp')
        ->setHost('localhost')
        ->setPort(1025)
        ->addTo('info@myemail.nl', 'Leo Flapper')
        ->addCc('cc@myemail.nl', 'Leo Flapper')
        ->addBcc('bcc@myemail.nl', 'Leo Flapper')
        ->setFrom('info@myhost.nl', 'Leo Flapper')
        ->setSubject('My Subject')
        ->setHtml('<p>Beautiful content</p>')
        ->addAttachment('../LICENSE.md', 'Attachment.txt')
        ->addHeader('MyHeader', 'Value')
        ->setReplyTo('reply@myemail.nl');

    $service->send();
```

### Other mail services ###
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
        ->addAttachment('../LICENSE.md', 'Attachment.txt')
        ->addHeader('MyHeader', 'Value')
        ->setReplyTo('reply@myemail.nl');
    $service->send();
```

## Installation ##
Add MailProvider to your `composer.json` file. If you are not using [Composer](http://getcomposer.org), you should be. It's an excellent way to manage dependencies in your PHP application. 

```json
{  
  "require": {
    "leoflapper/mailprovider": "dev-master"
  }
}
```

Then at the top of your PHP script require the autoloader:

```bash
require 'vendor/autoload.php';
```

## Example ##
There are different examples located inside the examples directory.

## Credits

- [Leo Flapper](https://github.com/leoflapper)
- [Nathan Jansen](https://github.com/nathanjansen)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
