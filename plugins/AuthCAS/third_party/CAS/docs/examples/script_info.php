<?php
/**
 *  Small script to add some info about the example script that is running.
 *  Adds some info that makes it easier to distinguish different proxy sessions
 *
 * PHP Version 5
 *
 * @file     script_info.php
 * @category Authentication
 * @package  PhpCAS
 * @author   Joachim Fritschi <jfritschi@freenet.de>
 * @author   Adam Franco <afranco@middlebury.edu>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @link     https://wiki.jasig.org/display/CASC/phpCAS
 */ ?>
    <dl style='border: 1px dotted; padding: 5px;'>
      <dt>Current script</dt><dd><?php print basename($_SERVER['SCRIPT_NAME']); ?></dd>
      <dt>session_name():</dt><dd> <?php print session_name(); ?></dd>
      <dt>session_id():</dt><dd> <?php print session_id(); ?></dd>
    </dl>