<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Usergroups
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: usergroups.php 11128 2011-10-08 22:23:24Z dionet $
 * @access public
 */

class Usergroups extends CAction {
	
	private $yii;
	private $controller;
	
    public function run()
    {
    	$actions = array_keys($_GET);
    	$_GET['method'] = $action = (!empty($actions[0])) ? $actions[0] : '';
    	
    	$this->yii = Yii::app();
    	$this->controller = $this->getController();
    	
    	if(!empty($action))
    	{
    		$this->$action($_GET[$action]);
    	}
    	else
    	{
    		$this->view();
    	}
    }
    function _post($d) {
    		if (isset($_POST[$d])) {
    			return $_POST[$d];
    		}else{
    			return FALSE;
    		}
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
        $clang = Yii::app()->lang;


        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
    	$this->getController()->_js_admin_includes(Yii::app()->baseUrl.'scripts/admin/users.js');
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu(false);
        $action = CHttpRequest::getPost("action");
        $this->_usergroupbar($ugid);
         

        if ($action == "mailsendusergroup")
        {   
            
            $usersummary = "<div class=\"header\">".$clang->gT("Mail to all Members")."</div>\n";
            $usersummary .= "<div class=\"messagebox\">\n";

            // user must be in user group
            // or superadmin
			//$this->load->model('user_in_groups');
			$result = User_in_groups::model()->getSomeRecords(array('uid'), array('ugid' => $ugid, 'uid' => Yii::app()->session['loginID']));
    
            if(count($result) > 0 || Yii::app()->session['loginID'] == 1)
            {
				$where	= array('and', 'ugid =' . $ugid, 'b.uid !=' . Yii::app()->session['loginID']);
				$join	= array('where' => "{{users}} b", 'on' => 'a.uid = b.uid');
				$eguresult = User_in_groups::model()->join(array('*'), "{{user_in_groups}} AS a", $where, $join, 'b.users_name');
                //die('me');
                $addressee = '';
                $to = '';
                if(isset($eguresult[0])) {
                foreach ($eguresult as $egurow)
                {
                    $to .= $egurow['users_name']. ' <'.$egurow['email'].'>'. '; ' ;
                    $addressee .= $egurow['users_name'].', ';
                }
                }else{
                   $to .= $eguresult['users_name']. ' <'.$eguresult['email'].'>'. '; ' ;
                   $addressee .= $eguresult['users_name'].', ';
                 }
                $to = substr("$to", 0, -2);
                $addressee = substr("$addressee", 0, -2);

				//$this->load->model('users');
				$from_user_result = User::model()->getSomeRecords(array('email', 'users_name', 'full_name'), array('uid' => Yii::app()->session['loginID']));
                $from_user_row = $from_user_result;
        
                if ($from_user_row[0]->full_name)
                {
                    $from = $from_user_row[0]->full_name;
                    $from .= ' <';
                    $from .= $from_user_row[0]->email . '> ';
                }
                else
                {
                    $from = $from_user_row[0]->users_name . ' <' . $from_user_row[0]->email . '> ';
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
                    $link = $this->getController()->createUrl("admin/usergroups/view/".$ugid);
                    $usersummary = "<div class=\"messagebox\">\n";
                    $usersummary .= "<div class=\"successheader\">".$clang->gT("Message(s) sent successfully!")."</div>\n"
                    . "<br />".$clang->gT("To:")."". $addressee."<br />\n"
                    . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
                else
                {
                    global $maildebug;
                    global $debug;
                    global $maildebugbody;
                    //$maildebug = (isset($maildebug)) ? $maildebug : "Their was a unknown error in the mailing part :)";
                    //$debug = (isset($debug)) ? $debug : 9;
                    //$maildebugbody = (isset($maildebugbody)) ? $maildebugbody : 'an unknown error accourd';
                    $link = $this->getController()->createUrl("admin/usergroups/mail/".$ugid);
                    $usersummary = "<div class=\"messagebox\">\n";
                    $usersummary .= "<div class=\"warningheader\">".sprintf($clang->gT("Email to %s failed. Error Message:"),$to) . " " . $maildebug."</div>";
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
            $this->getController()->render('/admin/usergroup/plain',$displaydata);
        }
        else
        {
			//$this->load->model('user_groups');
			$where	= array('and', 'a.ugid =' . $ugid, 'uid =' . Yii::app()->session['loginID']);
			$join	= array('where' => "{{user_in_groups}} AS b", 'on' => 'a.ugid = b.ugid');
			$result = User_groups::model()->join(array('a.ugid', 'a.name', 'a.owner_id', 'b.uid'), "{{user_groups}} AS a", $where, $join, 'name');
			
            $crow = $result;
            $data['ugid'] = $ugid;
            $data['clang'] = $clang;
            
           $this->getController()->render("/admin/usergroup/mailUserGroup_view",$data);
        }

        $this->getController()->_loadEndScripts();


	    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    /**
     * Usergroups::delete()
     * Function responsible to delete a user group.
     * @return
     */
    public function delete()
    {
        $clang = $this->yii->lang;

        $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
        $this->yii->setConfig("css_admin_includes", $css_admin_includes);
    	$this->controller->_js_admin_includes($this->yii->baseUrl.'scripts/admin/users.js');
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu(false);
        $action = $_POST['action'];
        $ugid = $_POST['ugid'];
        self::_usergroupbar($ugid);

        if ($action == "delusergroup")
        {
            $usersummary = "<div class=\"header\">".$clang->gT("Deleting User Group")."...</div>\n";
            $usersummary .= "<div class=\"messagebox\">\n";

            if ($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
            {

                if(!empty($ugid) && ($ugid > -1))
                {
					$query = 'SELECT ugid, name, owner_id FROM '.$this->yii->db->tablePrefix.'user_groups WHERE ugid=\''.$ugid.'\' AND owner_id=\''.$this->yii->session['loginID'].'\'';
					//$this->load->model('user_groups');
					//$result = $this->user_groups_model->getSomeRecords(array('ugid', 'name', 'owner_id'), array('ugid' => $ugid, 'owner_id' => $this->session->userdata('loginID')));
					$result = db_execute_assoc($query);
                    if($result->count() > 0)
                    {
                        $row = $result->readAll();
						
						$del_query = 'DELETE FROM '.$this->yii->db->tablePrefix.'user_groups WHERE owner_id=\''.$this->yii->session['loginID'].'\' AND ugid='.$ugid;
                        //$remquery = $this->user_groups_model->delete(array('owner_id' => $this->session->userdata('loginID'), 'ugid' => $ugid));
                        $delquery_result = db_execute_assoc($del_query);
                        
                        $del_user_in_groups_query = "DELETE FROM ".$this->yii->db->tablePrefix."user_in_groups WHERE ugid=$ugid AND uid=".$this->yii->session['loginID'];
                         
                        if($delquery_result) //Checked)
                        {
                            $usersummary .= "<br />".$clang->gT("Group Name").": {$row[0]['name']}<br /><br />\n";
                            $usersummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                        }
                        else
                        {
                            $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group.")."</div>\n";
                        }
                        $link = $this->controller->createUrl("admin/usergroups/view");
                        $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                    else
                    {
                        //include("access_denied.php");
                    }
                }
                else
                {
                    $link = $this->controller->createUrl("admin/usergroups/view");
                    $usersummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user group. No group selected.")."</div>\n";
                    $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            $usersummary .= "</div>\n";

            $displaydata['display'] = $usersummary;
            //$data['display'] = $editsurvey;
            $this->controller->render('/admin/usergroup/plain', $displaydata);
        }

        $this->controller->_loadEndScripts();
        
	    $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));

    }


    /**
     * Usergroups::add()
     * Load add user group screen.
     * @return
     */
    public function add()
    {
        $clang = $this->yii->lang;

        $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
        $this->yii->setConfig("css_admin_includes", $css_admin_includes);
    	$this->controller->_js_admin_includes($this->yii->baseUrl.'scripts/admin/users.js');
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu(false);
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';
        
        if ($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
        {

            self::_usergroupbar(false);
            $data['clang'] = $clang;
            
            if ($action == "usergroupindb")
            {
                $usersummary = "<div class=\"header\">".$clang->gT("Adding User Group")."...</div>\n";
                $usersummary .= "<div class=\"messagebox\">\n";

                if ($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
                {
                    $db_group_name = $_POST['group_name'];
                    $db_group_description = $_POST['group_description'];
                    $html_group_name = htmlspecialchars($_POST['group_name']);
                    $html_group_description = htmlspecialchars($_POST['group_description']);

                    if(isset($db_group_name) && strlen($db_group_name) > 0)
                    {
                        if (strlen($db_group_name) > 21)
                        {
                            $link = $this->controller->createUrl("admin/usergroups/add");
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
                                $link = $this->controller->createUrl("admin/usergroups/view/$ugid");
                                $usersummary .= "<div class=\"successheader\">".$clang->gT("User group successfully added!")."</div>\n";
                                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                            }
                            else
                            {
                                $link = $this->controller->createUrl("admin/usergroups/add");
                                $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                                . "<br />" . $clang->gT("Group already exists!")."<br />\n";
                                $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                            }
                        }

                    }
                    else
                    {
                        $link = $this->controller->createUrl("admin/usergroups/add");
                        $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add Group!")."</div>\n"
                        . "<br />" . $clang->gT("Group name was not supplied!")."<br />\n";
                        $usersummary .= "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {}
                $usersummary .= "</div>\n";
                $displaydata['display'] = $usersummary;
                //$data['display'] = $editsurvey;
                $this->controller->render('/admin/usergroup/plain', $displaydata);

            }
            else
            {
                $this->controller->render('/admin/usergroup/addUserGroup_view', $data);
            }


        }
        
        $this->controller->_loadEndScripts();

	 	$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));

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
        $clang = $this->yii->lang;


        $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
        $this->yii->setConfig("css_admin_includes", $css_admin_includes);
    	$this->controller->_js_admin_includes($this->yii->baseUrl.'scripts/admin/users.js');
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu(false);
        $action = (isset($_POST['action'])) ? $_POST['action'] : '';

        if ($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
        {

            self::_usergroupbar($ugid);
            $data['clang'] = $clang;
            if ($action == "editusergroupindb")
            {
                if ($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
                {
                    $ugid = $_POST['ugid'];

                    $db_name = $_POST['name'];
                    $db_description = $_POST['description'];
                    $html_name = html_escape($_POST['name']);
                    $html_description = html_escape($_POST['description']);

            		$usersummary = "<div class=\"messagebox\">\n";

                    if(self::_updateusergroup($db_name, $db_description, $ugid))
                    {
                        $link = $this->controller->createUrl("admin/usergroups/view/$ugid");
            			$usersummary .= "<div class=\"successheader\">".$clang->gT("Edit User Group Successfully!")."</div>\n"

                        . "<br />".$clang->gT("Name").": {$html_name}<br />\n"
                        . $clang->gT("Description: ").$html_description."<br />\n"
                        . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                        //. "<br /><a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
                    }
                    else
            		{
            			$link = $this->createUrl("admin/usergroups/view");
                        $usersummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update!")."</div>\n"
                        . "<br/><input type=\"submit\" onclick=\"window.location='$link'\" value=\"".$clang->gT("Continue")."\"/>\n";
                        //. "<br /><a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
                    }
            		$usersummary .= "</div>\n";

                    $displaydata['display'] = $usersummary;
                    //$data['display'] = $editsurvey;
                    $this->controller->render('/admin/usergroup/plain', $displaydata);

            	}
                else
                {
                    //include("access_denied.php");
                }


            }
            else
            {
            	$query = 'SELECT * FROM '.$this->yii->db->tablePrefix.'user_groups WHERE ugid='.$ugid.' AND owner_id='.$this->yii->session['loginID'];
            	$result = db_execute_assoc($query);
				/*$this->load->model('user_groups');
				$result = $this->user_groups_model->getAllRecords(array('ugid' => $ugid, 'owner_id' => $this->session->userdata('loginID')));*/
                $esrow = $result->readAll();
                $data['esrow'] = $esrow[0];
                $data['ugid'] = $ugid;
                $this->controller->render("/admin/usergroup/editUserGroup_view", $data);
            }


        }
        $this->controller->_loadEndScripts();


	   $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));
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
        $clang = $this->yii->lang;

        $css_admin_includes[] = $this->yii->getConfig('styleurl')."admin/default/superfish.css";
        $this->yii->setConfig("css_admin_includes", $css_admin_includes);
    	$this->controller->_js_admin_includes($this->yii->baseUrl.'scripts/admin/users.js');
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu(false);

        self::_usergroupbar($ugid);

        if ( $this->yii->session['loginID'])
        {

            if($ugid)
            {

                $ugid = sanitize_int($ugid);

				//$this->user_groups_model = new User_groups;

				$query = "SELECT a.ugid, a.name, a.owner_id, a.description, b.uid FROM ".$this->yii->db->tablePrefix."user_groups AS a LEFT JOIN ".$this->yii->db->tablePrefix."user_in_groups AS b ON a.ugid = b.ugid WHERE a.ugid = {$ugid} AND uid = ".$this->yii->session['loginID']." ORDER BY name";
				//$select	= array('a.ugid', 'a.name', 'a.owner_id', 'a.description', 'b.uid');
				//$join	= array('where' => 'user_in_groups AS b', 'type' => 'left', 'on' => 'a.ugid = b.ugid');
				//$where	= array('uid' => $this->session->userdata('loginID'), 'a.ugid' => $ugid);
				
				$result = db_execute_assoc($query)->readAll();
				
				//$result = $this->user_groups_model->join($select, 'user_groups AS a', $where, $join, 'name');
                $crow = $result[0];

                if($result)
                {
                	$usergroupsummary = '';
                    if(!empty($crow['description']))
                   
                        $usergroupsummary = "<table width='100%' border='0'>\n"
                        . "<tr><td align='justify' colspan='2' height='4'>"
                        . "<font size='2' ><strong>".$clang->gT("Description: ")."</strong>"
                        . "{$crow['description']}</font></td></tr>\n"
                        . "</table>";
                    }

					//$this->user_in_groups_model = new User_in_groups;

					 $eguquery = "SELECT * FROM ".$this->yii->db->tablePrefix."user_in_groups AS a INNER JOIN ".$this->yii->db->tablePrefix."users AS b ON a.uid = b.uid WHERE ugid = " . $ugid . " ORDER BY b.users_name";
					$eguresult = db_execute_assoc($eguquery);
					
                    $usergroupsummary .= "<table class='users'>\n"
                    . "<thead><tr>\n"
                    . "<th>".$clang->gT("Action")."</th>\n"
                    . "<th>".$clang->gT("Username")."</th>\n"
                    . "<th>".$clang->gT("Email")."</th>\n"
                    . "</tr></thead><tbody>\n";

					$query2 = "SELECT ugid FROM ".$this->yii->db->tablePrefix."user_groups WHERE ugid = ".$ugid." AND owner_id = ".$this->yii->session['loginID'];
                    $result2 = db_select_limit_assoc($query2, 1);
                    $row2 = $result2->readAll();

                    $row = 1;
                    $usergroupentries='';
                    foreach ($eguresult->readAll() as $egurow)
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

                        if($this->yii->session['USER_RIGHT_SUPERADMIN'] == 1)
                        {
                            $usergroupentries .= "<form method='post' action='scriptname?action=deleteuserfromgroup&amp;ugid=$ugid'>"
                            ." <input type='image' src='".$this->yii->getConfig('imageurl')."/token_delete.png' alt='".$clang->gT("Delete this user from group")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
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
                    
                    if(isset($row2[0]['ugid']))
                    {
                        $usergroupsummary .= "<form action='" . $this->getController()->createUrl('admin/usergroups/addusertogroup/' . $ugid) . "' method='post'>\n"
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
                    $this->controller->render('/admin/usergroup/plain',$displaydata);
                }
                else
                {
                    //include("access_denied.php");
                }
         }
        else
        {
            //include("access_denied.php");
        }

        $this->controller->_loadEndScripts();


	   $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));
    }


    function addusertogroup ()
    {
        $this->controller->_js_admin_includes($this->yii->baseUrl.'scripts/admin/users.js');
        $this->controller->_getAdminHeader();
        $this->getController()->_showadminmenu(false);        
	    Yii::app()->loadHelper('database');
	    $clang = Yii::app()->lang;
	    $postuserid = CHttpRequest::getPost('uid');
	    $ugid = $_GET['addusertogroup'];
	    $this->_usergroupbar($ugid);
        $addsummary = "<div class=\"header\">".$clang->gT("Adding User to group")."...</div>\n";
        $addsummary .= "<div class=\"messagebox\">\n";

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
        {
            $query = "SELECT ugid, owner_id FROM {{user_groups}} WHERE ugid = " . $ugid . " AND owner_id = ".Yii::app()->session['loginID']." AND owner_id != ".$postuserid;
            $result = db_execute_assoc($query); //Checked
            if($result->count() > 0)
            {
                if($postuserid > 0)
                {
                    //$isrquery = "INSERT INTO {{user_in_groups}} VALUES({$ugid},{$postuserid})";
                    $isrresult = User_in_groups::model()->insert(array('ugid' => $ugid, 'uid' =>$postuserid)); //Checked

                    if($isrresult)
                    {
                        $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                    }
                    else  // ToDo: for this to happen the keys on the table must still be set accordingly
                    {
                        // Username already exists.
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n" . "<br />" . $clang->gT("Username already exists.")."<br />\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n" . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                }
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.location='" . $this->getController()->createUrl('admin/usergroups/view') . '/' . $ugid . "'\" value=\"".$clang->gT("Continue")."\"/>\n";
                
            }
            else
            {
            	echo 'access denied';
              //include("access_denied.php");
            }
        }
        else
        {
        	echo 'access denied';
            //include("access_denied.php");
        }
        $addsummary .= "</div>\n";
        $displaydata['display'] = $addsummary;
        $this->controller->render('/admin/usergroup/plain',$displaydata);
        $this->controller->_loadEndScripts();
        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->yii->lang->gT("LimeSurvey online manual"));
    }


    /**
     * Usergroups::_usergroupbar()
     * Load menu bar of user group controller.
     * @param bool $ugid
     * @return
     */
    function _usergroupbar($ugid=false)
    {
        $data['clang'] = $this->yii->lang;
        $this->yii->loadHelper('database');
        if($ugid)
        {
			$grpquery = "SELECT gp.* FROM ".$this->yii->db->tablePrefix."user_groups AS gp, ".$this->yii->db->tablePrefix."user_in_groups AS gu WHERE gp.ugid=gu.ugid AND gp.ugid = $ugid AND gu.uid=".$this->yii->session['loginID'];
			$grpresult = db_execute_assoc($grpquery);
			$grpresultcount =  db_records_count($grpquery);
			
            if ($grpresultcount>0)
            {
                $grow = array_map('htmlspecialchars', $grpresult->read());
            }

            $data['grow'] = $grow;
            $data['grpresultcount'] = $grpresultcount;

        }

        $data['ugid'] = $ugid;


        $this->controller->render('/admin/usergroup/usergroupbar_view',$data);
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
    	$query = 'UPDATE '.$this->yii->db->tablePrefix.'user_groups SET name=\''.$name.'\', description=\''.$description.'\' WHERE ugid=\''.$ugid.'\'';
        //$this->load->model('user_groups');
		//$uquery = $this->user_groups_model->update(array('name' => $name, 'description' => $description), array('ugid' => $ugid));
		$uquery = db_execute_assoc($query);
        return $uquery;  //or safe_die($connect->ErrorMsg()) ; //Checked)
    }

    /**
     * Usergroups::_refreshtemplates()
     * Function to refresh templates.
     * @return
     */
    function _refreshtemplates() {
        
        $template_a = gettemplatelist();
    	foreach ($template_a as $tp=>$fullpath) {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
			$this->load->model('templates');
			$result = $this->templates_model->getAllRecords_like(array('folder' => $tp));

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
        $connect= $this->yii->db;
        $iquery = "INSERT INTO ".$this->yii->db->tablePrefix."user_groups (`name`, `description`, `owner_id`) VALUES('{$group_name}', '{$group_description}', '{$_SESSION['loginID']}')";
        $command = $connect->createCommand($iquery);
        $result = $command->query();
        /*$data = array(
                'name' => $group_name,
                'description' => $group_description,
                'owner_id' => $this->session->userdata('loginID')

        );
        $this->load->model('user_groups_model');
        $this->load->model('user_in_groups_model');*/
		
        if($result) { //Checked
            $id = $connect->getLastInsertID(); //$connect->Insert_Id(db_table_name_nq('user_groups'),'ugid');
            
            if($id > 0) {
            	$user_in_groups_query = 'INSERT INTO '.$this->yii->db->tablePrefix.'user_in_groups (ugid, uid) VALUES ('.$id.','.$this->yii->session['loginID'].')';
            	db_execute_assoc($user_in_groups_query);
            	/*$this->user_in_groups_model = new User_in_groups;
            	$this->user_in_groups_model->ugid = $id;
            	$this->user_in_groups_model->uid = $this->yii->session['loginID'];
            	$this->user_in_groups_model->save();*/
				//$this->user_in_groups_model->insert(array('ugid' => $id, 'uid' => $this->session->userdata('loginID')));
            }
            return $id;
        } else {
            return -1;
        }
    }


}
