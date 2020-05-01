<?php
    /**
     * @package     jamesiarmes\PhpEws\Message
     * @subpackage
     *
     * @copyright   A copyright
     * @license     A "Slug" license name e.g. GPL2
     */
    
    namespace jamesiarmes\PhpEws\Message;
    
    
    use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
    use jamesiarmes\PhpEws\Type\BodyType;
    use jamesiarmes\PhpEws\Type\ForwardItemType;
    use jamesiarmes\PhpEws\Type\ItemIdType;
    use jamesiarmes\PhpEws\Type\MessageType;

    class ExchangeMessageForward extends ForwardItemType implements BasicMessage
    {
    	use AddReceiver;
    	use AddAttachment;
    	use BasicTraits;

	    /**
	     * ExchangeMessageForward constructor.
	     *
	     * @param   string|MessageType  $id
	     * @param   string  $change_key
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
			{
				$this->setItemId($id->ItemId->Id);
				$this->setChangeKey($id->ItemId->ChangeKey);
			}

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
		    	$this->NewBodyContent->BodyType = BodyTypeType::HTML;
		    else
		    	$this->NewBodyContent->BodyType = BodyTypeType::TEXT;

	    }

	    /**
	     * @inheritDoc
	     */
	    public function setImportance(string $importance): void
	    {
		    //Doesn't work with this
	    }

	    /**
	     * This will overwrite the Subject
	     * @param   string  $subject
	     *
	     */
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