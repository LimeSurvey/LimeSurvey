<?php
	require_once( 'http.inc' );
	
	$fields =	array( 	'user' => 'GuinuX',
						'password' => 'mypass',
						'lang' => 'US'
				);
	
	$files = array();
	$files[] = 	array(	'name' => 'myfile1',
					'content-type' => 'text/plain',
					'filename' => 'test1.txt',
					'data' => 'Hello from File 1 !!!'
		);	
	
	$files[] = 	array(	'name' => 'myfile2',
						'content-type' => 'text/plain',
						'filename' => 'test2.txt',
						'data' => "bla bla bla\nbla bla"
		);	
	
		
	$http_client = new http( HTTP_V11, false );
	$http_client->host = 'www.myhost.com';
	if ($http_client->multipart_post( '/upload.pl', $fields, $files ) == HTTP_STATUS_OK )
  	       print($http_client->get_response_body());
        else
           print('Server returned status code : ' . $http_client->status);
	unset( $http_client );
?> 