#!/usr/bin/php
<?php
# Copyright (c) 2021 Valerio Bozzolan (https://boz.reyboz.it)
# PHP email warmup bot
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

// use system signals
pcntl_async_signals( true );

// install new signal handlers
pcntl_signal( SIGTERM, 'sig_handler' );
pcntl_signal( SIGINT,  'sig_handler' );

require 'autoload.php';

// flag indicating that an error raised
$error = false;

// flag indicating that we can continue to scan again the mailbox
$loop = true;

// let's go
message( "started" );

do {
	try {

		foreach( MailboxCredentialsCollector::all() as $credentials ) {

			// imap connection
			$spooler = new reyboz\IMAPSpooler(
				$credentials->imap,
				$credentials->login,
				$credentials->password
			);

			// set the callback for every e-mail
			$spooler->setEmailHandler( function ( $body, $headers, $info ) use ( $credentials ) {

				message( "connected" );

				// complete message
				$email_raw = $headers . $body;

				// '<something@asd.it>'
				$return_path = null;
				$subject = null;

				$parser = mailparse_msg_create(); // MUST be destroyed at the end of the script
				mailparse_msg_parse( $parser, $email_raw );
				$structure = mailparse_msg_get_structure( $parser ); // Ex. ["1", "1.1", "1.2"]
				foreach( $structure as $part_label ) { // Search among each e-mail part
					$part = mailparse_msg_get_part( $parser, $part_label ); // Parse a specified part
					$part_data = mailparse_msg_get_part_data( $part ); // Get parsed part data, header and meta values
					$headers = $part_data['headers'];

					// asd
					$subject = $headers['subject'] ?? null;

					$return_path = $headers[ 'return-path' ]
					            ?? $headers[ 'from' ]
					            ?? null;

					if( $return_path ) {

						// sometime this is a damn array
						if( is_array( $return_path ) ) {
							$return_path = $return_path[0];
						}

						break;
					}
				}

				// extract 'something@asd.it' from '<something@asd.it>' or 'Foo <something@asd.it>'
				$return_path_email = null;
				if( $return_path ) {
					$return_path_data = mailparse_rfc822_parse_addresses( $return_path );
					foreach( $return_path_data as $email_data ) {

						// address infos
						$return_path_address = $email_data['address'];
						break;
					}

				}

				if( $return_path_address ) {

					$reply_message = "Ciao bello!\n";
					$reply_message .= reply_email( $body );

					message( sprintf(
						"trying to send email from %s to $return_path_address",
						$credentials->from
					) );

					//TODO: in reply to
					// https://stackoverflow.com/questions/45690336/do-all-email-clients-use-in-reply-to-field-in-email-header


					$result = NetSMTPSender::send( $credentials, $return_path_address, "Re: $subject", $reply_message, $additional_headers = '', $more = '' );
					if( $result ) {
						message( sprintf(
							"sent email to %s",
							$return_path_address
						) );
					}

					$delete = true;
				} else {
					$delete = false;
				}

				return $delete;
			} );

			// open the connection
			$spooler->open();

			// just process all and then quit
			$spooler->processAll();

			// close the connection
			$spooler->close();

			message( "wait" );

			// wait some time
			sleep( IMAPBOT_CYCLE_SLEEP );

		}

	} catch( Exception $e ) {

		printf(
			"SMTP bot error (%s): %s\n",
			get_class( $e ),
			$e->getMessage()
		);

		printf(
			"  Trace: %s\n",
			$e->getTraceAsString()
		);

		// we can't just stop looping because sometime the user sends a wrong command and we get an Exception
		// $loop  = false;

		$error = true;
	}

} while( $loop );

/**
 * Operating system signal handler
 *
 * @param $signo   int
 * @param $siginfo mixed
 */
function sig_handler( $signo, $siginfo ) {

	// stop looping
	$GLOBALS['loop'] = false;

	// eventually close the spooler
	$GLOBALS['spooler']->close();

	// just warn about this signal
	message( "quit after SIG $signo" );

	// quit
	if( $GLOBALS['error'] ) {
		exit( 1 );
	} else {
		exit( 0 );
	}
}

/**
 * Print a message to standard output with a date
 *
 * @param string $message
 */
function message( $message ) {
	printf( "[%s] %s\n", date( 'c' ), $message );
}

function strippa_minchia( $message ) {
	throw new Exception( "to be implemented asd to strip the minch" );
}

/**
 * asd
 * >asd
 */
function reply_email( $message ) {

	$message = trim( $message );

	$message = str_replace( "\n", "\n>", ">$message" );

	return $message;
}
