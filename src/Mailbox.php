<?php
    
    use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
    use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfRequestAttachmentIdsType;
    use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
    use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
    use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
    use jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
    use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
    use jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
    use jamesiarmes\PhpEws\Request\CreateAttachmentType;
    use jamesiarmes\PhpEws\Request\CreateItemType;
    use jamesiarmes\PhpEws\Request\FindItemType;
    use jamesiarmes\PhpEws\Request\GetAttachmentType;
    use jamesiarmes\PhpEws\Request\GetItemType;
    use jamesiarmes\PhpEws\Request\SendItemType;
    use jamesiarmes\PhpEws\Type\AndType;
    use jamesiarmes\PhpEws\Type\BodyType;
    use jamesiarmes\PhpEws\Type\ConstantValueType;
    use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
    use jamesiarmes\PhpEws\Type\EmailAddressType;
    use jamesiarmes\PhpEws\Type\FieldURIOrConstantType;
    use jamesiarmes\PhpEws\Type\IsEqualToType;
    use jamesiarmes\PhpEws\Type\IsGreaterThanOrEqualToType;
    use jamesiarmes\PhpEws\Type\IsLessThanOrEqualToType;
    use jamesiarmes\PhpEws\Type\ItemIdType;
    use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
    use jamesiarmes\PhpEws\Type\MessageType;
    use jamesiarmes\PhpEws\Type\PathToExtendedFieldType;
    use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
    use jamesiarmes\PhpEws\Type\RequestAttachmentIdType;
    use jamesiarmes\PhpEws\Type\RestrictionType;
    use jamesiarmes\PhpEws\Type\SetItemFieldType;
    use jamesiarmes\PhpEws\Type\SingleRecipientType;
    use jamesiarmes\PhpEws\Type\TargetFolderIdType;
    
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
    
        /**
         * @var bool
         */
        private $show_unread_only = false;
        
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
        
        public function showUnreadOnly(bool $boolean = false) {
            $this->show_unread_only = $boolean;
        }
        
        public function limitToEmailAddress() {
        
        }
    
        /**
         * @throws Exception
         */
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

