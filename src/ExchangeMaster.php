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
     }
