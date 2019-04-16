<?php
   
    
    class ExchangeMessage
    {
       public $id = null;
       
       public $change_key = null;
       
       public $subject = "";
       
       public $body = "";
    
        /**
         * @var \jamesiarmes\PhpEws\Type\EmailAddressType[]
         * @since version
         */
       public $to = array();
    
        /**
         * @var \jamesiarmes\PhpEws\Type\EmailAddressType
         * @since version
         */
       public $from = "";
       
       public $cc = array();
       
       public $bcc = array();
       
       public $priority = 0;
       
       public $attachment_ids = array();
       
       public $attachments = array();
       
       public $isRead = false;
    
        /**
         * @return null
         */
        public function getId()
        {
            return $this->id;
        }
    
        /**
         * @param null $id
         */
        public function setId($id): void
        {
            $this->id = $id;
        }
    
        /**
         * @return null
         */
        public function getChangeKey()
        {
            return $this->change_key;
        }
    
        /**
         * @param null $change_key
         */
        public function setChangeKey($change_key): void
        {
            $this->change_key = $change_key;
        }
        
        
    
        /**
         * @return string
         */
        public function getSubject(): string
        {
            return $this->subject;
        }
    
        /**
         * @param string $subject
         */
        public function setSubject(string $subject): void
        {
            $this->subject = $subject;
        }
    
        /**
         * @return \jamesiarmes\PhpEws\Type\EmailAddressType[]
         */
        public function getTo()
        {
            return $this->to;
        }
    
        /**
         * @param \jamesiarmes\PhpEws\Type\EmailAddressType[] $to
         *
         * @throws Exception
         */
        public function setTo($to): void
        {
            if(is_array($to)) {
                foreach ($to as $t) {
                    if ($t instanceof \jamesiarmes\PhpEws\Type\EmailAddressType) {
                        $this->to[] = $t;
                    } else {
                        throw new Exception("Could not set \"To\". Must be an array of EmailAddressType.");
                    }
                }
            }
            else {
                throw new Exception("setTo must be an array of EmailAddressType");
            }
        }
    
        /**
         * @param string|array $email
         * @param null|string  $name
         *
         * @throws Exception
         */
        public function addRecipient($email, $name = null) {
            if($email instanceof \jamesiarmes\PhpEws\Type\EmailAddressType)
                $this->to[] = $email;
            elseif(is_string($email)) {
                $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                $To->EmailAddress = $email;
                $To->Name = ($name) ? $name : "";
                $this->to[] = $To;
            }
            elseif (is_array($email)) {
                foreach ($email as $e) {
                    $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                    $To->EmailAddress = $e[0];
                    $To->Name = ($e[1]) ? $e[1] : "";
                    $this->to[] = $To;
                }
            }
            else {
                throw new Exception("AddBccRecipient must be instance of EmailAddressType, string emailAddress, or array of email address (array(email, name))");
            }
        }
    
        /**
         * @return \jamesiarmes\PhpEws\Type\EmailAddressType
         */
        public function getFrom(): \jamesiarmes\PhpEws\Type\EmailAddressType
        {
            return $this->from;
        }
    
        /**
         * @param \jamesiarmes\PhpEws\Type\EmailAddressType $from
         */
        public function setFrom(\jamesiarmes\PhpEws\Type\EmailAddressType $from): void
        {
            $this->from = $from;
        }
    
        /**
         * @return array
         */
        public function getCc(): array
        {
            return $this->cc;
        }
    
        /**
         * @param array $cc
         *
         * @throws Exception
         */
        public function setCc(array $cc): void
        {
            if(is_array($cc)) {
                foreach ($cc as $t) {
                    if ($t instanceof \jamesiarmes\PhpEws\Type\EmailAddressType) {
                        $this->cc[] = $t;
                    } else {
                        throw new Exception("Could not set \"CC\". Must be an array of EmailAddressType.");
                    }
                }
            }
            else {
                throw new Exception("setCc must be an array of EmailAddressType");
            }
        }
    
        /**
         * @param string|array|\jamesiarmes\PhpEws\Type\EmailAddressType $email
         * @param null|string                                            $name
         *
         * @throws Exception
         */
        public function addCcRecipient($email, $name = null) {
            if($email instanceof \jamesiarmes\PhpEws\Type\EmailAddressType)
                $this->cc[] = $email;
            elseif(is_string($email)) {
                $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                $To->EmailAddress = $email;
                $To->Name = ($name) ? $name : "";
                $this->cc[] = $To;
            }
            elseif (is_array($email)) {
                foreach ($email as $e) {
                    $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                    $To->EmailAddress = $e[0];
                    $To->Name = ($e[1]) ? $e[1] : "";
                    $this->cc[] = $To;
                }
            }
            else {
                throw new Exception("AddBccRecipient must be instance of EmailAddressType, string emailAddress, or array of email address (array(email, name))");
            }
        }
    
        /**
         * @return array
         */
        public function getBcc(): array
        {
            return $this->bcc;
        }
    
        /**
         * @param array $bcc
         *
         * @throws Exception
         */
        public function setBcc(array $bcc): void
        {
            if(is_array($bcc)) {
                foreach ($bcc as $t) {
                    if ($t instanceof \jamesiarmes\PhpEws\Type\EmailAddressType) {
                        $this->bcc[] = $t;
                    } else {
                        throw new Exception("Could not set \"BCC\". Must be an array of EmailAddressType.");
                    }
                }
            }
            else {
                throw new Exception("setBcc must be an array of EmailAddressType");
            }
        }
    
        /**
         * @param string|array|\jamesiarmes\PhpEws\Type\EmailAddressType $email
         * @param null|string                                            $name
         *
         * @throws Exception
         */
        public function addBccRecipient($email, $name = null) {
            if($email instanceof \jamesiarmes\PhpEws\Type\EmailAddressType)
                $this->bcc[] = $email;
            elseif(is_string($email)) {
                $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                $To->EmailAddress = $email;
                $To->Name = ($name) ? $name : "";
                $this->bcc[] = $To;
            }
            elseif (is_array($email)) {
                foreach ($email as $e) {
                    $To = new \jamesiarmes\PhpEws\Type\EmailAddressType();
                    $To->EmailAddress = $e[0];
                    $To->Name = ($e[1]) ? $e[1] : "";
                    $this->bcc[] = $To;
                }
            }
            else {
                throw new Exception("AddBccRecipient must be instance of EmailAddressType, string emailAddress, or array of email address (array(email, name))");
            }
        }
    
        /**
         * @return int
         */
        public function getPriority(): int
        {
            return $this->priority;
        }
    
        /**
         * @param int $priority
         */
        public function setPriority(int $priority): void
        {
            $this->priority = $priority;
        }
    
        /**
         * @return bool
         */
        public function isRead(): bool
        {
            return $this->isRead;
        }
    
        /**
         * @param bool $isRead
         */
        public function setIsRead(bool $isRead): void
        {
            $this->isRead = $isRead;
        }
    
        /**
         * @return string|null
         */
        public function getBody()
        {
            return $this->body;
        }
    
        /**
         * @param string $body
         */
        public function setBody(?string $body): void
        {
            $this->body = $body;
        }
    
        /**
         * @param string $file_path
         *
         *
         * @since version
         */
        public function addAttachment(string $file_path) {
           // Open file handlers.
           $file = new SplFileObject($file_path);
           $finfo = finfo_open();
        
           // Build the file attachment.
           $attachment = new \jamesiarmes\PhpEws\Type\FileAttachmentType();
           $attachment->Content = $file->openFile()->fread($file->getSize());
           $attachment->Name = $file->getBasename();
           $attachment->ContentType = finfo_file($finfo, $file_path);
           
           $this->attachments[] = $attachment;
           
        }
    
        /**
         * @param string $raw
         * @param string $file_name
         * @param string $mime
         *
         *
         * @since version
         * @throws Exception
         */
        public function addStringAttachment(string $raw, string $file_name, string $mime = '') {
            if(!$raw)
                return;
            if(!$file_name)
                throw new Exception("No File name given for this string attachment.");
            
            $attachment = new \jamesiarmes\PhpEws\Type\FileAttachmentType();
            $attachment->Content = $raw;
            $attachment->Name = $file_name;
            $attachment->ContentType = $mime;
            
            $this->attachments[] = $attachment;
        }
    }