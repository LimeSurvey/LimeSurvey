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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
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
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang=$CI->limesurvey_lang;
    $CI->load->dbforge();
    $CI->dbforge->add_field("tid int(11) NOT NULL AUTO_INCREMENT");
    $aFields = array(
        'participant_id' => array('type' => 'VARCHAR', 'constraint' => 50),
        'firstname' => array('type' => 'VARCHAR', 'constraint' => 40),
        'lastname' => array('type' => 'VARCHAR', 'constraint' => 40),
        'email' => array('type' => 'TEXT'),
        'emailstatus' => array('type' => 'TEXT'),
        'token' => array('type' => 'VARCHAR', 'constraint' => 36),
        'language' => array('type' => 'VARCHAR', 'constraint' => 25),
        'blacklisted' => array('type' => 'CHAR', 'constraint' => 1),
        'sent' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
        'remindersent' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
        'remindercount' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
        'completed' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
        'usesleft' => array('type' => 'INT', 'constraint' => 11, 'default' => 1),
        'validfrom' => array('type' => 'DATETIME'),
        'validuntil' => array('type' => 'DATETIME'),
        'mpid' => array('type' => 'INT', 'constraint' => 11)
    );
    $CI->dbforge->add_field($aFields);
    $CI->dbforge->add_key('tid', TRUE);
    $CI->dbforge->add_key("token");
    return $CI->dbforge->create_table("tokens_{$iSurveyID}");

}