//Show only unread emails
            if($this->show_unread_only) {
                $unread = new IsEqualToType();
                $unread->FieldURI = new PathToExtendedFieldType();
                $unread->FieldURI->FieldURI = "message:IsRead";
                $unread->FieldURIOrConstant = new FieldURIOrConstantType();
                $unread->FieldURIOrConstant->Constant = new ConstantValueType();
                $unread->FieldURIOrConstant->Constant->Value = 0;
                
                $request->Restriction->And->IsEqualTo = $unread;
            }

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
                    $subject = $item->Subject;
                    $id = $item->ItemId->Id;
                    $new = $item->IsRead;
    
                    $Message = new ExchangeMessage();
                    $this->messages[$id] = $Message;
                   
                    
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
                    $Message->setChangeKey($item->ItemId->ChangeKey);
                    $Message->setIsRead($new);
                    $this->__getMessageBody($Message);
                    echo "ID: $id<br/>Has Attachements: $attach<br/>To: " . $Message->getTo()[0]->EmailAddress . "<br/>From: " . $Message->getFrom()->EmailAddress . "<br/>Subject: $subject:<br/>Read: $new<br/>Body: " . $Message->getBody() . "<br/>";
                }
            }
    
        }
    
        /**
         * @param ExchangeMessage $message

         * @throws Exception
         */
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
            $message->setFrom($response_messages[0]->Items->Message[0]->From->Mailbox);
            $message->setTo($response_messages[0]->Items->Message[0]->ToRecipients->Mailbox);

    
            // Iterate over the messages, getting the attachments for each.
            $attachments = array();
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
    
            $message->attachment_ids = $attachments;
        }
        
        public function getAttachments(ExchangeMessage $message) {
            if(count($message->attachment_ids) == 0) {
                return;
            }
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
                    ECHO "ATTACHMENT SAVED AT $path<br/>";
                    $message->attachments[] = array("path" => $path, "name" => $attachment->Name, "mime" => $attachment->ContentType, "size" => $attachment->Size);
                }
            }
        }
        
        public function markAsRead(ExchangeMessage $message) {
            //Check to see if the message is already marked as read
            if($message->isRead())
                return;
            
            $request = new \jamesiarmes\PhpEws\Request\UpdateItemType();
            $request->MessageDisposition = 'SaveOnly';
            $request->ConflictResolution = 'AlwaysOverwrite';
            $request->ItemChanges = array();
            
            $change = new \jamesiarmes\PhpEws\Type\ItemChangeType();
            $change->ItemId = new ItemIdType();
            $change->ItemId->Id = $message->getId();
            $change->ItemId->ChangeKey = $message->getChangeKey();
            $change->Updates = new \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfItemChangeDescriptionsType();
            $change->Updates->SetItemField = array();
            
            $field = new SetItemFieldType();
            $field->FieldURI = new PathToExtendedFieldType();
            $field->FieldURI->FieldURI = "message:IsRead";
            $field->Message = new \jamesiarmes\PhpEws\Type\MessageType();
            $field->Message->IsRead = true;
            
            $change->Updates->SetItemField[] = $field;
            
            $request->ItemChanges[] = $change;
            
            $response = $this->theClient->UpdateItem($request);
            
            $message->setIsRead(true);
        }
    
        /**
         * @param ExchangeMessage $message
         *
         * @throws Exception
         */
        public function deleteEmail(ExchangeMessage $message) {
            $request = new \jamesiarmes\PhpEws\Request\DeleteItemType();
            $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
            $request->ItemIds->ItemId[] = new ItemIdType();
            $request->ItemIds->ItemId[0]->Id = $message->getId();
            $request->ItemIds->ItemId[0]->ChangeKey = $message->getChangeKey();
            
            $request->DeleteType = \jamesiarmes\PhpEws\Enumeration\DisposalType::MOVE_TO_DELETED_ITEMS;
            
            $response = $this->theClient->DeleteItem($request);
            if($response->ResponseMessages->DeleteItemResponseMessage[0]->ResponseClass == "Success") {
                unset($this->messages[$message->getId()]);
            }
            else {
                throw new Exception("Unable to delete Email");
            }
        }
        
        public function createNewMessage(): ExchangeMessage
        {
            return new ExchangeMessage();
        }
    
        /**
         * @param ExchangeMessage $Message
         *
         * @throws Exception
         */
        public function sendMessage(ExchangeMessage $Message)
        {
            //Build the request
            $request = new CreateItemType();
            $request->Items = new NonEmptyArrayOfAllItemsType();
    
            //Save the Message to Draft
            $request->MessageDisposition = MessageDispositionType::SAVE_ONLY;
    
            // Create the message.
            $message = new MessageType();
            $message->Subject = $Message->getSubject();
            $message->ToRecipients = new ArrayOfRecipientsType();
            $message->CcRecipients = new ArrayOfRecipientsType();
            $message->BccRecipients = new ArrayOfRecipientsType();
    
            // Set the sender.
            $message->From = new SingleRecipientType();
            $message->From->Mailbox = new EmailAddressType();
            $message->From->Mailbox->EmailAddress = $this->fromUsername;
    
            // Set the recipient.
            $message->ToRecipients->Mailbox = $Message->getTo();
            
            //Set the Cc recipient
            $message->CcRecipients->Mailbox = $Message->getCc();
            
            //Set the Bcc Recipient
            $message->BccRecipients->Mailbox = $Message->getBcc();
            
            // Set the message body.
            $message->Body = new BodyType();
            $message->Body->BodyType = BodyTypeType::HTML;
            $message->Body->_ = $Message->getBody();
    
            // Add the message to the request.
            $request->Items->Message[] = $message;
    
            $response = $this->theClient->CreateItem($request);
            
            //Get the ID and Change Key
            $create_response = $response->ResponseMessages->CreateItemResponseMessage[0];
            $Message->setId($create_response->Items->Message[0]->ItemId->Id);
            $Message->setChangeKey($create_response->Items->Message[0]->ItemId->ChangeKey);
            
            if(!$create_response->ResponseClass == "Success")
                throw new Exception("Could not create the message to send");
            
            $response = null;
            
            //Add any attachments
            if(count($Message->attachments) > 0) {
                // Build the request,
                $request = new CreateAttachmentType();
                $request->ParentItemId = new ItemIdType();
                $request->ParentItemId->Id = $Message->getId();
                $request->Attachments = new NonEmptyArrayOfAttachmentsType();
                foreach ($Message->attachments as $a) {
                    $request->Attachments->FileAttachment[] = $a;
                }
    
                $response = $this->theClient->CreateAttachment($request);
                
                if(!$response->ResponseMessages->CreateAttachmentResponseMessage[0]->ResponseClass == "Success")
                    throw new Exception("Could not add Attachment(s)");
                
                $Message->setChangeKey($response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey);

                $response = null;
            }
    
            // Build the request.
            $request = new SendItemType();
            $request->SaveItemToFolder = true;
            $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();

// Add the message to the request.
            $item = new ItemIdType();
            $item->Id = $Message->getId();
            $item->ChangeKey = $Message->getChangeKey();
            $request->ItemIds->ItemId[] = $item;

// Configure the folder to save the sent message to.
            $send_folder = new TargetFolderIdType();
            $send_folder->DistinguishedFolderId = new DistinguishedFolderIdType();
            $send_folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::SENT;
            $request->SavedItemFolderId = $send_folder;

            $response = $this->theClient->SendItem($request);

            if(!$response->ResponseMessages->SendItemResponseMessage[0]->ResponseClass == "Success")
                throw new Exception("Could not Send Email. A Draft was created in your Mailbox");

            $response = null;

        }
        
        
    }