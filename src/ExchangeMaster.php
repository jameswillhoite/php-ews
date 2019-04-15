<?php

    defined('BASE') || define('BASE', __DIR__);
    use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
    use jamesiarmes\PhpEws\Client;
    use jamesiarmes\PhpEws\SoapClient;
    use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
    use jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
    use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
    use jamesiarmes\PhpEws\Request\CreateItemType;
    use jamesiarmes\PhpEws\Type\BodyType;
    use jamesiarmes\PhpEws\Type\EmailAddressType;
    use jamesiarmes\PhpEws\Type\MessageType;
    use jamesiarmes\PhpEws\Type\SingleRecipientType;
    
 spl_autoload_register(function ($class) {
     $exp = explode("\\", $class);
     $class = end($exp);
     if(file_exists(BASE ."/" . $class . ".php")) {
         require_once BASE . "/" . $class . ".php";
     }
     elseif (file_exists(BASE . "/ArrayType/" . $class . ".php")) {
         require_once BASE . "/ArrayType/" . $class . ".php";
     }
     elseif (file_exists(BASE . "/Enumeration/" . $class . ".php")) {
         require_once BASE . "/Enumeration/" . $class . ".php";
     }
     elseif (file_exists(BASE . "/Request/" . $class . ".php")) {
         require_once BASE . "/Request/" . $class . ".php";
     }
     elseif (file_exists(BASE . "/Response/" . $class . ".php")) {
         require_once BASE . "/Response/" . $class . ".php";
     }
     elseif (file_exists(BASE . "/Type/" . $class . ".php")) {
         require_once BASE . "/Type/" . $class . ".php";
     }
     else {
         return null;
     }
 });
 

     class ExchangeMaster {
         private $mailServer = "change me";
         private $fromUsername = "change me";
         private $fromPassword = "change me";
         protected $temp_dir = 'path/to/temp/directory/for/attachments';
         protected $theClient = null;



         public function __construct($username = null, $password = null)
         {
            if($username != null) {
                $this->fromUsername = $username;
                $this->fromPassword = $password;
            }
            $this->theClient = new Client($this->mailServer, $this->fromUsername, $this->fromPassword, Client::VERSION_2007);
            $this->theClient->setTimezone('Eastern Standard Time');
            $this->theClient->setCurlOptions(array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false));
         }

        public function sendEmail($to, $nameTo, $subject, $body) {
             //Connect
             $client = $this->theClient;
             if(!$client)
                 die("No client");

             //Build the request
             $request = new CreateItemType();
             $request->Items = new NonEmptyArrayOfAllItemsType();

             //Save the Message to Draft
            $request->MessageDisposition = MessageDispositionType::SAVE_ONLY;

            // Create the message.
            $message = new MessageType();
            $message->Subject = $subject;
            $message->ToRecipients = new ArrayOfRecipientsType();

            // Set the sender.
            $message->From = new SingleRecipientType();
            $message->From->Mailbox = new EmailAddressType();
            $message->From->Mailbox->EmailAddress = $this->fromUsername;

            // Set the recipient.
            $recipient = new EmailAddressType();
            $recipient->Name = $nameTo;
            $recipient->EmailAddress = $to;
            $message->ToRecipients->Mailbox[] = $recipient;

            // Set the message body.
            $message->Body = new BodyType();
            $message->Body->BodyType = BodyTypeType::HTML;
            $message->Body->_ = $body;

            // Add the message to the request.
            $request->Items->Message[] = $message;

            $response = $client->CreateItem($request);

            // Iterate over the results, printing any error messages or message ids.
            $response_messages = $response->ResponseMessages->CreateItemResponseMessage;
            foreach ($response_messages as $response_message) {
                // Make sure the request succeeded.
                if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                    $code = $response_message->ResponseCode;
                    $message = $response_message->MessageText;
                    echo "Message failed to create with \"$code: $message\"\n";
                    continue;
                }

                // Iterate over the created messages, printing the id for each.
                foreach ($response_message->Items->Message as $item) {
                    $output = '- Id: ' . $item->ItemId->Id . "\n";
                    $output .= '- Change key: ' . $item->ItemId->ChangeKey . "\n";
                    echo "Message created successfully.\n$output";
                }
            }
        }
     }
