<?php


namespace jamesiarmes\PhpEws\Events;

use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttendeesType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Enumeration\ImportanceChoicesType;
use jamesiarmes\PhpEws\Enumeration\ItemClassType;
use jamesiarmes\PhpEws\Enumeration\RoutingType;
use jamesiarmes\PhpEws\Enumeration\SensitivityChoicesType;
use jamesiarmes\PhpEws\Events\EventException\EventException;
use jamesiarmes\PhpEws\Message\AddAttachment;
use jamesiarmes\PhpEws\Type\AttendeeType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\FileAttachmentType;

class Event extends CalendarItemType
{

	use AddAttachment;

	public function __construct()
	{

		$this->Body           = new BodyType();
		$this->Body->BodyType = BodyTypeType::TEXT;
		$this->Body->_        = "";

		$this->AllowNewTimeProposal = false;

		$this->IsAllDayEvent = true;

		$this->IsResponseRequested = true;

		$this->ReminderIsSet              = true;
		$this->ReminderMinutesBeforeStart = 30;

		$this->ItemClass = new ItemClassType();
		$this->ItemClass->_ = ItemClassType::APPOINTMENT;

		$this->Sensitivity    = new SensitivityChoicesType();
		$this->Sensitivity->_ = SensitivityChoicesType::NORMAL;

		$this->Importance    = new ImportanceChoicesType();
		$this->Importance->_ = ImportanceChoicesType::NORMAL;

	}

	public function isAllowedToProposeNewTime(bool $bool = true)
	{
		$this->AllowNewTimeProposal = $bool;
	}

	public function isHTML(bool $is_html = true)
	{
		if ($is_html)
			$this->Body->BodyType = BodyTypeType::HTML;
		else
			$this->Body->BodyType = BodyTypeType::TEXT;
	}

	public function isResponseRequested(bool $bool = true)
	{
		$this->IsResponseRequested = $bool;
	}

	public function isAllDayEvent(bool $bool = true)
	{
		$this->IsAllDayEvent = $bool;
	}

	public function isReminderSet(bool $bool = true)
	{
		$this->ReminderIsSet = $bool;
	}

	public function setStartDateTime(\DateTime $start)
	{
		$this->Start = $start->format("c");

		$this->IsAllDayEvent = false;

		//Default the End Time to 30 minutes
		$start->modify("+30 minutes");

		$this->setEndDateTime($start);
	}

	public function setEndDateTime(\DateTime $end)
	{
		$this->End = $end->format("c");
	}

	public function setReminderMinutesBeforeStart(int $minutes)
	{
		$this->ReminderMinutesBeforeStart = $minutes;
	}

	public function setSensitivity(SensitivityChoicesType $type)
	{
		$this->Sensitivity->_ = $type;
	}

	public function setImportance(ImportanceChoicesType $type)
	{
		$this->Importance->_ = $type;
	}

	public function setSubject(string $subject)
	{
		$this->Subject = $subject;
	}

	public function setBody(string $body)
	{
		$this->Body->_ = $body;
	}

	public function setLocation(string $location)
	{
		$this->Location = $location;
	}

	/**
	 * @param   array|string  $email
	 * @param   string        $name
	 *
	 * @throws EventException
	 */
	public function addGuest($email, $name = "")
	{
		if (!$this->RequiredAttendees)
			$this->RequiredAttendees = new NonEmptyArrayOfAttendeesType();

		//put in array if not
		if (!is_array($email))
		{
			$email = array(
				0 => array(
					"email" => $email,
					"name"  => $name
				)
			);
		}

		foreach ($email as $guest)
		{
			if (!isset($guest['email']))
			{
				throw new EventException("Email Array must have keys \"email\" and \"name\"");
			}

			$attendee                        = new AttendeeType();
			$attendee->Mailbox               = new EmailAddressType();
			$attendee->Mailbox->EmailAddress = $guest['email'];
			$attendee->Mailbox->Name         = $guest['name'];
			$attendee->Mailbox->RoutingType  = RoutingType::SMTP;

			$this->RequiredAttendees->Attendee[] = $attendee;
		}
	}

}