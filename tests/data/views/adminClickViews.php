<?php
/**
 *  LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

use ls\tests\TestBaseClassWeb;

/**
 * This contains a list of survey-related admin views that we can loop for testing
 */
return [
    // Central participants DB ---------------------------------------
    // --------------------------------------------------
    ['setUserPermissions', ['route'=>'user/sa/setuserpermissions/uid/{UID}','clickId'=>'set-user-permissions-{UID}','username'=> TestBaseClassWeb::$noPermissionsUserUsername]],
    ['setUserTemplates', ['route'=>'user/sa/setusertemplates/uid/{UID}','clickId'=>'set-user-templates-{UID}','username'=> TestBaseClassWeb::$noPermissionsUserUsername]],
];