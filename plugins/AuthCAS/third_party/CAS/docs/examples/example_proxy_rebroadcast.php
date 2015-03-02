<?php

/**
 *  Example for a proxy that rebroadcasts all PGTs to different servers in the
 *  cluster
 *
 * PHP Version 5
 *
 * @file     example_proxy_rebroadcast.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

// Load the settings from the central config file
require_once 'config.php';
// Load the CAS lib
require_once $phpcas_path . '/CAS.php';

// Enable debugging
phpCAS::setDebug();

// Initialize phpCAS
phpCAS::proxy(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// Set the nodes for rebroadcasting pgtIou/pgtId and logoutRequest
phpCAS::addRebroadcastNode($rebroadcast_node_1);
phpCAS::addRebroadcastNode($rebroadcast_node_2);

// handle incoming logout requests
phpCAS::handleLogoutRequests();

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

?>
<html>
  <head>
    <title>phpCAS proxy rebroadcast example</title>
    <link rel="stylesheet" type='text/css' href='example.css'/>
  </head>
  <body>
    <h1>phpCAS proxy rebroadcast example</h1>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
  </body>
</html>
