<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey (tm)
* Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*
*/

/**
* surveypermission
*
* @package LimeSurvey
* @copyright 2011
* @version $Id$
* @access public
*/
class surveypermission extends Survey_Common_Controller {


    /**
    * surveypermission::__construct()
    * Constructor
    * @return
    */
    function __construct()
    {
        parent::__construct();
    }

    /**
    * surveypermission::view()
    * Load survey security screen.
    * @param mixed $surveyid
    * @return
    */
    function view($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'surveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');

        if(bHasSurveyPermission($surveyid,'survey','read'))
        {
            $this->load->model('survey_permissions_model');
            $aBaseSurveyPermissions=$this->survey_permissions_model->aGetBaseSurveyPermissions();

            //$js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
            self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
            self::_js_admin_includes(base_url().'scripts/admin/surveysecurity.js');
            //$js_admin_includes[]='scripts/surveysecurity.js';

            $query2 = "SELECT p.sid, p.uid, u.users_name, u.full_name FROM ".$this->db->dbprefix."survey_permissions AS p INNER JOIN ".$this->db->dbprefix."users  AS u ON p.uid = u.uid
            WHERE p.sid = {$surveyid} AND u.uid != ".$this->session->userdata('loginID') ."
            GROUP BY p.sid, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
            $result2 = db_execute_assoc($query2); //Checked

            $surveysecurity ="<div class='header ui-widget-header'>".$clang->gT("Survey permissions")."</div>\n"
            . "<table class='surveysecurity'><thead>"
            . "<tr>\n"
            . "<th>".$clang->gT("Action")."</th>\n"
            . "<th>".$clang->gT("Username")."</th>\n"
            . "<th>".$clang->gT("User Group")."</th>\n"
            . "<th>".$clang->gT("Full name")."</th>\n";
            foreach ($aBaseSurveyPermissions as $sPermission=>$aSubPermissions )
            {
                $surveysecurity.="<th align=\"center\"><img src=\"{$imageurl}/{$aSubPermissions['img']}_30.png\" alt=\"<span style='font-weight:bold;'>".$aSubPermissions['title']."</span><br />".$aSubPermissions['description']."\" /></th>\n";
            }
            $surveysecurity .= "</tr></thead>\n";

            // Foot first

            if ($this->config->item('usercontrolSameGroupPolicy') == true)
            {
                $authorizedGroupsList=getusergrouplist(NULL,'simplegidarray');
            }

            $surveysecurity .= "<tbody>\n";
            if($result2->num_rows() > 0)
            {

                //	output users
                $row = 0;

                foreach ($result2->result_array() as $PermissionRow)
                {

                    $query3 = "SELECT a.ugid FROM ".$this->db->dbprefix."user_in_groups AS a RIGHT OUTER JOIN ".$this->db->dbprefix."users AS b ON a.uid = b.uid WHERE b.uid = ".$PermissionRow['uid'];
                    $result3 = db_execute_assoc($query3); //Checked
                    foreach ($result3->result_array() as $resul3row)
                    {
                        if ($this->config->item('usercontrolSameGroupPolicy') == false ||
                        in_array($resul3row['ugid'],$authorizedGroupsList))
                        {
                            $group_ids[] = $resul3row['ugid'];
                        }
                    }

                    if(isset($group_ids) && $group_ids[0] != NULL)
                    {
                        $group_ids_query = implode(" OR ugid=", $group_ids);
                        unset($group_ids);

                        $query4 = "SELECT name FROM ".$this->db->dbprefix."user_groups WHERE ugid = ".$group_ids_query;
                        $result4 = db_execute_assoc($query4); //Checked

                        foreach ($result4->result_array() as $resul4row)
                        {
                            $group_names[] = $resul4row['name'];
                        }
                        if(count($group_names) > 0)
                            $group_names_query = implode(", ", $group_names);
                    }
                    //                  else {break;} //TODO Commented by lemeur
                    $surveysecurity .= "<tr>\n";

                    $surveysecurity .= "<td>\n";
                    $surveysecurity .= "<form style='display:inline;' method='post' action='".site_url('admin/surveypermission/set/'.$surveyid)."'>"
                    ."<input type='image' src='{$imageurl}/token_edit.png' title='".$clang->gT("Edit permissions")."' />"
                    ."<input type='hidden' name='action' value='setsurveysecurity' />"
                    ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                    ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                    ."</form>\n";
                    $surveysecurity .= "<form style='display:inline;' method='post' action='".site_url('admin/surveypermission/delete/'.$surveyid)."'>"
                    ."<input type='image' src='{$imageurl}/token_delete.png' title='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                    ."<input type='hidden' name='action' value='delsurveysecurity' />"
                    ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                    ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                    ."</form>";


                    $surveysecurity .= "</td>\n";
                    $surveysecurity .= "<td>{$PermissionRow['users_name']}</td>\n"
                    . "<td>";

                    if(isset($group_names) > 0)
                    {
                        $surveysecurity .= $group_names_query;
                    }
                    else
                    {
                        $surveysecurity .= "---";
                    }
                    unset($group_names);

                    $surveysecurity .= "</td>\n"
                    . "<td>\n{$PermissionRow['full_name']}</td>\n";

                    //Now show the permissions
                    foreach ($aBaseSurveyPermissions as $sPKey=>$aPDetails) {
                        unset($aPDetails['img']);
                        unset($aPDetails['description']);
                        unset($aPDetails['title']);
                        $iCount=0;
                        $iPermissionCount=0;
                        foreach ($aPDetails as $sPDetailKey=>$sPDetailValue)
                        {
                            if ($sPDetailValue && bHasSurveyPermission($surveyid,$sPKey,$sPDetailKey,$PermissionRow['uid']) && !($sPKey=='survey' && $sPDetailKey=='read')) $iCount++;
                            if ($sPDetailValue) $iPermissionCount++;
                        }
                        if ($sPKey=='survey')  $iPermissionCount--;
                        if ($iCount==$iPermissionCount) {
                            $insert = "<div class=\"ui-icon ui-icon-check\">&nbsp;</div>";
                        }
                        elseif ($iCount>0){
                            $insert = "<div class=\"ui-icon ui-icon-check mixed\">&nbsp;</div>";
                        }
                        else
                        {
                            $insert = "<div>&nbsp;</div>";
                        }
                        $surveysecurity .= "<td align=\"center\">\n$insert\n</td>\n";
                    }

                    $surveysecurity .= "</tr>\n";
                    $row++;
                }
            } else {
                $surveysecurity .= "<tr><td colspan='18'></td></tr>"; //fix error on empty table
            }

            $surveysecurity .= "</tbody>\n"
            . "</table>\n"
            . "<form class='form44' action='".site_url('admin/surveypermission/adduser/'.$surveyid)."' method='post'><ul>\n"
            . "<li><label for='uidselect'>".$clang->gT("User").": </label><select id='uidselect' name='uid'>\n"
            . sGetSurveyUserlist(false,false,$surveyid)
            . "</select>\n"
            . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User")."'  onclick=\"if (document.getElementById('uidselect').value == -1) { alert('".$clang->gT("Please select a user first","js")."'); return false;}\"/>"
            . "<input type='hidden' name='action' value='addsurveysecurity' />"
            . "</li></ul></form>\n"
            . "<form class='form44' action='".site_url('admin/surveypermission/addusergroup/'.$surveyid)."' method='post'><ul><li>\n"
            . "<label for='ugidselect'>".$clang->gT("Groups").": </label><select id='ugidselect' name='ugid'>\n"
            . getsurveyusergrouplist('htmloptions',$surveyid)
            . "</select>\n"
            . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User Group")."' onclick=\"if (document.getElementById('ugidselect').value == -1) { alert('".$clang->gT("Please select a user group first","js")."'); return false;}\" />"
            . "<input type='hidden' name='action' value='addusergroupsurveysecurity' />\n"
            . "</li></ul></form>";

            $data['display'] = $surveysecurity;
            $this->load->view('survey_view',$data);
        }
        else
        {
            access_denied();

        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
    * surveypermission::addusergroup()
    * Function responsible to add usergroup.
    * @param mixed $surveyid
    * @return
    */
    function addusergroup($surveyid)
    {
        $surveyid = sanitize_int($surveyid);

        $action = $this->input->post('action');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'addsurveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');
        $dbprefix = $this->db->dbprefix;
        $postusergroupid = $this->input->post('gid');


        if($action == "addusergroupsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Add user group")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\" >\n";

            $query = "SELECT sid, owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid} AND owner_id = ".$this->session->userdata('loginID');
            $result = db_execute_assoc($query); //Checked
            if( ($result->num_rows() > 0 && in_array($postusergroupid,getsurveyusergrouplist('simpleugidarray'))) || $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {
                if($postusergroupid > 0){
                    $query2 = "SELECT b.uid FROM (SELECT uid FROM ".$this->db->dbprefix."survey_permissions WHERE sid = {$surveyid}) AS c RIGHT JOIN ".$this->db->dbprefix."user_in_groups AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$postusergroupid}";
                    $result2 = db_execute_assoc($query2); //Checked
                    if($result2->num_rows() > 0)
                    {
                        while ($row2 = $result2->FetchRow())
                        {
                            $uid_arr[] = $row2['uid'];
                            $isrquery = "INSERT INTO {$dbprefix}survey_permissions (sid,uid,permission,read_p) VALUES ({$surveyid}, {$row2['uid']},'survey',1) ";
                            $isrresult = db_execute_assoc($isrquery); //Checked
                            if (!$isrresult) break;
                        }

                        if($isrresult)
                        {
                            $addsummary .= "<div class=\"successheader\">".$clang->gT("User Group added.")."</div>\n";
                            $_SESSION['uids'] = $uid_arr;
                            $addsummary .= "<br /><form method='post' action='".site_url('admin/surveypermission/set/'.$surveyid)."'>"
                            ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
                            ."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
                            ."<input type='hidden' name='ugid' value='{$postusergroupid}' />"
                            ."</form>\n";
                        }
                        else
                        {
                            // Error while adding user to the database
                            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                        }
                    }
                    else
                    {
                        // no user to add
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                    . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                access_denied();
            }
            $addsummary .= "</div>\n";
        }
        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


    }


    /**
    * surveypermission::adduser()
    * Function responsible to add user.
    * @param mixed $surveyid
    * @return
    */
    function adduser($surveyid)
    {

        $surveyid = sanitize_int($surveyid);

        $action = $this->input->post('action');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'addsurveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');
        $dbprefix = $this->db->dbprefix;
        $postuserid = $this->input->post('uid');

        if($action == "addsurveysecurity")
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Add User")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\">\n";

            $query = "SELECT sid, owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid} AND owner_id = ".$this->session->userdata('loginID')." AND owner_id != ".$postuserid;
            $result = db_execute_assoc($query); //Checked
            if( ($result->num_rows() > 0 && in_array($postuserid,getuserlist('onlyuidarray'))) ||
            $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {

                if($postuserid > 0){

                    $isrquery = "INSERT INTO {$dbprefix}survey_permissions (sid,uid,permission,read_p) VALUES ( {$surveyid}, {$postuserid}, 'survey', 1)";
                    $isrresult = db_execute_assoc($isrquery); //Checked

                    if($isrresult)
                    {

                        $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                        $addsummary .= "<br /><form method='post' action='".site_url('admin/surveypermission/set/'.$surveyid)."'>"
                        ."<input type='submit' value='".$clang->gT("Set survey permissions")."' />"
                        ."<input type='hidden' name='action' value='setsurveysecurity' />"
                        ."<input type='hidden' name='uid' value='{$postuserid}' />"
                        ."</form>\n";
                    }
                    else
                    {
                        // Username already exists.
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                        . "<br />" . $clang->gT("Username already exists.")."<br />\n";
                        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                    . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                access_denied();
            }

            $addsummary .= "</div>\n";

            $data['display'] = $addsummary;
            $this->load->view('survey_view',$data);
        }
        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * surveypermission::set()
    * Function responsible to set permissions to a user/usergroup.
    * @param mixed $surveyid
    * @return
    */
    function set($surveyid)
    {
        $surveyid = sanitize_int($surveyid);

        $action = $this->input->post('action');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'addsurveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');
        $dbprefix = $this->db->dbprefix;
        $postuserid = $this->input->post('uid');
        $postusergroupid = $this->input->post('gid');

        if($action == "setsurveysecurity" || $action == "setusergroupsurveysecurity")
        {
            $query = "SELECT sid, owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid} AND owner_id = ".$this->session->userdata('loginID');
            if ($action == "setsurveysecurity")
            {
                $query.=  " AND owner_id != ".$postuserid;
            }
            $result = db_execute_assoc($query); //Checked
            if($result->num_rows() > 0 || $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {
                //$js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
                //$js_admin_includes[]='scripts/surveysecurity.js';
                self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
                self::_js_admin_includes(base_url().'scripts/admin/surveysecurity.js');
                if ($action == "setsurveysecurity")
                {
                    $query = "select users_name from ".$this->db->dbprefix."users where uid={$postuserid}";
                    $res = db_execute_assoc($query);
                    $resrow = $res->row_array();
                    $sUsername=$resrow['users_name']; //$connect->GetOne("select users_name from ".$this->db->dbprefix."users where uid={$postuserid}");
                    $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for user %s"),"<span style='font-style:italic'>".$sUsername."</span>")."</div>";
                }
                else
                {
                    $query = "select name from ".$this->db->dbprefix."user_groups where ugid={$postusergroupid}";
                    $res = db_execute_assoc($query);
                    $resrow = $res->row_array();
                    $sUsergroupName=$resrow['name']; //$connect->GetOne("select name from ".$this->db->dbprefix."user_groups where ugid={$postusergroupid}");
                    $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for group %s"),"<span style='font-style:italic'>".$sUsergroupName."</span>")."</div>";
                }
                $usersummary .= "<br /><form action='".site_url('admin/surveypermission/surveyright/'.$surveyid)."' method='post'>\n"
                . "<table style='margin:0 auto;' border='0' class='usersurveypermissions'><thead>\n";

                $usersummary .= ""
                . "<tr><th></th><th align='center'>".$clang->gT("Permission")."</th>\n"
                . "<th align='center'><input type='button' id='btnToggleAdvanced' value='&gt;&gt;' /></th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Create")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("View/read")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Update")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Delete")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Import")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Export")."</th>\n"
                . "</tr></thead>\n";

                //content
                $this->load->model('survey_permissions_model');
                $aBasePermissions=$this->survey_permissions_model->aGetBaseSurveyPermissions();

                $oddcolumn=false;
                foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions)
                {
                    $oddcolumn=!$oddcolumn;
                    $usersummary .= "<tr><td align='center'><img src='{$imageurl}/{$aCRUDPermissions['img']}_30.png' /></td>";
                    $usersummary .= "<td align='right'>{$aCRUDPermissions['title']}</td>";
                    $usersummary .= "<td  align='center'><input type=\"checkbox\"  class=\"markrow\" name='all_{$sPermissionKey}' /></td>";
                    foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
                    {
                        if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;
                        $usersummary .= "<td class='extended' align='center'>";

                        if ($CRUDValue)
                        {
                            if (!($sPermissionKey=='survey' && $sCRUDKey=='read'))
                            {
                                $usersummary .= "<input type=\"checkbox\"  class=\"checkboxbtn\" name='perm_{$sPermissionKey}_{$sCRUDKey}' ";
                                if($action=='setsurveysecurity' && bHasSurveyPermission( $surveyid,$sPermissionKey,$sCRUDKey,$postuserid)) {
                                    $usersummary .= ' checked="checked" ';
                                }
                                $usersummary .=" />";
                            }
                        }
                        $usersummary .= "</td>";
                    }
                    $usersummary .= "</tr>";
                }

                $usersummary .= "\n</table>"
                ."<p><input type='submit' value='".$clang->gT("Save Now")."' />"
                ."<input type='hidden' name='perm_survey_read' value='1' />"
                ."<input type='hidden' name='action' value='surveyrights' />";

                if ($action=='setsurveysecurity')
                {
                    $usersummary .="<input type='hidden' name='uid' value='{$postuserid}' />";
                }
                else
                {
                    $usersummary .="<input type='hidden' name='ugid' value='{$postusergroupid}' />";
                }
                $usersummary .= "</form>\n";

                $data['display'] = $usersummary;
                $this->load->view('survey_view',$data);
            }
            else
            {
                include("access_denied.php");
            }
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
    * surveypermission::delete()
    * Function responsible to delete a user/usergroup.
    * @param mixed $surveyid
    * @return
    */
    function delete($surveyid)
    {

        $surveyid = sanitize_int($surveyid);

        $action = $this->input->post('action');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'addsurveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');
        $dbprefix = $this->db->dbprefix;
        $postuserid = $this->input->post('uid');
        $postusergroupid = $this->input->post('gid');
        $_POST = $this->input->post();

        if($action == "delsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Deleting User")."</div>\n";
            $addsummary .= "<div class=\"messagebox\">\n";

            $query = "SELECT sid, owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid} AND owner_id = ".$this->session->userdata('loginID')." AND owner_id != ".$postuserid;
            $result = db_execute_assoc($query); //Checked
            if($result->num_rows() > 0 || $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {
                if (isset($postuserid))
                {
                    $dquery="DELETE FROM ".$this->db->dbprefix."survey_permissions WHERE uid={$postuserid} AND sid={$surveyid}";	//	added by Dennis
                    $dresult=db_execute_assoc($dquery); //Checked

                    $addsummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string($_POST['user'])."<br /><br />\n";
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user. User was not supplied.")."</div>\n";
                }
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
            else
            {
                access_denied();
            }
            $addsummary .= "</div>\n";

            $data['display'] = $addsummary;
            $this->load->view('survey_view',$data);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
    * surveypermission::surveyright()
    * Function responsible to process setting of permission of a user/usergroup.
    * @param mixed $surveyid
    * @return
    */
    function surveyright($surveyid)
    {
        $surveyid = sanitize_int($surveyid);

        $action = $this->input->post('action');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);


        self::_getAdminHeader();
        self::_showadminmenu($surveyid);
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,'addsurveysecurity');

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $imageurl = $this->config->item('imageurl');
        $dbprefix = $this->db->dbprefix;
        $postuserid = $this->input->post('uid');
        $postusergroupid = $this->input->post('gid');
        $_POST = $this->input->post();

        if ($action == "surveyrights")
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Edit survey permissions")."</div>\n";
            $addsummary .= "<div class='messagebox ui-corner-all'>\n";

            if(isset($postuserid)){
                $query = "SELECT sid, owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid}";
                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
                {
                    $query.=" AND owner_id != {$postuserid} AND owner_id = ".$this->session->userdata('loginID');
                }
            }
            else{
                $sQuery = "SELECT owner_id FROM ".$this->db->dbprefix."surveys WHERE sid = {$surveyid}";
                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
                {
                    $query.=" AND owner_id = ".$this->session->userdata('loginID');
                }
                $res= db_execute_assoc($sQuery);
                $resrow=$res->row_array();
                $iOwnerID=$resrow['owner_id']; //$connect->GetOne($sQuery);
            }
            $this->load->model('survey_permissions_model');

            $aBaseSurveyPermissions=$this->survey_permissions_model->aGetBaseSurveyPermissions();
            $aPermissions=array();
            foreach ($aBaseSurveyPermissions as $sPermissionKey=>$aCRUDPermissions)
            {
                foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
                {
                    if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;

                    if ($CRUDValue)
                    {
                        if(isset($_POST["perm_{$sPermissionKey}_{$sCRUDKey}"])){
                            $aPermissions[$sPermissionKey][$sCRUDKey]=1;
                        }
                        else
                        {
                            $aPermissions[$sPermissionKey][$sCRUDKey]=0;
                        }
                    }
                }
            }
            if (isset($postusergroupid) && $postusergroupid>0)
            {
                $sQuery = "SELECT uid from ".$this->db->dbprefix."user_in_groups where ugid = {$postusergroupid} and uid<>{$_SESSION['loginID']} AND uid<>{$iOwnerID}";
                $oResult = db_execute_assoc($sQuery); //Checked
                if($oResult->num_rows() > 0)
                {
                    foreach ($oResult->result_array() as $aRow)
                    {
                        $this->survey_permissions_model->setSurveyPermissions($aRow['uid'], $surveyid, $aPermissions);
                    }
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions for all users in this group were successfully updated.")."</div>\n";
                }
            }
            else
            {
                $this->load->helper('admin/import');
                $this->load->model('survey_permissions_model');
                if($this->survey_permissions_model->setSurveyPermissions($postuserid, $surveyid, $aPermissions))
                {
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions were successfully updated.")."</div>\n";
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update survey permissions!")."</div>\n";
                }

            }
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".site_url('admin/surveypermission/view/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            $addsummary .= "</div>\n";
            $data['display'] = $addsummary;
            $this->load->view('survey_view',$data);
        }

        self::_loadEndScripts();


        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

}
