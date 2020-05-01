<?php


namespace jamesiarmes\PhpEws\Mailbox;

if(!class_exists("jamesiarmes\PhpEws\Mailbox\Mailbox"))
	require_once __DIR__ . '/Mailbox.php';

class NoReplyMailbox extends Mailbox
{

	public function __construct(string $username = "", string $password = "")
	{
		if(empty($username))
		{
			$username = "";
			$password = "";
		}

		parent::__construct($username, $password);
	}

	public function sendMessage($Message): void
	{
		if(!is_array($Message))
		{
			$Message = array($Message);
		}

		//Add the Automated Email footer
		$footer = "<br /><br />" . str_repeat("-", 125) . "<br />";
		$footer .= "This is an automated email. Please do not respond to this email as it is not monitored.";

		foreach ($Message as $M)
		{
			$M->isHtml(true);
			$M->setBody($M->getBody() . $footer);
		}


		parent::sendMessage($Message);
	}
}