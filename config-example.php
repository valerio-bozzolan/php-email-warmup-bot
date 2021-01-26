<?php
// save a copy of this 'config-example.php' file as 'config.php'

// run this in the parent directory:
//   git clone https://gitpull.it/source/php-imap-spooler/
define( 'IMAPBOT_PATH_SPOOLER', __DIR__ . '/../php-imap-spooler/class-IMAPSpooler.php' );

// how much seconds to wait before analyzing each mailbox
define( 'IMAPBOT_CYCLE_SLEEP', 20 );

// define a mailbox
MailboxCredentialsCollector::addFromArgs( [
	'name'     => 'Valerio Bozzolan 1',
	'from'     => 'whoaaaaa@succhia.cz',
	'login'    => 'whoaaaaa@succhia.cz',
	'host'     => 'ssl://mail.reyboz.it',
	'port'     => 465,
	'auth'     => 'PLAIN',
	'imap'     => '{mail.reyboz.it:993/imap/ssl}INBOX',
	'password' => 'REDACTED REDACTED REDACTED',
] );

// define another mailbox
MailboxCredentialsCollector::addFromArgs( [
	'name'     => 'Valerio Bozzolan 2',
	'from'     => 'whoaaaab@succhia.cz',
	'login'    => 'whoaaaab@succhia.cz',
	'host'     => 'ssl://mail.reyboz.it',
	'port'     => 465,
	'auth'     => 'PLAIN',
	'imap'     => '{mail.reyboz.it:993/imap/ssl}INBOX',
	'password' => 'REDACTED REDACTED REDACTED',
] );
