<?php


namespace jamesiarmes\PhpEws\Events;


use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttendeesType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfItemChangeDescriptionsType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemUpdateOperationType;
use jamesiarmes\PhpEws\Enumeration\ImportanceChoicesType;
use jamesiarmes\PhpEws\Enumeration\RoutingType;
use jamesiarmes\PhpEws\Enumeration\SensitivityChoicesType;
use jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
use jamesiarmes\PhpEws\Events\EventException\EventException;
use jamesiarmes\PhpEws\Message\AddAttachment;
use jamesiarmes\PhpEws\Type\AppendToItemFieldType;
use jamesiarmes\PhpEws\Type\AttachmentIdType;
use jamesiarmes\PhpEws\Type\AttendeeType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\FileAttachmentType;
use jamesiarmes\PhpEws\Type\ItemAttachmentType;
use jamesiarmes\PhpEws\Type\ItemChangeType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use jamesiarmes\PhpEws\Type\SetItemFieldType;

class UpdateEvent extends ItemChangeType
{
	use AddAttachment;
	/**
	 * @var NonEmptyArrayOfAttendeesType
	 */
	public $guests;

	public function __construct(string $id = "", string $change_key = "")
	{
		$this->ItemId      = new ItemIdType();
		$this->Updates     = new NonEmptyArrayOfItemChangeDescriptionsType();
		$this->guests      = new NonEmptyArrayOfAttendeesType();


		if (!empty($id))
			$this->ItemId->Id = $id;
		if (!empty($change_key))
			$this->ItemId->ChangeKey = $change_key;
	}

	public function setId(string $id)
	{
		$this->ItemId->Id = $id;
	}

	public function setChangeKey(string $change_key)
	{
		$this->ItemId->ChangeKey = $change_key;
	}

	public function setSubject(string $subject)
	{
		$field                        = new SetItemFieldType();
		$field->FieldURI              = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI    = UnindexedFieldURIType::ITEM_SUBJECT;
		$field->CalendarItem          = new CalendarItemType();
		$field->CalendarItem->Subject = $subject;

		$this->Updates->SetItemField[] = $field;
	}

	public function setBody(string $body, bool $is_html = false)
	{
		$field                               = new SetItemFieldType();
		$field->FieldURI                     = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI           = UnindexedFieldURIType::ITEM_BODY;
		$field->CalendarItem                 = new CalendarItemType();
		$field->CalendarItem->Body           = new BodyType();
		$field->CalendarItem->Body->BodyType = ($is_html) ? BodyTypeType::HTML : BodyTypeType::TEXT;
		$field->CalendarItem->Body->_        = $body;

		$this->Updates->SetItemField[] = $field;
	}

	public function setStartTime(\DateTime $start)
	{
		$field                      = new SetItemFieldType();
		$field->FieldURI            = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI  = UnindexedFieldURIType::CALENDAR_START;
		$field->CalendarItem        = new CalendarItemType();
		$field->CalendarItem->Start = $start->format("c");

		$this->Updates->SetItemField[] = $field;

		$this->setEndTime($start->modify('30 minutes'));
	}

	public function setEndTime(\DateTime $end)
	{
		$field                     = new SetItemFieldType();
		$field->FieldURI           = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI = UnindexedFieldURIType::CALENDAR_END;
		$field->CalendarItem       = new CalendarItemType();
		$field->CalendarItem->End  = $end->format("c");

		$this->Updates->SetItemField[] = $field;
	}

	public function setIsResponseRequested(bool $bool)
	{
		$field                                    = new SetItemFieldType();
		$field->FieldURI                          = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI                = UnindexedFieldURIType::CALENDAR_IS_RESPONSE_REQUESTED;
		$field->CalendarItem                      = new CalendarItemType();
		$field->CalendarItem->IsResponseRequested = $bool;

		$this->Updates->SetItemField[] = $field;
	}

