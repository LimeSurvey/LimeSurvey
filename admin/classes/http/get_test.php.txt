<?php

/*********************************************************************
 * Demonstrates the use of the get() method
 *********************************************************************/
	require_once( 'http.inc' );
	header( 'Content-Type: text/xml' );
	
	// Grab a RDF file from phpdeveloper.org and display it
	$http_client = new http( HTTP_V11, false);
	$http_client->host = 'www.phpdeveloper.org';
	
	if ( $http_client->get( '/phpdev.rdf' ) == HTTP_STATUS_OK)
		print( $http_client->get_response_body() );
	else
		print( 'Server returned ' .  $http_client->status );

	unset( $http_client );

?> 