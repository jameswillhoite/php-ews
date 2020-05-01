# Notice
This is just an extension of James Aries Php-Ews found [Here][4]. He did a really great job at putting all this together, I just wanted to provide other developers a easier time getting all this to work.

# PHP Exchange Web Services

The PHP Exchange Web Services library (php-ews) is intended to make
communication with Microsoft Exchange servers using Exchange Web Services
easier. It handles the NTLM authentication required to use the SOAP
services and provides an object-oriented interface to the complex types
required to form a request.


## Dependencies

* PHP 5.4 or greater
* cURL with NTLM support (7.30.0+ recommended)
* Exchange 2007 or later

**Note: Not all operations or request elements are supported on all versions of
Exchange.**

## Installation

Edit the ExchangeMaster class found in the main src folder. 
1) Add your Exchange Server host
2) Add the Username
3) Add the Password
4) Add the default temp folder to hold any attachments downloaded from the Exchange Server.
5) Add the default folder to hold Error Logs.

## Usage

The library can be used to make several different request types. In order to
make a request, you need to instantiate a new `\jamesiarmes\PhpEws\Mailbox\Mailbox`
object:

```php
use \jamesiarmes\PhpEws\Mailbox\Mailbox;

$ews = new Mailbox("username", "password");
```

The `Mailbox` class takes two parameters for its constructor:

* `$username`: The user to connect to the server with. This is usually the
  local portion of the users email address. Example: "user" if the email address
  is "user@example.com".
* `$password`: The user's plain-text password.

The `Mailbox` object is what talks with the Exchange Server. 
You can get all the messages from the mailbox with the method `getMailbox()`. 
This will return an array of `MessageType` objects. You can pass `true` to the `getMailbox` method to return the `MessageType` array with the Message Body and the Email Addresses of each Message.

To Send an Email, create a new `ExchangeMessage` object. (There is a method in the Mailbox called `createMessage` that will return a new `ExchangeMessage` object).
Pass that `ExchangeMessage` object to the Mailbox `sendMessage` method which will send the message to the Recipient.


## Examples

There are a number of examples included in the examples directory. These
examples are meant to be run from the command line. In each, you will need to
set the connection information variables to match those of your Exchange server.
For some of them, you will also need to set ids or additional data that will be
used in the request.

## Resources

* [php-ews Website][4]
* [Exchange 2007 Web Services Reference][5]
* [Exchange 2010 Web Services Reference][6]
* [Exchange 2013 Web Services Reference][7]

## Support

All questions should use the [issue queue][8]. This allows the community to
contribute to and benefit from questions or issues you may have. Any support
requests received via email will be directed here.

[4]: http://www.jamesarmes.com/php-ews/
[5]: http://msdn.microsoft.com/library/bb204119\(v=EXCHG.80\).aspx
[6]: http://msdn.microsoft.com/library/bb204119\(v=exchg.140\).aspx
[7]: http://msdn.microsoft.com/library/bb204119\(v=exchg.150\).aspx
[8]: https://github.com/jameswillhoite/php-ews/issues
