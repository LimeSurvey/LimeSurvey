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
class surveypermission extends Survey_Common_Action {
	/**
	 * Routes to current subview
	 *
	 * @access public
	 * @param string $sa
	 * @return
	 */
	public function run($sa)
	{
		if ($sa == 'view')
			$this->route('view', array('surveyid'));
		elseif ($sa == 'addusergroup')
			$this->route('addusergroup', array('surveyid'));
		elseif ($sa == 'adduser')
			$this->route('adduser', array('surveyid'));
		elseif ($sa == 'set')
			$this->route('set', array('surveyid'));
		elseif ($sa == 'delete')
			$this->route('delete', array('surveyid'));
		elseif ($sa == 'surveyright')
			$this->route('surveyright', array('surveyid'));
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
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'surveysecurity');

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');

        if(bHasSurveyPermission($surveyid,'survey','read'))
        {
            $aBaseSurveyPermissions=Survey_permissions::getBasePermissions();

            $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/jquery/jquery.tablesorter.min.js');
            $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/admin/surveysecurity.js');

            $query2 = "SELECT p.sid, p.uid, u.users_name, u.full_name FROM {{survey_permissions}} AS p INNER JOIN {{users}}  AS u ON p.uid = u.uid
            WHERE p.sid = {$surveyid} AND u.uid != ".Yii::app()->session['loginID'] ."
            GROUP BY p.sid, p.uid, u.users_name, u.full_name
            ORDER BY u.users_name";
            $result2 = Yii::app()->db->createCommand($query2)->query(); //Checked

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

            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true)
            {
                $authorizedGroupsList = getusergrouplist(NULL,'simplegidarray');
            }

            $surveysecurity .= "<tbody>\n";
            if($result2->getRowCount() > 0)
            {
                //	output users
                $row = 0;

                foreach ($result2->readAll() as $PermissionRow)
                {
                    $query3 = "SELECT a.ugid FROM {{user_in_groups}} AS a RIGHT OUTER JOIN {{users}} AS b ON a.uid = b.uid WHERE b.uid = ".$PermissionRow['uid'];
                    $result3 = Yii::app()->db->createCommand($query3)->query(); //Checked
                    foreach ($result3->readAll() as $resul3row)
                    {
                        if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
                        in_array($resul3row['ugid'],$authorizedGroupsList))
                        {
                            $group_ids[] = $resul3row['ugid'];
                        }
                    }

                    if(isset($group_ids) && $group_ids[0] != NULL)
                    {
                        $group_ids_query = implode(" OR ugid=", $group_ids);
                        unset($group_ids);

                        $query4 = "SELECT name FROM {{user_groups}} WHERE ugid = ".$group_ids_query;
                        $result4 = Yii::app()->db->createCommand($query4)->query(); //Checked

                        foreach ($result4->readAll() as $resul4row)
                        {
                            $group_names[] = $resul4row['name'];
                        }
                        if(count($group_names) > 0)
                            $group_names_query = implode(", ", $group_names);
                    }
                    //                  else {break;} //TODO Commented by lemeur
                    $surveysecurity .= "<tr>\n";

                    $surveysecurity .= "<td>\n";
                    $surveysecurity .= "<form style='display:inline;' method='post' action='".$this->getController()->createUrl('admin/surveypermission/sa/set/surveyid/'.$surveyid)."'>"
                    ."<input type='image' src='{$imageurl}/token_edit.png' title='".$clang->gT("Edit permissions")."' />"
                    ."<input type='hidden' name='action' value='setsurveysecurity' />"
                    ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                    ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                    ."</form>\n";
                    $surveysecurity .= "<form style='display:inline;' method='post' action='".$this->getController()->createUrl('admin/surveypermission/sa/delete/surveyid/'.$surveyid)."'>"
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
            . "<form class='form44' action='".$this->getController()->createUrl('admin/surveypermission/sa/adduser/surveyid/'.$surveyid)."' method='post'><ul>\n"
            . "<li><label for='uidselect'>".$clang->gT("User").": </label><select id='uidselect' name='uid'>\n"
            . sGetSurveyUserlist(false,false,$surveyid)
            . "</select>\n"
            . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User")."'  onclick=\"if (document.getElementById('uidselect').value == -1) { alert('".$clang->gT("Please select a user first","js")."'); return false;}\"/>"
            . "<input type='hidden' name='action' value='addsurveysecurity' />"
            . "</li></ul></form>\n"
            . "<form class='form44' action='".$this->getController()->createUrl('admin/surveypermission/sa/addusergroup/surveyid/'.$surveyid)."' method='post'><ul><li>\n"
            . "<label for='ugidselect'>".$clang->gT("Groups").": </label><select id='ugidselect' name='ugid'>\n"
            . getsurveyusergrouplist('htmloptions',$surveyid)
            . "</select>\n"
            . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add User Group")."' onclick=\"if (document.getElementById('ugidselect').value == -1) { alert('".$clang->gT("Please select a user group first","js")."'); return false;}\" />"
            . "<input type='hidden' name='action' value='addusergroupsurveysecurity' />\n"
            . "</li></ul></form>";

            $data['display'] = $surveysecurity;
            $this->getController()->render('/survey_view',$data);
        }
        else
        {
            access_denied();

        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

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

        $action = $_POST['action'];
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."/admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'addsurveysecurity');

        $clang = Yii::app()->lang;

        $imageurl = Yii::app()->getConfig('imageurl');

        $postusergroupid = !empty($_POST['gid']) ? $_POST['gid'] : false;


        if($action == "addusergroupsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Add user group")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\" >\n";

            $query = "SELECT sid, owner_id FROM {{surveys}} WHERE sid = {$surveyid} AND owner_id = ".Yii::app()->session['loginID'];
            $result = Yii::app()->db->createCommand($query)->query(); //Checked
            if( ($result->getRowCount() > 0 && in_array($postusergroupid,getsurveyusergrouplist('simpleugidarray'))) ||Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
            {
                if($postusergroupid > 0){
                    $query2 = "SELECT b.uid FROM (SELECT uid FROM {{survey_permissions}} WHERE sid = {$surveyid}) AS c RIGHT JOIN {{user_in_groups}} AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$postusergroupid}";
                    $result2 = Yii::app()->db->createCommand($query2)->query(); //Checked
                    if($result2->getRowCount() > 0)
                    {
                        while ($row2 = $result2->read())
                        {
                            $uid_arr[] = $row2['uid'];
                            $isrquery = "INSERT INTO {$dbprefix}survey_permissions (sid,uid,permission,read_p) VALUES ({$surveyid}, {$row2['uid']},'survey',1) ";
                            $isrresult = Yii::app()->db->createCommand($isrquery)->query(); //Checked
                            if (!$isrresult) break;
                        }

                        if($isrresult)
                        {
                            $addsummary .= "<div class=\"successheader\">".$clang->gT("User Group added.")."</div>\n";
                            $_SESSION['uids'] = $uid_arr;
                            $addsummary .= "<br /><form method='post' action='".$this->getController()->createUrl('admin/surveypermission/sa/set/surveyid/'.$surveyid)."'>"
                            ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
                            ."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
                            ."<input type='hidden' name='ugid' value='{$postusergroupid}' />"
                            ."</form>\n";
                        }
                        else
                        {
                            // Error while adding user to the database
                            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                        }
                    }
                    else
                    {
                        // no user to add
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add User Group.")."</div>\n";
                        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                    . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                access_denied();
            }
            $addsummary .= "</div>\n";
        }
        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));


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

        $action = $_POST['action'];
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'addsurveysecurity');

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = $_POST['uid'];

