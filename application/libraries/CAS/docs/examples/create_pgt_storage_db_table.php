<?php

/**
 *  Script that generates a default table for PGT/PGTiou storage. This script
 *  assumes a database with proper permissions exists and we are habe
 *  permissions to create a table.
 *  All database settings have to be set in the config.php file. Or the
 *  CAS_PGTStorage_Db() options:
 *  $db, $db_user, $db_password, $db_table, $driver_options
 *  have to filled out directly. Option examples can be found in the
 *  config.example.php
 *
 * PHP Version 5
 *
 * @file     create_pgt_storage_table.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */

// Load the settings from the central config file
require_once 'config.php';
// Load the CAS lib
require_once $phpcas_path . '/CAS.php';


// Dummy client because we need a 'client' object
$client = new CAS_Client(
    CAS_VERSION_2_0, true, $cas_host, $cas_port, $cas_context, false
);

// Set the torage object
$cas_obj = new CAS_PGTStorage_Db(
    $client, $db, $db_user, $db_password, $db_table, $driver_options
);
$cas_obj->init();
$cas_obj->createTable();
?>
<html>
  <head>
    <title>phpCAS PGT db storage table creation</title>
    <link rel="stylesheet" type='text/css' href='example.css'/>
  </head>
<body>
<div class="success">
<?php
echo 'Table <b>' . $db_table . '</b> successfully created in database <b>' . $db . '</b>';
?>
</div>
</body>
</html>