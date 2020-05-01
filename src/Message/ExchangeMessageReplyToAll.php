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
    use jamesiarmes\PhpEws\Type\ItemIdType;
    use jamesiarmes\PhpEws\Type\ReplyAllToItemType;

    class ExchangeMessageReplyToAll extends ReplyAllToItemType implements BasicMessage
    {
    	use AddAttachment;
    	use AddReceiver;
    	use BasicTraits;


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
	        elseif (is_object($id) && isset($id->ItemId) && !empty($id->ItemId->id))
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
		    	$this->NewBodyContent->BodyType = BodyTypeType::HTML;
		    else
		    	$this->NewBodyContent->BodyType = BodyTypeType::TEXT;
	    }

	    /**
	     * @inheritDoc
	     */
	    public function setImportance(string $importance): void
	    {
		    // There isn't an Importance Setting
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