        if($action == "addsurveysecurity")
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Add User")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\">\n";

            $query = "SELECT sid, owner_id FROM {{surveys}} WHERE sid = {$surveyid} AND owner_id = ". Yii::app()->session['loginID']." AND owner_id != ".$postuserid;
            $result = Yii::app()->db->createCommand($query)->query(); //Checked
            if( ($result->getRowCount() > 0 && in_array($postuserid,getuserlist('onlyuidarray'))) ||
            Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
            {

                if($postuserid > 0){

                    $isrquery = "INSERT INTO {{survey_permissions}} (sid,uid,permission,read_p) VALUES ( {$surveyid}, {$postuserid}, 'survey', 1)";
                    $isrresult = Yii::app()->db->createCommand($isrquery)->query(); //Checked

                    if($isrresult)
                    {

                        $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                        $addsummary .= "<br /><form method='post' action='".$this->getController()->createUrl('admin/surveypermission/sa/set/surveyid/'.$surveyid)."'>"
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
                        $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                    }
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user.")."</div>\n"
                    . "<br />" . $clang->gT("No Username selected.")."<br />\n";
                    $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                }
            }
            else
            {
                access_denied();
            }

            $addsummary .= "</div>\n";

            $data['display'] = $addsummary;
            $this->getController()->render('/survey_view',$data);
        }
        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
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

