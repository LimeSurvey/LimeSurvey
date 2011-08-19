<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: globalsettings.php 10760 2011-08-17 19:42:04Z dionet $
 */

/**
 * CheckIntegrity Controller
 *
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class CheckIntegrity extends Admin_Controller {

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
        {
            if($this->input->post("action"))
            {
                self::_correctissues();
            }
            else
            {
                self::_display();
            }
        }
    }

    function _display()
    {
        /****** Plainly delete survey permissions if the survey or user does not exist ***/
        $this->db->query("delete FROM {$this->db->dbprefix('survey_permissions')} where sid not in (select sid from {$this->db->dbprefix('surveys')})");
        $this->db->query("delete FROM {$this->db->dbprefix('survey_permissions')} where uid not in (select uid from {$this->db->dbprefix('users')})");

        self::_getAdminHeader();
        self::_showadminmenu();
        $this->load->view('admin/checkintegrity_view', $data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
}