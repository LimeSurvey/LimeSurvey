<?php

/**
 * Example that changes html of phpcas messages
 *
 * PHP Version 5
 *
 * @file     example_html.php
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
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// customize HTML output
phpCAS::setHTMLHeader(
    '<html>
  <head>
    <title>__TITLE__</title>
  </head>
  <body>
  <h1>__TITLE__</h1>'
);
phpCAS::setHTMLFooter(
    '<hr>
    <address>
      phpCAS __PHPCAS_VERSION__,
      CAS __CAS_VERSION__ (__SERVER_BASE_URL__)
    </address>
  </body>
</html>'
);

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// for this test, simply print that the authentication was successfull
?>
<html>
  <head>
    <title>phpCAS simple client with HTML output customization</title>
  </head>
  <body>
    <h1>Successfull Authentication!</h1>
    <?php require 'script_info.php' ?>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <p>phpCAS version is <b><?php echo phpCAS::getVersion(); ?></b>.</p>
  </body>
</html>
