<?php

class MailboxCredentials {

	public $from;
	public $host;
	public $port;
	public $auth;
	public $debug;
	public $login;
	public $name;
	public $imap;
	public $password;

	/**
	 * Constructor
	 */
	public function __construct() {
		// asd non ho voglia
	}

	public static function createFromArgs( $args ) {

		$asd = new self();

		$asd->host     = $args['host'];
		$asd->port     = $args['port'];
		$asd->from     = $args['from'];
		$asd->login    = $args['login'];
		$asd->password = $args['password'];
		$asd->auth     = $args['auth'];
		$asd->debug    = $args['debug'] ?? false;
		$asd->name     = $args['name']  ?? $args['from'];
		$asd->imap     = $args['imap'];

		return $asd;
	}

	public function isDebug() {
		return $this->debug ?? false;
	}

}
