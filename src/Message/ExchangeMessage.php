<?php

namespace jamesiarmes\PhpEws\Message;

use jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\MessageType;

class ExchangeMessage extends MessageType implements BasicMessage
{

	use AddAttachment;
	use AddReceiver;
	use BasicTraits;

	public function __construct()
	{
		$this->ToRecipients = new ArrayOfRecipientsType();

		$this->Body           = new BodyType();
		$this->Body->BodyType = BodyTypeType::HTML;
	}

	public function isHtml(bool $is_html = true): void
	{
		if(!isset($this->Body))
			$this->Body = new BodyType();

		if ($is_html)
			$this->Body->BodyType = BodyTypeType::HTML;
		else
			$this->Body->BodyType = BodyTypeType::TEXT;
	}

	/**
	 * @param   string  $importance <b>Please use the constants from <em>ImportanceChoicesType</em></b>
	 */
	public function setImportance(string $importance): void
	{
		$this->Importance = $importance;
	}

	public function setSubject(string $subject): void
	{
		$this->Subject = $subject;
	}

	public function setBody(string $body): void
	{
		$this->Body->_ = $body;
	}

	public function getBody(): string
	{
		return $this->Body->_;
	}
}