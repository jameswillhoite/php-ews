<?php

namespace jamesiarmes\PhpEws\Mailbox;

use DateTime;
use Exception;
use finfo;
use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfItemChangeDescriptionsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfRequestAttachmentIdsType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemCreateOrDeleteOperationType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemUpdateOperationType;
use jamesiarmes\PhpEws\Enumeration\ConflictResolutionType;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Enumeration\DisposalType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
use jamesiarmes\PhpEws\Events\Event;
use jamesiarmes\PhpEws\Events\EventException\EventException;
use jamesiarmes\PhpEws\Events\UpdateEvent;
use jamesiarmes\PhpEws\ExchangeMaster;
use jamesiarmes\PhpEws\Message\ExchangeMessage;
use jamesiarmes\PhpEws\Message\ExchangeMessageForward;
use jamesiarmes\PhpEws\Message\ExchangeMessageReplyTo;
use jamesiarmes\PhpEws\Message\ExchangeMessageReplyToAll;
use jamesiarmes\PhpEws\Request\CreateAttachmentType;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Request\DeleteAttachmentType;
use jamesiarmes\PhpEws\Request\DeleteItemType;
use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Request\GetAttachmentType;
use jamesiarmes\PhpEws\Request\GetItemType;
use jamesiarmes\PhpEws\Request\SendItemType;
use jamesiarmes\PhpEws\Request\UpdateItemType;
use jamesiarmes\PhpEws\Response\SendItemResponseType;
use jamesiarmes\PhpEws\Response\UpdateItemResponseMessageType;
use jamesiarmes\PhpEws\Type\AndType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\CalendarViewType;
use jamesiarmes\PhpEws\Type\CancelCalendarItemType;
use jamesiarmes\PhpEws\Type\ConstantValueType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\FieldURIOrConstantType;
use jamesiarmes\PhpEws\Type\FolderIdType;
use jamesiarmes\PhpEws\Type\ForwardItemType;
use jamesiarmes\PhpEws\Type\IsEqualToType;
use jamesiarmes\PhpEws\Type\IsGreaterThanOrEqualToType;
use jamesiarmes\PhpEws\Type\IsLessThanOrEqualToType;
use jamesiarmes\PhpEws\Type\ItemChangeType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Type\MeetingMessageType;
use jamesiarmes\PhpEws\Type\MessageType;
use jamesiarmes\PhpEws\Type\PathToExtendedFieldType;
use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use jamesiarmes\PhpEws\Type\ReplyAllToItemType;
use jamesiarmes\PhpEws\Type\ReplyToItemType;
use jamesiarmes\PhpEws\Type\RequestAttachmentIdType;
use jamesiarmes\PhpEws\Type\RestrictionType;
use jamesiarmes\PhpEws\Type\SetItemFieldType;
use jamesiarmes\PhpEws\Type\SingleRecipientType;
use jamesiarmes\PhpEws\Type\TargetFolderIdType;
use SplFileObject;

if (!class_exists("jamesiarmes\\Php-Ews\\ExchangeMaster"))
{
	require_once __DIR__ . "/../ExchangeMaster.php";
}

class Mailbox extends ExchangeMaster
{
	public $test_in_progress = false;

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

	/**
	 * @var null|string This is the email address to search for
	 */
	private $limit_to_email_address = null;

	/**
	 * Just a property to keep track of all the Messages Sent
	 * @var int
	 */
	public $Total_Messages_Sent = 0;

	/**
	 * Property that will hold all Failed Messages
	 * @var array
	 */
	public $Failed_Messages = array();

	/**
	 * Mailbox constructor.
	 *
	 * @param   string  $username
	 * @param   string  $password
	 *
	 * @throws Exception
	 */
	public function __construct(string $username = "", string $password = "")
	{
		parent::__construct($username, $password);
		$this->start_date = new DateTime(date("m/d/Y 00:00:00"));
		$this->stop_date  = new DateTime(date("m/d/Y 23:59:59"));

		$this->logger->setLogLevel("ERROR");
	}

	/**
	 * @param   DateTime  $start_date
	 * @param   DateTime  $stop_date
	 */
	public function changeDateRange(DateTime $start_date, DateTime $stop_date): void
	{
		$this->start_date = $start_date;
		$this->stop_date  = $stop_date;
	}

	/**
	 * @param   bool  $boolean
	 */
	public function showUnreadOnly(bool $boolean = false)
	{
		$this->show_unread_only = $boolean;
	}

	/**
	 * @param   string  $email_address
	 */
	public function showOnlyFrom(string $email_address)
	{
		$this->limit_to_email_address = $email_address;
	}

