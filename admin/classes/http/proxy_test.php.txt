<?php
/*********************************************************************
 * Demonstrates the use of requests via proxy
 *********************************************************************/

	header('Content-Type: text/plain');
	require_once( 'http.inc' );
	
	$http_client = new http( HTTP_V11, false );
	$http_client->host = 'www.yahoo.com';
	$http_client->use_proxy( 'ns.crs.org.ni', 3128 );
	if ($http_client->get( '/' ) == HTTP_STATUS_OK)
		print_r( $http_client );
	else
		print('Server returned ' . $http_client->status );
	unset( $http_client );
	
?> 
