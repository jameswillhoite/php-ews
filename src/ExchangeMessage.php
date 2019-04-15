<?php
   
    
    class ExchangeMessage
    {
       public $id = null;
       
       public $subject = "";
       
       public $body = "";
    
        /**
         * @var \jamesiarmes\PhpEws\Type\EmailAddressType
         * @since version
         */
       public $to = "";
    
        /**
         * @var \jamesiarmes\PhpEws\Type\EmailAddressType
         * @since version
         */
       public $from = "";
       
       public $cc = "";
       
       public $bcc = "";
       
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
         * @return \jamesiarmes\PhpEws\Type\EmailAddressType
         */
        public function getTo(): \jamesiarmes\PhpEws\Type\EmailAddressType
        {
            return $this->to;
        }
    
        /**
         * @param \jamesiarmes\PhpEws\Type\EmailAddressType $to
         */
        public function setTo(\jamesiarmes\PhpEws\Type\EmailAddressType $to): void
        {
            $this->to = $to;
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
         * @return string
         */
        public function getCc(): string
        {
            return $this->cc;
        }
    
        /**
         * @param string $cc
         */
        public function setCc(?string $cc): void
        {
            $this->cc = $cc;
        }
    
        /**
         * @return string
         */
        public function getBcc(): string
        {
            return $this->bcc;
        }
    
        /**
         * @param string $bcc
         */
        public function setBcc(?string $bcc): void
        {
            $this->bcc = $bcc;
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
       
       
    }