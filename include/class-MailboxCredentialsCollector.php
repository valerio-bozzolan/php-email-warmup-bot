<?php

class MailboxCredentialsCollector {

	private static $all = [];

	public static function addFromArgs( $args ) {
		self::$all[] = MailboxCredentials::createFromArgs( $args );
	}

	public static function all() {
		return self::$all;
	}

}
