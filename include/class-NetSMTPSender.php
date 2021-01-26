<?php


function error_wp_net_smtp( $title, $message ) {
	throw new Exception( "$title: $message" );
}

class NetSMTPSender {

	public static function send( $credentials, $to, $subject, $message, $additional_headers = '', $more = '' ) {

		// Force array
		if( ! is_array( $to ) ) {
			$to = [ $to ];
		}

		$socket_options = [
			'ssl' => [
				'verify_peer_name' => false,
				'verify_peer'      => false,
			],
		];

		if( ! ($smtp = new Net_SMTP( $credentials->host, $credentials->port, null, false, 0, $socket_options ) ) ) {
			error_wp_net_smtp(
				'Unable to instantiate Net_SMTP object',
				$smtp->getUserInfo()
			);
			return false;
		}

		if( $credentials->isDebug() ) {
			$smtp->setDebug( true );
		}

		if( PEAR::isError( $e = $smtp->connect() ) ) {
			error_wp_net_smtp(
				'Error connect',
				$e->getMessage()
			);
			return false;
		}

		if( PEAR::isError( $e = $smtp->auth( $credentials->login, $credentials->password, $credentials->auth, true, '', true ) ) ) {
			error_wp_net_smtp(
				'Error auth',
				$e->getMessage()
			);
			return false;
		}

		if( PEAR::isError( $smtp->mailFrom( $credentials->from ) ) ) {
			error_wp_net_smtp(
				'Error set from',
				$res->getMessage()
			);
			return false;
		}

		foreach( $to as $i => $single_to ) {
			if( filter_var( $single_to, FILTER_VALIDATE_EMAIL ) === false ) {
				unset( $to[$i] );

				error_wp_net_smtp(
					'Wrong e-mail address stripped out',
					$single_to
				);
				continue;
			}

			if( PEAR::isError( $res = $smtp->rcptTo( $single_to ) ) ) {
				error_wp_net_smtp(
					'Error set To',
					$res->getMessage()
				);
				return false;
			}
		}

		if( count( $to ) === 0 ) {
			error_wp_net_smtp( 'No email sent', 'no addresses' );
			return false;
		}

		$headers = [
			'MIME-Version' => '1.0',
			'Subject'      => $subject,
			'To'           => implode( ',', $to ),
			'From'         => sprintf(
				'%s <%s>',
				$credentials->name,
				$credentials->from
			),
			'Content-Type' => sprintf(
				'text/plain;charset=%s',
				'utf-8'
			),
			'X-Mailer'     => 'Net/SMTP.php via WordPress in Debian GNU/Linux asd',
		];

		$merge = [];
		foreach( $headers as $header => $value ) {
			$value = trim( $value );
			$merge[] = sprintf('%s: %s', $header, $value);
		}
		$headers = $additional_headers . implode( "\r\n" , $merge );

		$error = PEAR::isError( $smtp->data( "$headers\r\n$message" ) );

		$smtp->disconnect();

		return ! $error;

	}
}

