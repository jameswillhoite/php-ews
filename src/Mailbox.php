<?php
    
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfRequestAttachmentIdsType;
    use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
    use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
    use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
    use jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
    use jamesiarmes\PhpEws\Request\FindItemType;
    use jamesiarmes\PhpEws\Request\GetAttachmentType;
    use jamesiarmes\PhpEws\Request\GetItemType;
    use jamesiarmes\PhpEws\Type\AndType;
    use jamesiarmes\PhpEws\Type\ConstantValueType;
    use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
    use jamesiarmes\PhpEws\Type\FieldURIOrConstantType;
    use jamesiarmes\PhpEws\Type\IsGreaterThanOrEqualToType;
    use jamesiarmes\PhpEws\Type\IsLessThanOrEqualToType;
    use jamesiarmes\PhpEws\Type\ItemIdType;
    use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
    use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
    use jamesiarmes\PhpEws\Type\RequestAttachmentIdType;
    use jamesiarmes\PhpEws\Type\RestrictionType;
    
    if(!class_exists("ExchangeMaster")) {
        require_once "ExchangeMaster.php";
    }
    
    class Mailbox extends ExchangeMaster
    {
        /**
         * @var DateTime $start_date
         */
        private $start_date = null;
    
        /**
         * @var DateTime $stop_date
         */
        private $stop_date = null;
        
        public $messages = array();
        
        public function __construct($username = null, $password = null)
        {
            parent::__construct($username, $password);
            $this->start_date = new DateTime(date("m/d/Y 00:00:00"));
            $this->stop_date = new DateTime(date("m/d/Y 23:59:59"));
        }
    
        public function changeDateRange(DateTime $start_date, DateTime $stop_date): void
        {
            $this->start_date = $start_date;
            $this->stop_date = $stop_date;
        }
        
        public function getMailbox() {
            $request = new FindItemType();
            $request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
            $request->Traversal = \jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType::SHALLOW;

// Build the start date restriction.
            $greater_than = new IsGreaterThanOrEqualToType();
            $greater_than->FieldURI = new PathToUnindexedFieldType();
            $greater_than->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
            $greater_than->FieldURIOrConstant = new FieldURIOrConstantType();
            $greater_than->FieldURIOrConstant->Constant = new ConstantValueType();
            $greater_than->FieldURIOrConstant->Constant->Value = $this->start_date->format('c');

// Build the end date restriction;
            $less_than = new IsLessThanOrEqualToType();
            $less_than->FieldURI = new PathToUnindexedFieldType();
            $less_than->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
            $less_than->FieldURIOrConstant = new FieldURIOrConstantType();
            $less_than->FieldURIOrConstant->Constant = new ConstantValueType();
            $less_than->FieldURIOrConstant->Constant->Value = $this->stop_date->format('c');

// Build the restriction.
            $request->Restriction = new RestrictionType();
            $request->Restriction->And = new AndType();
            $request->Restriction->And->IsGreaterThanOrEqualTo = $greater_than;
            $request->Restriction->And->IsLessThanOrEqualTo = $less_than;

// Return all message properties.
            $request->ItemShape = new ItemResponseShapeType();
            $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

// Search in the user's inbox.
            $folder_id = new DistinguishedFolderIdType();
            $folder_id->Id = DistinguishedFolderIdNameType::INBOX;
            $request->ParentFolderIds->DistinguishedFolderId[] = $folder_id;
    
            $response = $this->theClient->FindItem($request);

// Iterate over the results, printing any error messages or message subjects.
            $response_messages = $response->ResponseMessages->FindItemResponseMessage;
            foreach ($response_messages as $response_message) {
                // Make sure the request succeeded.
                if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                    $code = $response_message->ResponseCode;
                    $message = $response_message->MessageText;
                    echo "Failed to search for messages with \"$code: $message\"\n";
                    continue;
                }
        
                // Iterate over the messages that were found, printing the subject for each.
                $items = $response_message->RootFolder->Items->Message;
                foreach ($items as $item) {
                    $Message = new ExchangeMessage();
                    $this->messages[] = $Message;
                    $subject = $item->Subject;
                    $id = $item->ItemId->Id;
                    $new = $item->IsRead;
                   
                    
                    /**
                     * @var \jamesiarmes\PhpEws\Type\EmailAddressType $from
                     *
                     */
                    $from = $item->From->Mailbox;
                    $attach = $item->HasAttachments;
                    
                    //$Message->setTo($to);
                    $Message->setFrom($from);
                    $Message->setSubject($subject);
                    $Message->setId($id);
                    $Message->setIsRead($new);
                    $this->__getMessageBody($Message);
                    echo "ID: $id<br/>Has Attachements: $attach<br/>From: " . $from->EmailAddress . "<br/>Subject: $subject:<br/>Read: $new<br/>Body: " . $Message->getBody() . "<br/>";
                }
            }
    
        }
        
        private function __getMessageBody(ExchangeMessage $message) {
            $id = $message->getId(); // Message ID
    
            $request = new GetItemType();
            $request->ItemShape = new ItemResponseShapeType();
            $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
            $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
    
            $item = new ItemIdType();
            $item->Id = $id;
            $request->ItemIds->ItemId[] = $item;
    
            $response = $this->theClient->GetItem($request);
            $response_messages = $response->ResponseMessages->GetItemResponseMessage;
            $body = $response_messages[0]->Items->Message[0]->Body->_;
            $message->setBody($body);
    
            // Iterate over the messages, getting the attachments for each.
            $attachments = $message->attachment_ids;
            foreach ($response_messages[0]->Items->Message as $item) {
                // If there are no attachments for the item, move on to the next
                // message.
                if (empty($item->Attachments)) {
                    continue;
                }
        
                // Iterate over the attachments for the message.
                foreach ($item->Attachments->FileAttachment as $attachment) {
                    $attachments[] = $attachment->AttachmentId->Id;
                }
            }
            
        }
        
        public function getAttachments(ExchangeMessage $message) {
            // Build the request to get the attachments.
            $request = new GetAttachmentType();
            $request->AttachmentIds = new NonEmptyArrayOfRequestAttachmentIdsType();
    
            // Iterate over the attachments for the message.
            foreach ($message->attachment_ids as $attachment_id) {
                $id = new RequestAttachmentIdType();
                $id->Id = $attachment_id;
                $request->AttachmentIds->AttachmentId[] = $id;
            }
    
            $response = $this->theClient->GetAttachment($request);
    
            // Iterate over the response messages, printing any error messages or
            // saving the attachments.
            $attachment_response_messages = $response->ResponseMessages
                ->GetAttachmentResponseMessage;
            foreach ($attachment_response_messages as $attachment_response_message) {
                // Make sure the request succeeded.
                if ($attachment_response_message->ResponseClass
                    != ResponseClassType::SUCCESS) {
                    echo "<br/><br/>COULD NOT GET ATTACHMENT<br/><br/>";
                    continue;
                }
        
                // Iterate over the file attachments, saving each one.
                $attachments = $attachment_response_message->Attachments
                    ->FileAttachment;
                foreach ($attachments as $attachment) {
                    $path = $this->temp_dir . "/" . time() . "_" . $attachment->Name;
                    file_put_contents($path, $attachment->Content);
                    ECHO "ATTACHEMENT SAVED AT $path<br/>";
                    $message->attachments[] = array("path" => $path, "name" => $attachment->Name, "mime" => $attachment->ContentType, "size" => $attachment->Size);
                }
            }
        }
        
        
    }