<?php
/**
 * This contains a list of survey-related admin views that we can loop for testing
 * // TODO not complete views list
 */
return [

    // Users ---------------------------------------
    // --------------------------------------------------

    ['usersIndex', ['route'=>'user/sa/index']],
    ['modifyUser', ['route'=>'user/sa/modifyuser/uid/{UID}']],

    // needs POST implementation
    ['setUserPermissions', ['route'=>'user/sa/setuserpermissions/uid/{UID}']],
    ['setUserTemplates', ['route'=>'user/sa/setusertemplates/uid/{UID}']],
];