	/**
	 * @param   bool  $deep  Set to true to get the Message Body and all other elements
	 *                       Please note, To get Attachments, you must send the message through
	 *                       the GetAttachments Method
	 *
	 * @return MessageType[]
	 * @throws Exception
	 */
	public function getMailbox(bool $deep = false)
	{
		$request                  = new FindItemType();
		$request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
		$request->Traversal       = ItemQueryTraversalType::SHALLOW;

// Build the start date restriction.
		$greater_than                                      = new IsGreaterThanOrEqualToType();
		$greater_than->FieldURI                            = new PathToUnindexedFieldType();
		$greater_than->FieldURI->FieldURI                  = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
		$greater_than->FieldURIOrConstant                  = new FieldURIOrConstantType();
		$greater_than->FieldURIOrConstant->Constant        = new ConstantValueType();
		$greater_than->FieldURIOrConstant->Constant->Value = $this->start_date->format('c');

// Build the end date restriction;
		$less_than                                      = new IsLessThanOrEqualToType();
		$less_than->FieldURI                            = new PathToUnindexedFieldType();
		$less_than->FieldURI->FieldURI                  = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
		$less_than->FieldURIOrConstant                  = new FieldURIOrConstantType();
		$less_than->FieldURIOrConstant->Constant        = new ConstantValueType();
		$less_than->FieldURIOrConstant->Constant->Value = $this->stop_date->format('c');

// Build the restriction.
		$request->Restriction                              = new RestrictionType();
		$request->Restriction->And                         = new AndType();
		$request->Restriction->And->IsGreaterThanOrEqualTo = $greater_than;
		$request->Restriction->And->IsLessThanOrEqualTo    = $less_than;

//Show only unread emails
		if ($this->show_unread_only)
		{
			$unread                                      = new IsEqualToType();
			$unread->FieldURI                            = new PathToExtendedFieldType();
			$unread->FieldURI->FieldURI                  = "message:IsRead";
			$unread->FieldURIOrConstant                  = new FieldURIOrConstantType();
			$unread->FieldURIOrConstant->Constant        = new ConstantValueType();
			$unread->FieldURIOrConstant->Constant->Value = 0;

			$request->Restriction->And->IsEqualTo = $unread;
		}
//Limit to a specific sender
		if ($this->limit_to_email_address)
		{
			$limit                                      = new IsEqualToType();
			$limit->FieldURI                            = new PathToUnindexedFieldType();
			$limit->FieldURI->FieldURI                  = "message:Sender";
			$limit->FieldURIOrConstant                  = new FieldURIOrConstantType();
			$limit->FieldURIOrConstant->Constant        = new ConstantValueType();
			$limit->FieldURIOrConstant->Constant->Value = $this->limit_to_email_address;

			$request->Restriction->And->IsEqualTo = $limit;
		}

// Return all message properties.
		$request->ItemShape            = new ItemResponseShapeType();
		$request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

// Search in the user's inbox.
		$folder_id                                         = new DistinguishedFolderIdType();
		$folder_id->Id                                     = DistinguishedFolderIdNameType::INBOX;
		$request->ParentFolderIds->DistinguishedFolderId[] = $folder_id;

		$response = $this->theClient->FindItem($request);

// Iterate over the results, printing any error messages or message subjects.
		$response_messages = $response->ResponseMessages->FindItemResponseMessage;
		$messages          = array();

		for ($i = 0; $i < count($response_messages); $i++)
		{
			$response_message = $response_messages[$i];

			// Make sure the request succeeded.
			if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error     = true;
				$this->error_msg = $response_message->MessageText;
				throw new Exception($this->error_msg);
			}

			// Iterate over the messages that were found, printing the subject for each.
			if (count($response_message->RootFolder->Items->Message) > 0)
				foreach ($response_message->RootFolder->Items->Message AS $m)
					$messages[] = $m;
		}

		//No Messages
		if(count($messages) === 0)
			return $messages;

		if ($deep)
		{
			$messages = $this->__getMessageBody($messages);
		}