	public function setIsAllDayEvent(bool $bool)
	{
		if ($bool)
		{
			$field                         = new SetItemFieldType();
			$field->FieldURI               = new PathToUnindexedFieldType();
			$field->FieldURI->FieldURI     = UnindexedFieldURIType::CALENDAR_START;
			$field->CalendarItem           = new CalendarItemType();
			$field->CalendarItem->Start    = "";
			$this->Updates->SetItemField[] = $field;

			$field                         = new SetItemFieldType();
			$field->FieldURI               = new PathToUnindexedFieldType();
			$field->FieldURI->FieldURI     = UnindexedFieldURIType::CALENDAR_END;
			$field->CalendarItem           = new CalendarItemType();
			$field->CalendarItem->End      = "";
			$this->Updates->SetItemField[] = $field;
		}

		$field                              = new SetItemFieldType();
		$field->FieldURI                    = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI          = UnindexedFieldURIType::CALENDAR_IS_ALL_DAY_EVENT;
		$field->CalendarItem                = new CalendarItemType();
		$field->CalendarItem->IsAllDayEvent = $bool;

		$this->Updates->SetItemField[] = $field;


	}

	public function setIsAllowNewTimeProposal(bool $bool)
	{
		$field                                     = new SetItemFieldType();
		$field->FieldURI                           = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI                 = UnindexedFieldURIType::CALENDAR_ALLOW_NEW_TIME_PROPOSAL;
		$field->CalendarItem                       = new CalendarItemType();
		$field->CalendarItem->AllowNewTimeProposal = $bool;
		$this->Updates->SetItemField[]             = $field;
	}

	public function setIsReminderSet(bool $bool)
	{
		$field                              = new SetItemFieldType();
		$field->FieldURI                    = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI          = UnindexedFieldURIType::ITEM_REMINDER_IS_SET;
		$field->CalendarItem                = new CalendarItemType();
		$field->CalendarItem->ReminderIsSet = $bool;
		$this->Updates->SetItemField[]      = $field;
	}

	public function setLocation(string $location)
	{
		$field                         = new SetItemFieldType();
		$field->FieldURI               = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI     = UnindexedFieldURIType::CALENDAR_LOCATION;
		$field->CalendarItem           = new CalendarItemType();
		$field->CalendarItem->Location = $location;
		$this->Updates->SetItemField[] = $field;
	}

	public function setReminderMinutesBeforeStart(int $minutes)
	{
		$field                                           = new SetItemFieldType();
		$field->FieldURI                                 = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI                       = UnindexedFieldURIType::ITEM_REMINDER_MINUTES_BEFORE_START;
		$field->CalendarItem                             = new CalendarItemType();
		$field->CalendarItem->ReminderMinutesBeforeStart = $minutes;
		$this->Updates->SetItemField[]                   = $field;
	}

	public function setSensitivity(SensitivityChoicesType $type)
	{
		$field                            = new SetItemFieldType();
		$field->FieldURI                  = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI        = UnindexedFieldURIType::ITEM_SENSITIVITY;
		$field->CalendarItem              = new CalendarItemType();
		$field->CalendarItem->Sensitivity = $type;
		$this->Updates->SetItemField[]    = $field;
	}

	public function setImportance(ImportanceChoicesType $type)
	{
		$field                           = new SetItemFieldType();
		$field->FieldURI                 = new PathToUnindexedFieldType();
		$field->FieldURI->FieldURI       = UnindexedFieldURIType::ITEM_IMPORTANCE;
		$field->CalendarItem             = new CalendarItemType();
		$field->CalendarItem->Importance = $type;
		$this->Updates->SetItemField[]   = $field;
	}

	/**
	 * @param           $email
	 * @param   string  $name
	 *
	 * @throws EventException
	 */
	public function addGuest($email, string $name = "")
	{
		if(count($this->guests->Attendee) === 0)
		{
			$field = new AppendToItemFieldType();
			$field->CalendarItem->RequiredAttendees = $this->guests;

			$this->Updates->AppendToItemField[] = $field;
		}

		if (!is_array($email))
		{
			$email = array(
				0 => array(
					"email" => $email,
					"name"  => $name
				)
			);
		}

		foreach ($email as $e)
		{
			if (!isset($e['email']))
				throw new EventException("addGuest doesn't have the key \"email\"");

			$guest                        = new AttendeeType();
			$guest->Mailbox               = new EmailAddressType();
			$guest->Mailbox->EmailAddress = $e['email'];
			$guest->Mailbox->Name         = (isset($e['name'])) ? $e['name'] : "";
			$guest->Mailbox->RoutingType  = RoutingType::SMTP;

			$this->guests->Attendee[] = $guest;
		}

	}


}