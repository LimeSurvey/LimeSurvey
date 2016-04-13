<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */


// This file provide a way to automatically republish assets for people updating manually (via zip extraction or git pull)
// When the admin interface check for updates, it also check the value of this field. If it's true, then it republish the assets and it set it to false
// So in the Git Repository, it should be always set to true.

$config['republish_assets'] = true;
return $config;
?>
