<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * $Id$
 *
 */

/**
 * Usergroups
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class Usergroups extends Admin_Controller {


    /**
     * Usergroups::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * Usergroups::mail()
     * Function responsible to send an e-mail to a user group.
     * @param mixed $ugid
     * @return
     */
    function mail($ugid)
    {

		$ugid = sanitize_int($ugid);
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
    	self::_js_admin_includes(base_url().'scripts/admin/users.js');
        self::_getAdminHeader();
        self::_showadminmenu(false);
        $action = $this->input->post("action");

        self::_usergroupbar($ugid);

        if ($action == "mailsendusergroup")
        {
            $usersummary = "<div class=\"header\">".$clang->gT("Mail to all Members")."</div>\n";
            $usersummary .= "<div class=\"messagebox\">\n";
            $_POST = $this->input->post();

            // user must be in user group
            // or superadmin
            $query = "SELECT uid FROM ".$this->db->dbprefix."user_in_groups WHERE ugid = {$ugid} AND uid = ".$this->session->userdata('loginID');
            $result = db_execute_assoc($query); //Checked

            if($result->num_rows() > 0 || $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {

                $eguquery = "SELECT * FROM ".$this->db->dbprefix."user_in_groups AS a INNER JOIN ".$this->db->dbprefix."users AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " AND b.uid != ".$this->session->userdata('loginID')." ORDER BY b.users_name";
                $eguresult = db_execute_assoc($eguquery); //Checked
                $addressee = '';
                $to = '';
                foreach ($eguresult->result_array() as $egurow)
                {
                    $to .= $egurow['users_name']. ' <'.$egurow['email'].'>'. '; ' ;
                    $addressee .= $egurow['users_name'].', ';
                }
                $to = substr("$to", 0, -2);
                $addressee = substr("$addressee", 0, -2);

                $from_user = "SELECT email, users_name, full_name FROM ".$this->db->dbprefix."users WHERE uid = " .$this->session->userdata('loginID');
                $from_user_result = db_execute_assoc($from_user); //Checked
                $from_user_row = $from_user_result->row_array();
                if ($from_user_row['full_name'])
                {
                    $from = $from_user_row['full_name'].' <'.$from_user_row['email'].'> ';
                }
                else
                {
                    $from = $from_user_row['users_name'].' <'.$from_user_row['email'].'> ';
                }


                $body = $_POST['body'];
                $subject = $_POST['subject'];

                if(isset($_POST['copymail']) && $_POST['copymail'] == 1)
                {
                    if ($to == "")
                    $to = $from;
                    else
                    $to .= ", " . $from;
                }

                $body = str_replace("\n.", "\n..", $body);
                $body = wordwrap($body, 70);


                //echo $body . '-'.$subject .'-'.'<pre>'.htmlspecialchars($to).'</pre>'.'-'.$from;
                if (SendEmailMessage( $body, $subject, $to, $from,''))
                {
                    $link = site_url("admin/usergroups/view/".$ugid);
                    $usersummary = "<div class=\"messagebox\">\n";
                    $usersummary .= "<div class=\"successheader\">".$clang->gT("Message(s) sent successfully!")."</div>\n"
                    . "<br />".$clang->gT("To:")."". $addressee."<br />\n"
                    . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
                else
                {
                    $link = site_url("admin/usergroups/mail/".$ugid);
                    $usersummary = "<div class=\"messagebox\">\n";
                    $usersummary .= "<div class=\"warningheader\">".sprintf($clang->gT("Email to %s failed. Error Message:"),$to)." ".$maildebug."</div>";
                    if ($debug>0)
                    {
                        $usersummary .= "<br /><pre>Subject : $subject<br /><br />".htmlspecialchars($maildebugbody)."<br /></pre>";
                    }

                    $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                //include("access_denied.php");
            }
            $usersummary .= "</div>\n";

            $displaydata['display'] = $usersummary;
            //$data['display'] = $editsurvey;
            $this->load->view('survey_view',$displaydata);
        }
        else
        {
            $query = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM ".$this->db->dbprefix."user_groups AS a LEFT JOIN ".$this->db->dbprefix."user_in_groups AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = ".$this->session->userdata('loginID')." ORDER BY name";
            $result = db_execute_assoc($query); //Checked
            $crow = $result->row_array();

            $data['clang'] = $clang;
            $this->load->view("admin/usergroup/mailUserGroup_view",$data);
        }

        self::_loadEndScripts();


	    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
     * Usergroups::delete()
     * Function responsible to delete a user group.
     * @return
     */
    function delete()
    {

        $clang = $this->limesurvey_lang;
        $this->load->helper('database');

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
    	self::_js_admin_includes(base_url().'scripts/admin/users.js');
        self::_getAdminHeader();
        self::_showadminmenu(false);
        $action = $this->input->post("action");
        $ugid = $this->input->post("ugid");
        self::_usergroupbar($ugid);

        if ($action == "delusergroup")
        {
            $usersummary = "<div class=\"header\">".$clang->gT("Deleting User Group")."...</div>\n";
            $usersummary .= "<div class=\"messagebox\">\n";

            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
            {

                if(!empty($ugid) && ($ugid > -1))
                {

                    $query = "SELECT ugid, name, owner_id FROM ".$this->db->dbprefix."user_groups WHERE ugid = {$ugid} AND owner_id = ".$this->session->userdata('loginID');
                    $result = db_select_limit_assoc($query, 1);
                    if($result->num_rows() > 0)
                    {
                        $row = $result->row_array();

                        $remquery = "DELETE FROM ".$this->db->dbprefix."user_groups WHERE ugid = {$ugid} AND owner_id = ".$this->session->userdata('loginID');
                        if(db_execute_assoc($remquery)) //Checked)
                        {
                            $usersummary .= "<br />".$clang->gT("Group Name").": {$row['name']}<br /><br />\n";
                            $usersummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                        }
                        else
                        {
                            $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group.")."</div>\n";
                        }
                        $link = site_url("admin/usergroups/view");
                        $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                    else
                    {
                        //include("access_denied.php");
                    }
                }
                else
                {
                    $link = site_url("admin/usergroups/view");
                    $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group. No group selected.")."</div>\n";
                    $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            $usersummary .= "</div>\n";

            $displaydata['display'] = $usersummary;
            //$data['display'] = $editsurvey;
            $this->load->view('survey_view',$displaydata);
        }

        self::_loadEndScripts();


	    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }


    /**
     * Usergroups::add()
     * Load add user group screen.
     * @return
     */
    function add()
    {
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
    	self::_js_admin_includes(base_url().'scripts/admin/users.js');
        self::_getAdminHeader();
        self::_showadminmenu(false);
        $action = $this->input->post("action");
        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
        {

            self::_usergroupbar(false);
            $data['clang'] = $clang;
            if ($action == "usergroupindb")
            {
                $usersummary = "<div class=\"header\">".$clang->gT("Adding User Group")."...</div>\n";
                $usersummary .= "<div class=\"messagebox\">\n";

                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
                {
                    $_POST = $this->input->post();
                    $db_group_name = $_POST['group_name'];
                    $db_group_description = $_POST['group_description'];
                    $html_group_name = htmlspecialchars($_POST['group_name']);
                    $html_group_description = htmlspecialchars($_POST['group_description']);

                    if(isset($db_group_name) && strlen($db_group_name) > 0)
                    {
                        if (strlen($db_group_name) > 21)
                        {
                            $link = site_url("admin/usergroups/add");
                            $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                            . "<br />" . $clang->gT("Group name length more than 20 characters!")."<br />\n"; //need to nupdate translations for this phrase.
                            $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";

                        }
                        else
                        {
                            $ugid = self::_addUserGroupInDB($db_group_name, $db_group_description);
                            if($ugid > 0)
                            {
                                $usersummary .= "<br />".$clang->gT("Group Name").": ".$html_group_name."<br /><br />\n";

                                if(isset($db_group_description) && strlen($db_group_description) > 0)
                                {
                                    $usersummary .= $clang->gT("Description: ").$html_group_description."<br /><br />\n";
                                }
                                $link = site_url("admin/usergroups/view/$ugid");
                                $usersummary .= "<div class=\"successheader\">".$clang->gT("User group successfully added!")."</div>\n";
                                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                            }
                            else
                            {
                                $link = site_url("admin/usergroups/add");
                                $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                                . "<br />" . $clang->gT("Group already exists!")."<br />\n";
                                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                            }
                        }

                    }
                    else
                    {
                        $link = site_url("admin/usergroups/add");
                        $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                        . "<br />" . $clang->gT("Group name was not supplied!")."<br />\n";
                        $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {
                    //include("access_denied.php");
                }
                $usersummary .= "</div>\n";
                $displaydata['display'] = $usersummary;
                //$data['display'] = $editsurvey;
                $this->load->view('survey_view',$displaydata);

            }
            else
            {
                $this->load->view("admin/usergroup/addUserGroup_view",$data);
            }


        }
        self::_loadEndScripts();


	    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }

    /**
     * Usergroups::edit()
     * Load edit user group screen.
     * @param mixed $ugid
     * @return
     */
    function edit($ugid)
    {
    	$ugid = (int) $ugid;
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
    	self::_js_admin_includes(base_url().'scripts/admin/users.js');
        self::_getAdminHeader();
        self::_showadminmenu(false);
        $action = $this->input->post("action");

        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
        {

            self::_usergroupbar($ugid);
            $data['clang'] = $clang;
            if ($action == "editusergroupindb")
            {
                $_POST = $this->input->post();
                if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
                {
                    $ugid = $_POST['ugid'];

                    $db_name = $_POST['name'];
                    $db_description = $_POST['description'];
                    $html_name = html_escape($_POST['name']);
                    $html_description = html_escape($_POST['description']);

            		$usersummary = "<div class=\"messagebox\">\n";

                    if(self::_updateusergroup($db_name, $db_description, $ugid))
                    {
                        $link = site_url("admin/usergroups/view/$ugid");
            			$usersummary .= "<div class=\"successheader\">".$clang->gT("Edit User Group Successfully!")."</div>\n"
                        . "<br />".$clang->gT("Name").": {$html_name}<br />\n"
                        . $clang->gT("Description: ").$html_description."<br />\n"
                        . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                        //. "<br /><a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
                    }
                    else
            		{
            			$link = site_url("admin/usergroups/view");
                        $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update!")."</div>\n"
                        . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                        //. "<br /><a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
                    }
            		$usersummary .= "</div>\n";

                    $displaydata['display'] = $usersummary;
                    //$data['display'] = $editsurvey;
                    $this->load->view('survey_view',$displaydata);

            	}
                else
                {
                    //include("access_denied.php");
                }


            }
            else
            {

                $query = "SELECT * FROM ".$this->db->dbprefix."user_groups WHERE ugid = ".$ugid." AND owner_id = ".$this->session->userdata('loginID');
                $result = db_select_limit_assoc($query, 1);
                $esrow = $result->row_array();
                $data['esrow'] = $esrow;
                $data['ugid'] = $ugid;
                $this->load->view("admin/usergroup/editUserGroup_view",$data);
            }


        }
        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }



    /**
     * Usergroups::view()
     * Load viewing of a user group screen.
     * @param bool $ugid
     * @return
     */
    function view($ugid=false)
    {
    	if($ugid!=false) $ugid = (int) $ugid;
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');

        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
    	self::_js_admin_includes(base_url().'scripts/admin/users.js');
        self::_getAdminHeader();
        self::_showadminmenu(false);

        self::_usergroupbar($ugid);

        if ( $this->session->userdata('loginID'))
        {

            if($ugid)
            {

                $ugid = sanitize_int($ugid);

                $query = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM ".$this->db->dbprefix."user_groups AS a LEFT JOIN ".$this->db->dbprefix."user_in_groups AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = ".$this->session->userdata('loginID')." ORDER BY name";
                $result = db_execute_assoc($query); //Checked
                $crow = $result->row_array();

                if($result->num_rows() > 0)
                {

                    if(!empty($crow['description']))
                    {
                        $usergroupsummary = "<table width='100%' border='0'>\n"
                        . "<tr><td align='justify' colspan='2' height='4'>"
                        . "<font size='2' ><strong>".$clang->gT("Description: ")."</strong>"
                        . "{$crow['description']}</font></td></tr>\n"
                        . "</table>";
                    }


                    $eguquery = "SELECT * FROM ".$this->db->dbprefix."user_in_groups AS a INNER JOIN ".$this->db->dbprefix."users AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
                    $eguresult = db_execute_assoc($eguquery); //Checked
                    $usergroupsummary = (isset($usergroupsummary) ? $usergroupsummary : '') . "<table class='users'>\n"
                    . "<thead><tr>\n"
                    . "<th>".$clang->gT("Action")."</th>\n"
                    . "<th>".$clang->gT("Username")."</th>\n"
                    . "<th>".$clang->gT("Email")."</th>\n"
                    . "</tr></thead><tbody>\n";

                    $query2 = "SELECT ugid FROM ".$this->db->dbprefix."user_groups WHERE ugid = ".$ugid." AND owner_id = ".$this->session->userdata('loginID');
                    $result2 = db_select_limit_assoc($query2, 1);
                    $row2 = $result2->row_array();

                    $row = 1;
                    $usergroupentries='';
                    foreach ($eguresult->result_array() as $egurow)
                    {
                        if (!isset($bgcc)) {$bgcc="evenrow";}
                        else
                        {
                            if ($bgcc == "evenrow") {$bgcc = "oddrow";}
                            else {$bgcc = "evenrow";}
                        }

                        if($egurow['uid'] == $crow['owner_id'])
                        {
                            $usergroupowner = "<tr class='$bgcc'>\n"
                            . "<td align='center'>&nbsp;</td>\n"
                            . "<td align='center'><strong>{$egurow['users_name']}</strong></td>\n"
                            . "<td align='center'><strong>{$egurow['email']}</strong></td>\n"
                            . "</tr>";
                            continue;
                        }

                        //	output users

                        $usergroupentries .= "<tr class='$bgcc'>\n"
                        . "<td align='center'>\n";

                        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
                        {
                            $usergroupentries .= "<form method='post' action='scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>"
                            ." <input type='image' src='".$this->config->item('imageurl')."/token_delete.png' alt='".$clang->gT("Delete this user from group")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                            ." <input type='hidden' name='user' value='{$egurow['users_name']}' />"
                            ." <input name='uid' type='hidden' value='{$egurow['uid']}' />"
                            ." <input name='ugid' type='hidden' value='{$ugid}' />";
                        }
                        $usergroupentries .= "</form>"
                        . "</td>\n";
                        $usergroupentries .= "<td align='center'>{$egurow['users_name']}</td>\n"
                        . "<td align='center'>{$egurow['email']}</td>\n"
                        . "</tr>\n";
                        $row++;
                    }
                    $usergroupsummary .= $usergroupowner;
                    if (isset($usergroupentries)) {$usergroupsummary .= $usergroupentries;};
                    $usergroupsummary .= '</tbody></table>';

                    if(isset($row2['ugid']))
                    {
                        $usergroupsummary .= "<form action='scriptname?ugid={$ugid}' method='post'>\n"
                        . "<table class='users'><tbody><tr><td>&nbsp;</td>\n"
                        . "<td>&nbsp;</td>"
                        . "<td align='center'><select name='uid'>\n"
                        . getgroupuserlist($ugid,'optionlist')
                        . "</select>\n"
                        . "<input type='submit' value='".$clang->gT("Add User")."' />\n"
                        . "<input type='hidden' name='action' value='addusertogroup' /></td>\n"
                        . "</tr></tbody></table>\n"
                        . "</form>\n";
                    }

                    $displaydata['display'] = $usergroupsummary;
                    //$data['display'] = $editsurvey;
                    $this->load->view('survey_view',$displaydata);
                }
                else
                {
                    //include("access_denied.php");
                }
            }
        }
        else
        {
            //include("access_denied.php");
        }

        self::_loadEndScripts();


	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));


    }



    /**
     * Usergroups::_usergroupbar()
     * Load menu bar of user group controller.
     * @param bool $ugid
     * @return
     */
    function _usergroupbar($ugid=false)
    {
        $this->load->helper('database');
        if($ugid)
        {
            $grpquery = "SELECT gp.* FROM ".$this->db->dbprefix."user_groups AS gp, ".$this->db->dbprefix."user_in_groups AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = $ugid AND gu.uid=".$this->session->userdata('loginID');
            $grpresult = db_execute_assoc($grpquery);//Checked
            $grpresultcount = $grpresult->num_rows();
            if ($grpresultcount>0)
            {
                $grow = array_map('htmlspecialchars', $grpresult->row_array());
            }

            $data['grow'] = $grow;
            $data['grpresultcount'] = $grpresultcount;

        }

        $data['ugid'] = $ugid;


        $this->load->view('admin/usergroup/usergroupbar_view',$data);
    }

    /**
     * Usergroups::_updateusergroup()
     * Function responsible to update a user group.
     * @param mixed $name
     * @param mixed $description
     * @param mixed $ugid
     * @return
     */
    function _updateusergroup($name, $description, $ugid)
    {
        $this->load->helper('database');
        $uquery = "UPDATE ".$this->db->dbprefix."user_groups SET name = '$name', description = '$description' WHERE ugid =$ugid";
        // TODO
        return db_execute_assoc($uquery);  //or safe_die($connect->ErrorMsg()) ; //Checked)
    }

    /**
     * Usergroups::_refreshtemplates()
     * Function to refresh templates.
     * @return
     */
    function _refreshtemplates() {
        $this->load->helper('database');
        $template_a = gettemplatelist();
    	foreach ($template_a as $tp=>$fullpath) {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
            $query = "SELECT * FROM ".$this->db->dbprefix."templates WHERE folder LIKE '".$tp."'";
            $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg()); //Checked

            if ($result->num_rows() == 0) {
                //$query2 = "INSERT INTO ".$this->db->dbprefix."templates (".db_quote_id('folder').",".db_quote_id('creator').") VALUES ('".$tp."', ".$_SESSION['loginID'].')' ;
                $data = array(
                        'folder' => $tp,
                        'creator' => $this->session->userdata('loginID')


                );

                $this->load->model('templates_model');
                $this->templates_model->insertRecords($data);

                //db_execute_assoc($query2); // or safe_die($connect->ErrorMsg()); //Checked
            }
        }
        return true;
    }

    // adds Usergroups in Database by Moses
    /**
     * Usergroups::_addUserGroupInDB()
     * Function that add a user group in database.
     * @param mixed $group_name
     * @param mixed $group_description
     * @return
     */
    function _addUserGroupInDB($group_name, $group_description) {
        $this->load->helper('database');
        //$iquery = "INSERT INTO ".$this->db->dbprefix."user_groups (name, description, owner_id) VALUES('{$group_name}', '{$group_description}', '{$_SESSION['loginID']}')";
        $data = array(
                'name' => $group_name,
                'description' => $group_description,
                'owner_id' => $this->session->userdata('loginID')

        );
        $this->load->model('user_groups_model');


        if($this->user_groups_model->insertRecords($data)) { //Checked
            $id = $this->db->insert_id(); //$connect->Insert_Id(db_table_name_nq('user_groups'),'ugid');
            if($id > 0) {
                $iquery = "INSERT INTO ".$this->db->dbprefix."user_in_groups VALUES($id, '".$this->session->userdata('loginID')."')";
                db_execute_assoc($iquery ); // or safe_die($connect->ErrorMsg()); //Checked
            }
            return $id;
        } else {
            return -1;
        }
    }


}