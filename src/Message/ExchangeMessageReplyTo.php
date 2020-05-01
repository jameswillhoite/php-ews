<?php


    namespace jamesiarmes\PhpEws\Message;
    
    
    use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
    use jamesiarmes\PhpEws\Type\BodyType;
    use jamesiarmes\PhpEws\Type\ItemIdType;
    use jamesiarmes\PhpEws\Type\MessageType;
    use jamesiarmes\PhpEws\Type\ReplyToItemType;

    class ExchangeMessageReplyTo extends ReplyToItemType implements BasicMessage
    {
		use AddAttachment;
		use AddReceiver;
		use BasicTraits;

	    /**
	     * ExchangeMessageReplyTo constructor.
	     *
	     * @param   string|MessageType  $id
	     * @param   string              $change_key
	     */
        public function __construct($id = "", string $change_key = "")
        {

        	$this->NewBodyContent = new BodyType();
        	$this->NewBodyContent->BodyType = BodyTypeType::HTML;

	        $this->ReferenceItemId = new ItemIdType();

            if(is_string($id) && strlen($id) > 0)
            {
	            $this->ReferenceItemId->Id = $id;
	            if(!empty($change_key))
	            	$this->ReferenceItemId->ChangeKey = $change_key;
            }
            elseif (is_object($id) && isset($id->ItemId) && !empty($id->ItemId->Id))
	            $this->ReferenceItemId = $id->ItemId;

        }

	    public function setItemId(string $id)
	    {
		    $this->ReferenceItemId->Id = $id;
	    }

	    public function setChangeKey(string $change_key)
	    {
		    $this->ReferenceItemId->ChangeKey = $change_key;
	    }

	    public function isHtml(bool $is_html = true): void
	    {
		    if($is_html)
		    {
		    	$this->NewBodyContent->BodyType = BodyTypeType::HTML;
		    }
		    else
		    {
		    	$this->NewBodyContent->BodyType = BodyTypeType::TEXT;
		    }
	    }

	    public function setImportance(string $importance): void
	    {
		    //There is no Importance for Reply
	    }

	    public function setSubject(string $subject): void
	    {
		    $this->Subject = $subject;
	    }

	    public function setBody(string $body): void
	    {
		    $this->NewBodyContent->_ = $body;
	    }

	    public function getBody(): string
	    {
		    return $this->NewBodyContent->_;
	    }
    }