        $action = $_POST['action'];
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->getConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'addsurveysecurity');

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : null;
        $postusergroupid = !empty($_POST['gid']) ? $_POST['gid'] : null;

        if($action == "setsurveysecurity" || $action == "setusergroupsurveysecurity")
        {
            $query = "SELECT sid, owner_id FROM {{surveys}} WHERE sid = {$surveyid} AND owner_id = ".Yii::app()->session['loginID'];
            if ($action == "setsurveysecurity")
            {
                $query.=  " AND owner_id != ".$postuserid;
            }
            $result = Yii::app()->db->createCommand($query)->query(); //Checked
            if($result->getRowCount() > 0 || Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
            {
                //$js_admin_includes[]='../scripts/jquery/jquery.tablesorter.min.js';
                //$js_admin_includes[]='scripts/surveysecurity.js';
                $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/jquery/jquery.tablesorter.min.js');
                $this->getController()->_js_admin_includes(Yii::app()->baseUrl.'/scripts/admin/surveysecurity.js');
                if ($action == "setsurveysecurity")
                {
                    $query = "select users_name from {{users}} where uid={$postuserid}";
                    $res = Yii::app()->db->createCommand($query)->query();
                    $resrow = $res->read();
                    $sUsername=$resrow['users_name']; //$connect->GetOne("select users_name from {{users}} where uid={$postuserid}");
                    $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for user %s"),"<span style='font-style:italic'>".$sUsername."</span>")."</div>";
                }
                else
                {
                    $query = "select name from {{user_groups}} where ugid={$postusergroupid}";
                    $res = Yii::app()->db->createCommand($query)->query();
                    $resrow = $res->read();
                    $sUsergroupName=$resrow['name']; //$connect->GetOne("select name from {{user_groups}} where ugid={$postusergroupid}");
                    $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for group %s"),"<span style='font-style:italic'>".$sUsergroupName."</span>")."</div>";
                }
                $usersummary .= "<br /><form action='".$this->getController()->createUrl('admin/surveypermission/sa/surveyright/surveyid/'.$surveyid)."' method='post'>\n"
                . "<table style='margin:0 auto;' border='0' class='usersurveypermissions'><thead>\n";

                $usersummary .= ""
                . "<tr><th></th><th align='center'>".$clang->gT("Permission")."</th>\n"
                . "<th align='center'><input type='button' id='btnToggleAdvanced' value='<<' /></th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Create")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("View/read")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Update")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Delete")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Import")."</th>\n"
                . "<th align='center' class='extended'>".$clang->gT("Export")."</th>\n"
                . "</tr></thead>\n";

                //content
                $aBasePermissions=Survey_permissions::getBasePermissions();

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
                $this->getController()->render('/survey_view',$data);
            }
            else
            {
                include("access_denied.php");
            }
        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

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

        $action = $_POST['action'];
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'addsurveysecurity');

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : false;
        $postusergroupid = !empty($_POST['gid']) ? $_POST['gid'] : false;


        if($action == "delsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Deleting User")."</div>\n";
            $addsummary .= "<div class=\"messagebox\">\n";

            $query = "SELECT sid, owner_id FROM {{surveys}} WHERE sid = {$surveyid} AND owner_id = ".Yii::app()->session['loginID']." AND owner_id != ".$postuserid;
            $result = Yii::app()->db->createCommand($query)->query(); //Checked
            if($result->getRowCount() > 0 || Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
            {
                if (isset($postuserid))
                {
                    $dquery="DELETE FROM {{survey_permissions}} WHERE uid={$postuserid} AND sid={$surveyid}";	//	added by Dennis
                    $dresult=Yii::app()->db->createCommand($dquery)->query(); //Checked

                    $addsummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string($_POST['user'])."<br /><br />\n";
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Success!")."</div>\n";
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Could not delete user. User was not supplied.")."</div>\n";
                }
                $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            }
            else
            {
                access_denied();
            }
            $addsummary .= "</div>\n";

            $data['display'] = $addsummary;
            $this->getController()->render('/survey_view',$data);
        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
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

        $action = $_POST['action'];
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."/admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);


        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,'addsurveysecurity');

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : false;
        $postusergroupid = !empty($_POST['gid']) ? $_POST['gid'] : false;

        if ($action == "surveyrights")
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Edit survey permissions")."</div>\n";
            $addsummary .= "<div class='messagebox ui-corner-all'>\n";

            if(isset($postuserid)){
                $query = "SELECT sid, owner_id FROM {{surveys}} WHERE sid = {$surveyid}";
                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
                {
                    $query.=" AND owner_id != {$postuserid} AND owner_id = ".Yii::app()->session['loginID'];
                }
            }
            else{
                $sQuery = "SELECT owner_id FROM {{surveys}} WHERE sid = {$surveyid}";
                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
                {
                    $query.=" AND owner_id = ".Yii::app()->session['loginID'];
                }
                $res= Yii::app()->db->createCommand($sQuery)->query();
                $resrow=$res->read();
                $iOwnerID=$resrow['owner_id']; //$connect->GetOne($sQuery);
            }

            $aBaseSurveyPermissions = Survey_permissions::getBasePermissions();
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
                $sQuery = "SELECT uid from {{user_in_groups}} where ugid = {$postusergroupid} and uid<>{$_SESSION['loginID']} AND uid<>{$iOwnerID}";
                $oResult = Yii::app()->db->createCommand($sQuery)->query(); //Checked
                if($oResult->getRowCount() > 0)
                {
                    foreach ($oResult->readAll() as $aRow)
                    {
						Survey_permissions::setPermission($aRow['uid'], $surveyid, $aPermissions);
                    }
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions for all users in this group were successfully updated.")."</div>\n";
                }
            }
            else
            {
                if (Survey_permissions::setPermission($postuserid, $surveyid, $aPermissions))
                {
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions were successfully updated.")."</div>\n";
                }
                else
                {
                    $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to update survey permissions!")."</div>\n";
                }

            }
            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
            $addsummary .= "</div>\n";
            $data['display'] = $addsummary;
            $this->getController()->render('/survey_view',$data);
        }

        $this->getController()->_loadEndScripts();


        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }

}
