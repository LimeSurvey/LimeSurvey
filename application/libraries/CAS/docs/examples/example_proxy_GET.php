<?php

/**
 *  Example for a proxy that makes a GET request.
 *
 * PHP Version 5
 *
 * @file     example_proxy_GET.php
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

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// moreover, a PGT was retrieved from the CAS server that will
// permit to gain accesses to new services.

?>
<html>
  <head>
    <title>phpCAS proxy example #2</title>
    <link rel="stylesheet" type='text/css' href='example.css'/>
  </head>
  <body>
    <h1>phpCAS proxied proxy example</h1>
    <?php require 'script_info.php' ?>
    <p>the user's login is <b><?php echo phpCAS::getUser(); ?></b>.</p>
    <h2>Response from service <?php echo $serviceUrl; ?></h2>
<?php
flush();

// call a service and change the color depending on the result
try {
    $service = phpCAS::getProxiedService(PHPCAS_PROXIED_SERVICE_HTTP_GET);
    $service->setUrl($serviceUrl);
    $service->send();
    if ($service->getResponseStatusCode() == 200) {
        echo '<div class="success">';
        echo $service->getResponseBody();
        echo '</div>';
    } else {
        // The service responded with an error code 404, 500, etc.
        echo '<div class="error">';
        echo 'The service responded with a '
        . $service->getResponseStatusCode() . ' error.';
        echo '</div>';
    }
} catch (CAS_ProxyTicketException $e) {
    if ($e->getCode() == PHPCAS_SERVICE_PT_FAILURE) {
        echo '<div class="error">';
        echo "Your login has timed out. You need to log in again.";
        echo '</div>';
    } else {
        // Other proxy ticket errors are from bad request format (shouldn't happen)
        // or CAS server failure (unlikely) so lets just stop if we hit those.
        throw $e;
    }
} catch (CAS_ProxiedService_Exception $e) {
    // Something prevented the service request from being sent or received.
    // We didn't even get a valid error response (404, 500, etc), so this
    // might be caused by a network error or a DNS resolution failure.
    // We could handle it in some way, but for now we will just stop.
    throw $e;
}

                                                             ?>
  </body>
</html>