		return $messages;

	}

	/**
	 * This will return the Given Message. It will return all properties except any attachments.
	 * If there are attachments, check the $Message->HasAttachments property and then send through
	 * the Method $Mailbox->getAttachments() to download them
	 *
	 * @param   string  $id
	 *
	 * @return MessageType
	 * @throws Exception
	 */
	public function getMessage(string $id)
	{
		// Build the request.
		$request                       = new GetItemType();
		$request->ItemShape            = new ItemResponseShapeType();
		$request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
		$request->ItemIds              = new NonEmptyArrayOfBaseItemIdsType();

		$ItemID     = new ItemIdType();
		$ItemID->Id = $id;

		$request->ItemIds->ItemId[] = $ItemID;

		$response = $this->theClient->GetItem($request);

		$response_message = $response->ResponseMessages->GetItemResponseMessage[0];

		if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
		{
			throw new Exception("Could not get Message. " . $response_message->MessageText . ".\nMessage ID: $id");
		}

		return $response_message->Items->Message[0];
	}

	/**
	 * This will get the Attachments for the Message. The Location of the downloaded attachment will
	 * be set in the attachment property $Attachment->Path. Iterate over the message attachments
	 * $Message->Attachments->FilAttachment[]
	 *
	 * @param   MessageType  $message
	 */
	public function getAttachments(MessageType $message)
	{
		if (!isset($message->Attachments) || count($message->Attachments->FileAttachment) == 0)
		{
			return;
		}

		$this->logger->debug(__FUNCTION__, "Getting Attachments");

		// Build the request to get the attachments.
		$request                = new GetAttachmentType();
		$request->AttachmentIds = new NonEmptyArrayOfRequestAttachmentIdsType();

		// Iterate over the attachments for the message.
		foreach ($message->Attachments->FileAttachment as $attachment)
		{
			$id                                     = new RequestAttachmentIdType();
			$id->Id                                 = $attachment->AttachmentId->Id;
			$request->AttachmentIds->AttachmentId[] = $id;
		}

		$response = $this->theClient->GetAttachment($request);

//		$this->logger->debug(__FUNCTION__, print_r($response, true) . "\n\n\n");

		// Iterate over the response messages, printing any error messages or
		// saving the attachments.
		$attachment_response_messages = $response->ResponseMessages->GetAttachmentResponseMessage;

		foreach ($attachment_response_messages as $attachment_response_message)
		{
			// Make sure the request succeeded.
			if ($attachment_response_message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$code    = $attachment_response_message->ResponseCode;
				$message = $attachment_response_message->MessageText;
				$this->logger->error(__FUNCTION__, "Could not get Attachment. $message");
				continue;
			}

			// Iterate over the file attachments, saving each one.
			$attachments = $attachment_response_message->Attachments->FileAttachment;

			$message->Attachments = new NonEmptyArrayOfAttachmentsType();

			foreach ($attachments as $attachment)
			{
				$path = $this->temp_dir . "/" . time() . "_" . $attachment->Name;
				file_put_contents($path, $attachment->Content);
				$attachment->Path        = $path;
				$file                    = new SplFileObject($path);
				$attachment->ContentType = explode(';', (new finfo(FILEINFO_MIME))->file($file->getRealPath()))[0];
				$attachment->Size        = $this->convert($file->openFile()->getSize());

				$message->Attachments->FileAttachment[] = $attachment;
			}
		}
	}


	/**
	 * Mark the Message a Read. FYI: If you forward or Reply to the Message, that will automatically
	 * mark the Message as Read.
	 *
	 * @param   MessageType  $message
	 *
	 * @throws Exception
	 */
	public function markAsRead(MessageType $message): void
	{
		//Check to see if the message is already marked as read
		if ($message->IsRead)
			return;

		$request                     = new UpdateItemType();
		$request->MessageDisposition = MessageDispositionType::SAVE_ONLY;
		$request->ConflictResolution = ConflictResolutionType::ALWAYS_OVERWRITE;
		$request->ItemChanges        = array();

		$change          = new ItemChangeType();
		$change->ItemId  = $message->ItemId;
		$change->Updates = new NonEmptyArrayOfItemChangeDescriptionsType();

		$field                     = new SetItemFieldType();
		$field->FieldURI           = new PathToExtendedFieldType();
		$field->FieldURI->FieldURI = "message:IsRead";
		$field->Message            = new MessageType();
		$field->Message->IsRead    = true;

		$change->Updates->SetItemField[] = $field;

		$request->ItemChanges[] = $change;

		$response = $this->theClient->UpdateItem($request);

		if (!$response->ResponseMessages->UpdateItemResponseMessage[0]->ResponseClass == "Success")
		{
			throw new Exception("Could not mark the message as Read");
		}

		$message->IsRead = true;
	}

	/**
	 * Will move the Message to the Deleted Items Folder
	 *
	 * @param   MessageType|MessageType[]  $Message
	 *
	 * @throws Exception
	 */
	public function deleteEmail($Message): void
	{
		if (!is_array($Message))
		{
			$Message = array($Message);
		}

		$request             = new DeleteItemType();
		$request->ItemIds    = new NonEmptyArrayOfBaseItemIdsType();
		$request->DeleteType = DisposalType::MOVE_TO_DELETED_ITEMS;

		foreach ($Message as $M)
			$request->ItemIds->ItemId[] = $M->ItemId;

		$response = $this->theClient->DeleteItem($request);

		$response_messages = $response->ResponseMessages->DeleteItemResponseMessage;

		for ($i = 0; $i < count($response_messages); $i++)
		{
			$response_message = $response_messages[$i];

			if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error     = true;
				$this->error_msg = $response_message->MessageText;

				throw new Exception($this->error_msg);
			}

		}
	}

	/**
	 * Just a quick way to initialize the ExchangeMessage Class
	 * @return ExchangeMessage
	 *
	 */
	public function createNewMessage(): ExchangeMessage
	{
		return new ExchangeMessage();
	}

	/**
	 * The Main Send Message Method. This method takes either a single Message or an Array of Messages. You can
	 * mix the types and it will send all. You may Send new Messages and Reply To a Message then send them all
	 * at once. The process for this Method is to create a Draft (you can check the property $Message->IsDraft)
	 * then attach any attachments to the Message if there are any, then it will Send the Message. Each Message
	 * will then have a property $Message->IsSubmitted that is changed to true if the Message was Successful.
	 *
	 * @param   MessageType|MessageType[]|ExchangeMessage|ExchangeMessage[]|ExchangeMessageReplyTo|ExchangeMessageReplyTo[]|ExchangeMessageReplyToAll|ExchangeMessageReplyToAll[]|ExchangeMessageForward|ExchangeMessageForward[]  $Message
	 *
	 */
	public function sendMessage($Message): void
	{
		//reset the Total_Emails counter
		$this->Total_Messages_Sent = 0;

		if (!is_array($Message))
		{
			$Message = array($Message);
		}

		$messages   = array();
		$replyTo    = array();
		$replyToAll = array();
		$forward    = array();

		foreach ($Message as $M)
		{
			//We are testing, so make sure we add a notice to the message in case this actually gets out
			if($this->test_in_progress)
			{
				$mes_body = $M->getBody();
				$M->isHtml(true);
				$new_mes = "<p style='font-size: 20pt;'>************** This is a Test! If you receive this message, please disregard! Thank you. *************</p><br /><br /><br />"
					. $mes_body;
				$M->setBody($new_mes);
			}

			if ($M instanceof MessageType)
			{
				$messages[] = $M;
			}
			elseif ($M instanceof ReplyAllToItemType)
			{
				$replyToAll[] = $M;
			}
			elseif ($M instanceof ReplyToItemType)
			{
				$replyTo[] = $M;
			}
			elseif ($M instanceof ForwardItemType)
			{
				$forward[] = $M;
			}
		}

		if (count($messages) > 0)
			$this->__createDraft($messages, "NewMessage");
		if (count($replyTo) > 0)
			$this->__createDraft($replyTo, "ReplyTo");
		if (count($replyToAll) > 0)
			$this->__createDraft($replyToAll, "ReplyToAll");
		if (count($forward) > 0)
			$this->__createDraft($forward, "Forward");

		//Add Any Attachments If there are any
		$this->__addAttachments($Message);

		//Send the Message
		$this->__send($Message);

		$messages   = null;
		$replyTo    = null;
		$replyToAll = null;
	}

	/**
	 * This will get the Message Body since $Mailbox->getMessages() will only get the "Header", we have to make
	 * an extra call to get the contents of the body
	 *
	 * @param   MessageType[]  $messages
	 *
	 * @return MessageType[]|void
	 * @throws Exception
	 */
	private function __getMessageBody(array $messages)
	{

		if (count($messages) === 0)
			return;

		$request                       = new GetItemType();
		$request->ItemShape            = new ItemResponseShapeType();
		$request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
		$request->ItemIds              = new NonEmptyArrayOfBaseItemIdsType();

		foreach ($messages as $message)
			$request->ItemIds->ItemId[] = $message->ItemId;


		$response = $this->theClient->GetItem($request);

		$response_messages = $response->ResponseMessages->GetItemResponseMessage;

		$messages = array();
		for ($i = 0; $i < count($response_messages); $i++)
		{
			$response_message = $response_messages[$i];

			if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error_msg = $response_message->MessageText;
				$this->error     = true;

				throw new Exception($this->error_msg);
			}

			$messages[] = $response_message->Items->Message[0];
		}

		return $messages;

	}

	/**
	 * @param   MessageType[]  $Messages
	 *
	 * @param   string         $request_type
	 *
	 */
	private function __createDraft(array $Messages, string $request_type): void
	{
		//Build the request
		$request        = new CreateItemType();
		$request->Items = new NonEmptyArrayOfAllItemsType();

		//Save the Message to Draft
		$request->MessageDisposition = MessageDispositionType::SAVE_ONLY;

		// Add the message to the request.
		foreach ($Messages as $Message)
		{
			if ($request_type === "NewMessage")
			{
				$request->Items->Message[] = $Message;
			}
			elseif ($request_type === "ReplyToAll")
			{
				$request->Items->ReplyAllToItem[] = $Message;
			}
			elseif ($request_type === "ReplyTo")
			{
				$request->Items->ReplyToItem[] = $Message;
			}
			elseif ($request_type === "Forward")
			{
				$request->Items->ForwardItem[] = $Message;
			}
			else
			{
				$Message->error     = true;
				$Message->error_msg = "Invalid Request Type in in CreateDraft";
			}
		}

		if (count($request->Items->Message) === 0 &&
			count($request->Items->ReplyAllToItem) === 0 &&
			count($request->Items->ReplyToItem) === 0 &&
			count($request->Items->ForwardItem) === 0)
		{
			$this->logger->error(__FUNCTION__ . "    $request_type", "There were no messages to create a Draft with.");
			return;
		}

		$response = $this->theClient->CreateItem($request);

		$this->logger->debug(__FUNCTION__ . "   $request_type", print_r($response, true) . "\n\n\n");

		//New Messages process differently for some reason, so this is just for new messages
		if ($request_type === "NewMessage")
		{
			$response_messages = $response->ResponseMessages->CreateItemResponseMessage;
			for ($i = 0; $i < count($response_messages); $i++)
			{
				$response_message = $response_messages[$i];
				$Message          = $Messages[$i];

				if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
				{
					$Message->IsDraft   = false;
					$Message->error     = true;
					$Message->error_msg = "Could not create the Message as Draft.";
					$this->logger->error(__FUNCTION__, "Could not create Message Draft. " . $response_message->MessageText .
						"\nFile: " . __FILE__ . " Line: " . __LINE__);
					continue;
				}

				$Message->IsDraft = true;
				$Message->ItemId  = $response_message->Items->Message[0]->ItemId;
			}
		}
		else
		{
			//Get the ID and Change Key
			$response_messages = $response->ResponseMessages->CreateItemResponseMessage;

			for ($i = 0; $i < count($response_messages); $i++)
			{
				$response_message = $response_messages[$i];
				$Message          = $Messages[$i];

				if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
				{
					$Message->error     = true;
					$Message->error_msg = "Could not Create the Draft. " . $response_message->MessageText;
					$this->logger->error(__FUNCTION__, "Could not create Message Draft. " . $response_message->MessageText .
						"\nFile: " . __FILE__ . " Line: " . __LINE__);
					continue;
				}

				$Message->IsDraft = true;
				$Message->ItemId  = $response_message->Items->Message[0]->ItemId;
			}
		}
	}


	/**
	 * @param   ExchangeMessage[]|ExchangeMessageReplyTo[]|ExchangeMessageReplyToAll[]  $Messages
	 *
	 */
	private function __addAttachments(array $Messages): void
	{
		/*
		 * I don't like that this has to make a call for each Message that has an attachment, but that
		 * is just how Exchange works.
		 */
		foreach ($Messages as $Message)
		{
			/*
			 * This is different than the main Attachments property. If the attachments are in the main
			 * Attachments property, the message Fails to send. Must add attachments after the Message has
			 * been created as a Draft.
			 */
			if (!isset($Message->attachments) ||
				count($Message->attachments) === 0 ||
				$Message->IsDraft !== true ||
				$Message->error === true)
				continue;

			// Build the request,
			$request               = new CreateAttachmentType();
			$request->ParentItemId = $Message->ItemId;
			$request->Attachments  = new NonEmptyArrayOfAttachmentsType();

			foreach ($Message->attachments as $a)
			{
				$request->Attachments->FileAttachment[] = $a;
			}

			$response = $this->theClient->CreateAttachment($request);

			if ($response->ResponseMessages->CreateAttachmentResponseMessage[0]->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->logger->warn(__FUNCTION__, "Could not Add Attachments. " . $response->ResponseMessages->CreateAttachmentResponseMessage[0]->MessageText);
				$Message->error     = true;
				$Message->error_msg = "Could not add Attachments.";
				continue;
			}

			//Just need to get one of the Attachments RootItemChangeKey to update the Message Object
			$KeyChange = $response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey;

			$Message->ItemId->ChangeKey = $KeyChange;
			$Message->HasAttachments    = true;

			$response = null;

		}
	}

	/**
	 * @param   MessageType[]|ReplyToItemType[]|ReplyAllToItemType[]  $Messages
	 *
	 */
	private function __send(array $Messages)
	{
		// Build the request.
		$request                   = new SendItemType();
		$request->SaveItemToFolder = true;
		$request->ItemIds          = new NonEmptyArrayOfBaseItemIdsType();

// Add the message to the request.
		foreach ($Messages as $Message)
		{
			if ($Message->IsDraft !== true || $Message->error === true)
			{
				$this->Failed_Messages[] = $Message;
				continue;
			}

			$request->ItemIds->ItemId[] = $Message->ItemId;
		}

		if (count($request->ItemIds->ItemId) == 0)
		{
			$this->logger->error(__FUNCTION__, "There are no Messages to send. Failed at Drafts/Attachments.");

			return;
		}

// Configure the folder to save the sent message to.
		$send_folder                            = new TargetFolderIdType();
		$send_folder->DistinguishedFolderId     = new DistinguishedFolderIdType();
		$send_folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::SENT;

		$request->SavedItemFolderId = $send_folder;

		$response = $this->theClient->SendItem($request);

		$this->logger->debug(__FUNCTION__, print_r($response, true));

		$response_messages = $response->ResponseMessages->SendItemResponseMessage;

		for ($i = 0; $i < count($response_messages); $i++)
		{
			$response_message = $response_messages[$i];
			$Message          = $Messages[$i];

			if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error     = true;
				$this->error_msg = "Could not Send Email. A Draft was created in your Mailbox";

				$this->logger->error(__FUNCTION__, "Could not send the message. " . $response_message->MessageText);

				$Message->error     = true;
				$Message->error_msg = "Could not send the Message. " . $response_message->MessageText;

				$this->Failed_Messages[] = $Message;

				continue;
			}

			$Message->IsSubmitted = true;

			$this->Total_Messages_Sent++;
		}
		$response = null;
	}

	private function convert($size)
	{
		$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}





	/*
	 * Calendar Methods
	 */

	/**
	 * @param   Event  $event
	 *
	 * @return bool
	 * @throws EventException
	 */
	public function createNewEvent(Event $event)
	{
		$request = new CreateItemType();
		$request->SendMeetingInvitations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;
		$request->Items                  = new NonEmptyArrayOfAllItemsType();
		$request->Items->CalendarItem[]  = $event;

		//Send to the Exchange Server
		$response = $this->theClient->CreateItem($request);

		//Check the status of the Request
		$response_messages = $response->ResponseMessages->CreateItemResponseMessage;
		if ($response_messages[0]->ResponseClass != ResponseClassType::SUCCESS)
		{
			$this->error     = true;
			$this->error_msg = $response_messages[0]->MessageText;

			throw new EventException($this->error_msg);
		}
		else
		{
			$this->error     = false;
			$this->error_msg = "";
		}

		$event->ItemId         = $response_messages[0]->Items->CalendarItem[0]->ItemId;
		$event->ParentFolderId = $response_messages[0]->Items->CalendarItem[0]->ParentFolderId;

		//If there are attachments then add those
		if(count($event->attachments) > 0)
		{
			$request = new CreateAttachmentType();
			$request->Attachments = new NonEmptyArrayOfAttachmentsType();
			$request->Attachments->FileAttachment = $event->attachments;
			$request->ParentItemId = $event->ItemId;

			$response = $this->theClient->CreateAttachment($request);
			if ($response->ResponseMessages->CreateAttachmentResponseMessage[0]->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error_msg = "Could not create attachments.";
				$this->error     = true;

				throw new EventException($this->error_msg);
			}

			//Need to get the RootItemChangeKey
			$event->ItemId->ChangeKey = $response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey;

		}

		//If there are Attendees then send out the invites
		if(count($event->RequiredAttendees->Attendee) > 0)
		{
			$UpdateEvent = new UpdateEvent();
			$UpdateEvent->ItemId = $event->ItemId;

			$UpdateEvent->setSubject($event->Subject);

			$this->updateEvent($UpdateEvent, CalendarItemUpdateOperationType::SEND_TO_ALL_AND_SAVE_COPY);

			$event->ItemId->ChangeKey = $UpdateEvent->ItemId->ChangeKey;
		}

	}

	/**
	 * @param   UpdateEvent                           $event
	 *
	 * @param   string|null  $send_type Use CalendarItemUpdateOperationType constants
	 *
	 * @return bool
	 * @throws EventException
	 */
	public function updateEvent(UpdateEvent $event, ?string $send_type = null)
	{

		//must get the change_key if not set or if there is no send_type
		if ($send_type === null || empty($event->ItemId->ChangeKey))
			$Full_Event = $this->getEvent($event->ItemId->Id)[0];

		//Update the Change Key if it is not set
		if (isset($Full_Event) && empty($event->ItemId->ChangeKey))
			$event->ItemId = $Full_Event->ItemId;

		//Now Add the Attachments if any
		if (count($event->attachments) > 0)
		{
			$request               = new CreateAttachmentType();
			$request->ParentItemId = $event->ItemId;
			$request->Attachments->FileAttachment  = $event->attachments;

			$response = $this->theClient->CreateAttachment($request);
			if ($response->ResponseMessages->CreateAttachmentResponseMessage[0]->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error_msg = "Could not create attachments.";
				$this->error     = true;

				return false;
			}

			//Need to get the RootItemChangeKey
			$event->ItemId->ChangeKey = $response->ResponseMessages->CreateAttachmentResponseMessage[0]->Attachments->FileAttachment[0]->AttachmentId->RootItemChangeKey;

		}

		//Need to check if we have to send a Change Meeting Request to the Required Attendees
		//If no $send_type is provided then preform some checks
		if ($send_type === null)
		{
			//If the User added Guests then send change event
			if (count($event->guests->Attendee) > 0)
			{
				$send_type = CalendarItemUpdateOperationType::SEND_TO_ALL_AND_SAVE_COPY;
			}
			//Else we need to get the event to see if there were already some Guests invited
			else
			{
				//Need to get the deep Event to make sure we set some properties right
				$Full_Event = $this->getEvent($event->ItemId->Id)[0];

				$send_type = CalendarItemUpdateOperationType::SEND_TO_NONE;

				if (count($Full_Event->RequiredAttendees->Attendee) > 0)
					$send_type = CalendarItemUpdateOperationType::SEND_TO_ALL_AND_SAVE_COPY;
			}
		}

		// Build the request.
		$request                                        = new UpdateItemType();
		$request->ConflictResolution                    = ConflictResolutionType::ALWAYS_OVERWRITE;
		$request->SendMeetingInvitationsOrCancellations = $send_type;

		$request->ItemChanges[] = $event;

		$response         = $this->theClient->UpdateItem($request);
		$response_message = $response->ResponseMessages->UpdateItemResponseMessage[0];

		if ($response_message->ResponseClass != ResponseClassType::SUCCESS)
		{
			$this->error     = true;
			$this->error_msg = $response_message->MessageText;

			return false;
		}
		else
		{
			$this->error     = false;
			$this->error_msg = "";
		}

		$event->ItemId = $response_message->Items->CalendarItem[0]->ItemId;

		return true;
	}

	/**
	 * @param   CalendarItemType|Event|string  $event
	 *
	 * @param   string|null                    $change_key
	 * @param   MessageDispositionType|null    $send_type
	 *
	 * @return bool
	 * @throws EventException
	 */
	public function deleteEvent($event, ?string $change_key = null, ?MessageDispositionType $send_type = null)
	{
		$id = "";

		//Extract the ID out of the Object if it is a object
		if (is_object($event))
		{
			if (!in_array(get_class($event), array("Event", "CalendarItemType")))
			{
				$this->error     = true;
				$this->error_msg = "Must be of type Event or CalendarItemType";
				throw new EventException($this->error_msg);
			}

			$id         = $event->ItemId->Id;
			$change_key = $event->ItemId->ChangeKey;

			//If the User gives a send_type then use that. Otherwise lets try and figure out
			//if we need to send it to anyone
			if ($send_type === null)
			{
				//Lets see if there are any Guests we need to Send to
				if (count($event->RequiredAttendees->Attendee) > 0)
					$send_type = MessageDispositionType::SEND_AND_SAVE_COPY;
			}
		}
		elseif (!$event ||
			(is_string($event) && strlen($event) === 0)
		)
		{
			$this->error_msg = "Please provide a Id AND ChangeKey to delete";
			$this->error     = true;
			throw new EventException($this->error_msg);
		}
		else
		{
			$id = $event;
		}

		//If no change_key or send_type is null
		if (!$change_key || strlen($change_key) === 0 || !$send_type)
		{
			$calItem = $this->getEvent($id);
			if (count($calItem) !== 1)
			{
				$this->error     = true;
				$this->error_msg = "Could not find the Calendar Event.";
				throw new EventException($this->error_msg);
			}

			$calItem    = $calItem[0];
			$change_key = $calItem->ItemId->ChangeKey;
		}

		//If send_type is still null, then lets get the object from the server
		if ($send_type === null)
		{
			if (count($calItem->RequiredAttendees->Attendee) > 0)
				$send_type = MessageDispositionType::SEND_AND_SAVE_COPY;
			else
				$send_type = MessageDispositionType::SAVE_ONLY;
		}

		$request                     = new CreateItemType();
		$request->MessageDisposition = $send_type;
		$request->Items              = new NonEmptyArrayOfAllItemsType();

		$cancellation                             = new CancelCalendarItemType();
		$cancellation->ReferenceItemId            = new ItemIdType();
		$cancellation->ReferenceItemId->Id        = $id;
		$cancellation->ReferenceItemId->ChangeKey = $change_key;

		$request->Items->CancelCalendarItem[] = $cancellation;

		$response = $this->theClient->CreateItem($request);

		if ($response->ResponseMessages->CreateItemResponseMessage[0]->ResponseClass != ResponseClassType::SUCCESS)
		{
			$this->error     = true;
			$this->error_msg = $response->ResponseMessages->CreateItemResponseMessage[0]->MessageText;
			throw new EventException($this->error_msg);
		}

		return true;
	}

	/**
	 * @param   bool  $deep  Will get all elements of the Event
	 *
	 * @return CalendarItemType[]
	 * @throws EventException
	 */
	public function getEvents(bool $deep = false)
	{
		//Create the Request
		$request                  = new FindItemType();
		$request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
		$request->Traversal       = ItemQueryTraversalType::SHALLOW;

		//Return all event properties
		$request->ItemShape            = new ItemResponseShapeType();
		$request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

		//This says to get the calendar
		$folder_id     = new DistinguishedFolderIdType();
		$folder_id->Id = DistinguishedFolderIdNameType::CALENDAR;

		//Add to the request
		$request->ParentFolderIds->DistinguishedFolderId[] = $folder_id;

		$request->CalendarView            = new CalendarViewType();
		$request->CalendarView->StartDate = $this->start_date->format("c");
		$request->CalendarView->EndDate   = $this->stop_date->format('c');

		//Make the request
		$response = $this->theClient->FindItem($request);

		//Get the response from the Server
		$response_messages = $response->ResponseMessages->FindItemResponseMessage[0];
		$calendar_items    = $response_messages->RootFolder->Items->CalendarItem;
		//Make sure the request went through
		if ($response_messages->ResponseClass != ResponseClassType::SUCCESS)
		{
			throw new EventException($response_messages->MessageText, $response_messages->ResponseCode);
		}

		$calendarItems = new \SplFixedArray(count($calendar_items));
		$ids_for_deep  = new \SplFixedArray(count($calendar_items));

		$i = 0;
		foreach ($calendar_items as $CalendarItem)
		{
			$calendarItems[$i] = $CalendarItem;
			$ids_for_deep[$i]  = $CalendarItem->ItemId->Id;
			$i++;
		}

		//Clean up
		$response          = null;
		$response_messages = null;
		$calendar_items    = null;

		//Get all Properties
		if ($deep)
		{
			return $this->getEvent($ids_for_deep->toArray());
		}

		return $calendarItems->toArray();
	}

	/**
	 * @param   array|string  $id
	 *
	 * @return CalendarItemType[]
	 * @throws EventException
	 */
	public function getEvent($id)
	{
		if (!is_array($id))
		{
			$str   = $id;
			$id    = new \SplFixedArray(1);
			$id[0] = $str;
		}
		else
		{
			$id = \SplFixedArray::fromArray($id);
		}

		// Build the request.
		$request                       = new GetItemType();
		$request->ItemShape            = new ItemResponseShapeType();
		$request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
		$request->ItemIds              = new NonEmptyArrayOfBaseItemIdsType();

		foreach ($id as $ItemID)
		{
			$item     = new ItemIdType();
			$item->Id = $ItemID;

			$request->ItemIds->ItemId[] = $item;
		}

		$response = $this->theClient->GetItem($request);


		$CalendarItems = new \SplFixedArray(count($response->ResponseMessages->GetItemResponseMessage));
		$i             = 0;
		foreach ($response->ResponseMessages->GetItemResponseMessage as $Message)
		{
			if ($Message->ResponseClass != ResponseClassType::SUCCESS)
			{
				$this->error     = true;
				$this->error_msg = $Message->MessageText;
				throw new EventException($this->error_msg);
			}
			$CalendarItems[$i] = $Message->Items->CalendarItem[0];
			$i++;
		}

		return $CalendarItems->toArray();
	}
}