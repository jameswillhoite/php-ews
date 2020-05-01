<?php


namespace jamesiarmes\PhpEws\Message;


trait BasicTraits
{
	public $error = false;
	public $error_msg = "";
	public $IsDraft = false;
	public $IsSubmitted = false;
	public $HasAttachments = false;
}