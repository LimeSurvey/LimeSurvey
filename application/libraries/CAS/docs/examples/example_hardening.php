<?php

/**
 * Advanced example for hardening the phpcas client
 *
 * PHP Version 5
 *
 * @file     example_hardening.php
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

// Harden session cookie to prevent some attacks on the cookie (e.g. XSS)
session_set_cookie_params($client_lifetime, $client_path, $client_domain, $client_secure, $client_httpOnly);

// Initialize phpCAS
phpCAS::client(SAML_VERSION_1_1, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
// phpCAS::setNoCasServerValidation();

// Handle SAML logout requests that emanate from the CAS host exclusively.
// Failure to restrict SAML logout requests to authorized hosts could
// allow denial of service attacks where at the least the server is
// tied up parsing bogus XML messages.
phpCAS::handleLogoutRequests(true, $cas_real_hosts);

// Force CAS authentication on any page that includes this file
phpCAS::forceAuthentication();

// Some small code triggered by the logout button
if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
}
?>
<html>
  <head>
    <title>Advanced SAML 1.1 example</title>
  </head>
  <body>
<h2>Advanced SAML 1.1 example</h2>
<?php require 'script_info.php' ?>

Authentication succeeded for user
<strong><?php echo phpCAS::getUser(); ?></strong>.

<h3>User Attributes</h3>
<ul>
<?php
foreach (phpCAS::getAttributes() as $key => $value) {
    if (is_array($value)) {
        echo '<li>', $key, ':<ol>';
        foreach ($value as $item) {
            echo '<li><strong>', $item, '</strong></li>';
        }
        echo '</ol></li>';
    } else {
        echo '<li>', $key, ': <strong>', $value, '</strong></li>' . PHP_EOL;
    }
}
    ?>
</ul>
<p><a href="?logout=">Logout</a></p>
</body>
</html>
