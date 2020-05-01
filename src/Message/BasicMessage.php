<?php


namespace jamesiarmes\PhpEws\Message;


interface BasicMessage
{
	public function isHtml(bool $is_html = true): void;

	/**
	 * @param   string  $importance Please use the constants from ImportanceChoicesType
	 */
	public function setImportance(string $importance): void;

	public function setSubject(string $subject): void;

	public function setBody(string $body): void;

	public function getBody(): string;
}