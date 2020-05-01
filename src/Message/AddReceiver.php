<?php


namespace jamesiarmes\PhpEws\Message;


use Exception;
use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use jamesiarmes\PhpEws\Enumeration\RoutingType;
use jamesiarmes\PhpEws\Type\EmailAddressType;

trait AddReceiver
{

	/**
	 * @param   string|array  $email
	 * @param   string        $name
	 *
	 * @return bool
	 */
	public function addRecipient($email, string $name = ""): bool
	{
		if (is_string($email))
		{
			$email = array(
				0 => array(
					'email' => $email,
					'name'  => $name
				)
			);
		}

		foreach ($email as $e)
		{
			if(!isset($e['email']))
				return false;

			$To               = new EmailAddressType();
			$To->EmailAddress = $e['email'];
			$To->Name         = (isset($e['name'])) ? $e['name'] : "";
			$To->RoutingType  = RoutingType::SMTP;

			$this->ToRecipients->Mailbox[] = $To;
		}

		return true;

	}

	/**
	 * @param   string|array  $email
	 * @param   string        $name
	 *
	 * @return bool
	 */
	public function addReplyTo($email, string $name = ""): bool
	{
		if (!$this->ReplyTo || !isset($this->ReplyTo->Mailbox))
			$this->ReplyTo = new ArrayOfRecipientsType();

		if (is_string($email))
		{
			$email = array(
				0 => array(
					'email' => $email,
					'name'  => $name
				)
			);
		}

		foreach ($email as $e)
		{
			if (!isset($e['email']))
				return false;

			$replyTo               = new EmailAddressType();
			$replyTo->EmailAddress = $e['email'];
			$replyTo->Name         = (isset($e['name'])) ? $e['name'] : "";
			$replyTo->RoutingType  = RoutingType::SMTP;

			$this->ReplyTo->Mailbox[] = $replyTo;
		}

		return true;
	}

	/**
	 * @param   string|array  $email
	 * @param   string        $name
	 *
	 * @return bool
	 */
	public function addCcRecipient($email, string $name = ""): bool
	{
		if (!$this->CcRecipients)
			$this->CcRecipients = new ArrayOfRecipientsType();

		if (is_string($email))
		{
			$email = array(
				0 => array(
					'email' => $email,
					'name'  => $name
				)
			);
		}

		foreach ($email as $e)
		{
			if (!isset($e['email']))
				return false;

			$to               = new EmailAddressType();
			$to->EmailAddress = $e['email'];
			$to->Name         = (isset($e['name'])) ? $e['name'] : "";
			$to->RoutingType  = RoutingType::SMTP;

			$this->CcRecipients->Mailbox[] = $to;
		}

		return true;
	}

	/**
	 * @param   string|array  $email
	 * @param   string        $name
	 *
	 * @return bool
	 */
	public function addBccRecipient($email, string $name = ""): bool
	{
		if (!$this->BccRecipients)
			$this->BccRecipients = new ArrayOfRecipientsType();

		if (is_string($email))
		{
			$email = array(
				0 => array(
					'email' => $email,
					'name'  => $name
				)
			);
		}

		foreach ($email as $e)
		{
			if (!isset($e['email']))
				return false;

			$to               = new EmailAddressType();
			$to->EmailAddress = $e['email'];
			$to->Name         = (isset($e['name'])) ? $e['name'] : "";
			$to->RoutingType  = RoutingType::SMTP;

			$this->BccRecipients->Mailbox[] = $to;
		}

		return true;
	}

}