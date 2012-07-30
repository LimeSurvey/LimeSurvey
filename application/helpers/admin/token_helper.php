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
*	$Id$
*/


/**
* Creates the basic token table for a survey
*
* @param mixed $iSurveyID
* @param mixed $aAttributeFields
* @return False if failed , else DB object
*/
function createTokenTable($iSurveyID, $aAttributeFields=array())
{
    Yii::app()->loadHelper('database');
    $fields = array(
    'tid' => 'pk',
    'participant_id' => 'varchar(50)',
    'firstname' => 'varchar(40)',
    'lastname' => 'varchar(40)',
    'email' => 'text',
    'emailstatus' => 'text',
    'token' => 'varchar(35)',
    'language' => 'varchar(25)',
    'blacklisted' => 'varchar(17)',
    'sent' => "varchar(17) DEFAULT 'N'",
    'remindersent' => "varchar(17) DEFAULT 'N'",
    'remindercount' => 'integer DEFAULT 0',
    'completed' => "varchar(17) DEFAULT 'N'",
    'usesleft' => 'integer DEFAULT 1',
    'validfrom' => 'datetime',
    'validuntil' => 'datetime',
    'mpid' => 'integer'
    );
    foreach ($aAttributeFields as $sAttributeField)
    {
        $fields[$sAttributeField]='string';
    }
    try{
        createTable("{{tokens_".intval($iSurveyID)."}}", $fields);
        return true;
    } catch(Exception $e) {
        return false;
    }

}
