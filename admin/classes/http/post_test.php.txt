<?php
require_once( 'http.inc' );
header( 'Content-Type: text/plain' );

$form = array(
				'value' => 1,
				'date' => '05/20/02',
				'date_fmt' => 'us',
				'result' => 1,
				'lang' => 'eng',
				'exch' => 'USD',
				'Currency' => 'EUR',
				'format' => 'HTML',
				'script' => '../convert/fxdaily',
				'dest' => 'Get Table'
			);

	$http_client = new http( HTTP_V11, true );
	$http_client->host = 'www.oanda.com';
	$status = $http_client->post( '/convert/fxdaily', $form, 'http://www.oanda.com/convert/fxdaily' );
	if ( $status == HTTP_STATUS_OK ) {
		print( $http_client->get_response_body() );
	} else {
		print( "An error occured while requesting your file !\n" );
		print_r( $http_client );
	}
	$http_client->disconnect();
	unset( $http_client );

?>