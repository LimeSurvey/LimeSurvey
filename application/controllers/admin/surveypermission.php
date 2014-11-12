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
 */

/**
* surveypermission
*
* @package LimeSurvey
* @copyright 2011
* @access public
*/
class surveypermission extends Survey_Common_Action {

    /**
    * Load survey security screen.
    * @param mixed $surveyid
    * @return void
    */
    function index($surveyid)
    {
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();
        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('adminimageurl');

        if(Permission::model()->hasSurveyPermission($surveyid,'surveysecurity','read'))
        {
            $aBaseSurveyPermissions=Permission::model()->getSurveyBasePermissions();
            $userList=getUserList('onlyuidarray'); // Limit the user list for the samegrouppolicy
            App()->getClientScript()->registerPackage('jquery-tablesorter');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "surveypermissions.js");
            $surveysecurity ="<div class='header ui-widget-header'>".$clang->gT("Survey permissions")."</div>\n";
            $result2 = Permission::model()->getUserDetails($surveyid);
            if(count($result2) > 0)
            {
                    $surveysecurity = ""
                    . "<table class='surveysecurity'><thead>"
                    . "<tr>\n"
                    . "<th>".$clang->gT("Action")."</th>\n"
                    . "<th>".$clang->gT("Username")."</th>\n"
                    . "<th>".$clang->gT("User group")."</th>\n"
                    . "<th>".$clang->gT("Full name")."</th>\n";
                foreach ($aBaseSurveyPermissions as $sPermission=>$aSubPermissions )
                {
                    $surveysecurity.="<th><img src=\"{$imageurl}{$aSubPermissions['img']}_30.png\" alt=\"<span style='font-weight:bold;'>".$aSubPermissions['title']."</span><br />".$aSubPermissions['description']."\" /></th>\n";
                }
                $surveysecurity .= "</tr></thead>\n";

                // Foot first

                if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true)
                {
                    $authorizedGroupsList = getUserGroupList(NULL,'simplegidarray');
                }

                $surveysecurity .= "<tbody>\n";
                $row = 0;
                foreach ($result2 as $PermissionRow)
                {
                    if(in_array($PermissionRow['uid'],$userList))
                    {

                        $result3 = UserInGroup::model()->with('users')->findAll('users.uid = :uid',array(':uid' => $PermissionRow['uid']));
                        foreach ($result3 as $resul3row)
                        {
                            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
                            in_array($resul3row->ugid,$authorizedGroupsList))
                            {
                                $group_ids[] = $resul3row->ugid;
                            }
                        }

                        if(isset($group_ids) && $group_ids[0] != NULL)
                        {
                            $group_ids_query = implode(",", $group_ids);
                            unset($group_ids);
                            $result4 = UserGroup::model()->findAll("ugid IN ($group_ids_query)");

                            foreach ($result4 as $resul4row)
                            {
                                $group_names[] = $resul4row->name;
                            }
                            if(count($group_names) > 0)
                                $group_names_query = implode(", ", $group_names);
                        }
                        //                  else {break;} //TODO Commented by lemeur
                        $surveysecurity .= "<tr>\n";

                        $surveysecurity .= "<td>\n";

                        if(Permission::model()->hasSurveyPermission($surveyid,'surveysecurity','update'))
                        {
                            if($PermissionRow['uid']!=Yii::app()->user->getId() || Permission::model()->hasGlobalPermission('superadmin','read')) // Can not update own security
                            {
                                $surveysecurity .= CHtml::form(array("admin/surveypermission/sa/set/surveyid/{$surveyid}"), 'post', array('style'=>"display:inline;"))
                                ."<input type='image' src='{$imageurl}edit_16.png' alt='".$clang->gT("Edit permissions")."' />"
                                ."<input type='hidden' name='action' value='setsurveysecurity' />"
                                ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                                ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                                ."</form>\n";
                            }
                        }
                        if(Permission::model()->hasSurveyPermission($surveyid,'surveysecurity','delete'))
                        {
                            $surveysecurity .= CHtml::form(array("admin/surveypermission/sa/delete/surveyid/{$surveyid}"), 'post', array('style'=>"display:inline;"))
                            ."<input type='image' src='{$imageurl}/token_delete.png' alt='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
                            ."<input type='hidden' name='action' value='delsurveysecurity' />"
                            ."<input type='hidden' name='user' value='{$PermissionRow['users_name']}' />"
                            ."<input type='hidden' name='uid' value='{$PermissionRow['uid']}' />"
                            ."</form>";
                        }

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
                                if ($sPDetailValue && Permission::model()->hasSurveyPermission($surveyid,$sPKey,$sPDetailKey,$PermissionRow['uid']) && !($sPKey=='survey' && $sPDetailKey=='read')) $iCount++;
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
                            $surveysecurity .= "<td>\n$insert\n</td>\n";
                        }

                        $surveysecurity .= "</tr>\n";
                        $row++;
                    }
                }
                $surveysecurity .= "</tbody>\n"
                . "</table>\n";
            }
            else
            {

            }
            if(Permission::model()->hasSurveyPermission($surveyid,'surveysecurity','create'))
            {
                $surveysecurity .= CHtml::form(array("admin/surveypermission/sa/adduser/surveyid/{$surveyid}"), 'post', array('class'=>"form44"))."<ul>\n"
                . "<li><label for='uidselect'>".$clang->gT("User").": </label><select id='uidselect' name='uid'>\n"
                . getSurveyUserList(false,false,$surveyid)
                . "</select>\n"
                . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add user")."'  onclick=\"if (document.getElementById('uidselect').value == -1) { alert('".$clang->gT("Please select a user first","js")."'); return false;}\"/>"
                . "<input type='hidden' name='action' value='addsurveysecurity' />"
                . "</li></ul></form>\n";

                $surveysecurity .=  CHtml::form(array("admin/surveypermission/sa/addusergroup/surveyid/{$surveyid}"), 'post', array('class'=>"form44"))."<ul><li>\n"
                . "<label for='ugidselect'>".$clang->gT("User group").": </label><select id='ugidselect' name='ugid'>\n"
                . getSurveyUserGroupList('htmloptions',$surveyid)
                . "</select>\n"
                . "<input style='width: 15em;' type='submit' value='".$clang->gT("Add user group")."' onclick=\"if (document.getElementById('ugidselect').value == -1) { alert('".$clang->gT("Please select a user group first","js")."'); return false;}\" />"
                . "<input type='hidden' name='action' value='addusergroupsurveysecurity' />\n"
                . "</li></ul></form>";
            }

            $aViewUrls['output'] = $surveysecurity;
        }
        else
        {
            $this->getController()->error('Access denied');
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

    /**
    * surveypermission::addusergroup()
    * Function responsible to add usergroup.
    * @param mixed $surveyid
    * @return void
    */
    function addusergroup($surveyid)
    {
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();

        $action = $_POST['action'];
        $clang = Yii::app()->lang;

        $imageurl = Yii::app()->getConfig('imageurl');

        $postusergroupid = !empty($_POST['ugid']) ? $_POST['ugid'] : false;


        if($action == "addusergroupsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Add user group")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\" >\n";

            $result = Survey::model()->findAll('sid = :surveyid AND owner_id = :owner_id',array(':surveyid' => $surveyid, ':owner_id' => Yii::app()->session['loginID']));
            if( Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')
                && in_array($postusergroupid,getSurveyUserGroupList('simpleugidarray',$surveyid))
                )
            {
                if($postusergroupid > 0){
                    $result2 = User::model()->getCommonUID($surveyid, $postusergroupid); //Checked
                    $result2 = $result2->readAll();
                    if(count($result2) > 0)
                    {
                        foreach ($result2 as $row2 )
                        {
                            $uid_arr[] = $row2['uid'];
                            $isrresult = Permission::model()->insertSomeRecords(array('entity_id' => $surveyid, 'entity'=>'survey', 'uid' => $row2['uid'], 'permission' => 'survey', 'read_p' => 1));
                            if (!$isrresult) break;
                        }

                        if($isrresult)
                        {
                            $addsummary .= "<div class=\"successheader\">".$clang->gT("User group added.")."</div>\n";
                            Yii::app()->session['uids'] = $uid_arr;
                            $addsummary .= "<br />"
                            .CHtml::form(array("admin/surveypermission/sa/set/surveyid/{$surveyid}"), 'post')
                            ."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
                            ."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
                            ."<input type='hidden' name='ugid' value='{$postusergroupid}' />"
                            ."</form>\n";
                        }
                        else
                        {
                            // Error while adding user to the database
                            $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user group.")."</div>\n";
                            $addsummary .= "<br/><input type=\"submit\" onclick=\"window.open('".$this->getController()->createUrl('admin/surveypermission/sa/view/surveyid/'.$surveyid)."', '_top')\" value=\"".$clang->gT("Continue")."\"/>\n";
                        }
                    }
                    else
                    {
                        // no user to add
                        $addsummary .= "<div class=\"warningheader\">".$clang->gT("Failed to add user group.")."</div>\n";
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
                $this->getController()->error('Access denied');
            }
            $addsummary .= "</div>\n";

            $aViewUrls['output'] = $addsummary;
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }


    /**
    * surveypermission::adduser()
    * Function responsible to add user.
    * @param mixed $surveyid
    * @return void
    */
    function adduser($surveyid)
    {
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();

        $action = $_POST['action'];

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = $_POST['uid'];

        if($action == "addsurveysecurity")
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Add user")."</div>\n";
            $addsummary .= "<div class=\"messagebox ui-corner-all\">\n";

            $result = Survey::model()->findAll('sid = :sid AND owner_id = :owner_id AND owner_id != :postuserid',array(':sid' => $surveyid, ':owner_id' => Yii::app()->session['loginID'], ':postuserid' => $postuserid));
            if( Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'create')
                &&  in_array($postuserid,getUserList('onlyuidarray'))
                )
            {

                if($postuserid > 0){

                    $isrresult = Permission::model()->insertSomeRecords(array('entity_id' => $surveyid, 'entity'=>'survey', 'uid' => $postuserid, 'permission' => 'survey', 'read_p' => 1));

                    if($isrresult)
                    {

                        $addsummary .= "<div class=\"successheader\">".$clang->gT("User added.")."</div>\n";
                        $addsummary .= "<br />"
                        .CHtml::form(array("admin/surveypermission/sa/set/surveyid/{$surveyid}"), 'post')
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
                $this->getController()->error('Access denied');
            }

            $addsummary .= "</div>\n";

            $aViewUrls['output'] = $addsummary;
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

    /**
    * surveypermission::set()
    * Function responsible to set permissions to a user/usergroup.
    * @param mixed $surveyid
    * @return void
    */
    function set($surveyid)
    {
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();

        $action = $_POST['action'];

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('adminimageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : null;
        $postusergroupid = !empty($_POST['ugid']) ? $_POST['ugid'] : null;
        if($action == "setsurveysecurity")
        {
            if ( (!Permission::model()->hasGlobalPermission('superadmin','read') && Yii::app()->user->getId()==$postuserid) // User can not change own security (except superadmin)
                || !in_array($postuserid,getUserList('onlyuidarray')) // User can not set user security if it can not see it
               )
            {
                $this->getController()->error('Access denied');
            }
        }
        elseif( $action == "setusergroupsurveysecurity" )
        {
            if ( !Permission::model()->hasGlobalPermission('superadmin','read') && !in_array($postusergroupid,getUserGroupList(null, 'simplegidarray')) ) // User can not change own security (except for superadmin ?)
            {
                $this->getController()->error('Access denied');
            }
        }
        else
        {
            Yii::app()->request->redirect(Yii::app()->getController()->createUrl('admin/surveypermission/sa/view', array('surveyid'=>$surveyid)));
            //$this->getController()->error('Unknow action');
        }

        if( Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'update') )
        {
            App()->getClientScript()->registerPackage('jquery-tablesorter');
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "surveypermissions.js");
            if ($action == "setsurveysecurity")
            {
                $query = "select users_name from {{users}} where uid=:uid";
                $resrow = Yii::app()->db->createCommand($query)->bindParam(":uid", $postuserid, PDO::PARAM_INT)->queryRow();
                $sUsername=$resrow['users_name'];
                $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for user %s"),"<span style='font-style:italic'>".$sUsername."</span>")."</div>";
            }
            else
            {
                $resrow = UserGroup::model()->find('ugid = :ugid',array(':ugid' => $postusergroupid));
                $sUsergroupName=$resrow['name'];
                $usersummary = "<div class='header ui-widget-header'>".sprintf($clang->gT("Edit survey permissions for group %s"),"<span style='font-style:italic'>".$sUsergroupName."</span>")."</div>";
            }
            $usersummary .= "<br />"
            .CHtml::form(array("admin/surveypermission/sa/surveyright/surveyid/{$surveyid}"), 'post')
            . "<table style='margin:0 auto;' class='usersurveypermissions'><thead>\n";

            $usersummary .= ""
            . "<tr><th></th><th>".$clang->gT("Permission")."</th>\n"
            . "<th><input type='button' id='btnToggleAdvanced' value='<<' /></th>\n"
            . "<th class='extended'>".$clang->gT("Create")."</th>\n"
            . "<th class='extended'>".$clang->gT("View/read")."</th>\n"
            . "<th class='extended'>".$clang->gT("Update")."</th>\n"
            . "<th class='extended'>".$clang->gT("Delete")."</th>\n"
            . "<th class='extended'>".$clang->gT("Import")."</th>\n"
            . "<th class='extended'>".$clang->gT("Export")."</th>\n"
            . "</tr></thead>\n";

            //content

            $aBasePermissions=Permission::model()->getSurveyBasePermissions();

            $oddcolumn=false;
            foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions)
            {
                $oddcolumn=!$oddcolumn;
                $usersummary .= "<tr><td><img src='{$imageurl}{$aCRUDPermissions['img']}_30.png' alt='' title='{$aCRUDPermissions['description']}'/></td>";
                $usersummary .= "<td>{$aCRUDPermissions['title']}</td>";
                $usersummary .= "<td ><input type=\"checkbox\"  class=\"markrow\" name='all_{$sPermissionKey}' /></td>";
                foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
                {
                    if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;
                    $usersummary .= "<td class='extended'>";

                    if ($CRUDValue)
                    {
                        if (!($sPermissionKey=='survey' && $sCRUDKey=='read'))
                        {
                            $usersummary .= "<input type=\"checkbox\"  class=\"checkboxbtn\" name='perm_{$sPermissionKey}_{$sCRUDKey}' ";
                            if($action=='setsurveysecurity' && Permission::model()->hasSurveyPermission( $surveyid,$sPermissionKey,$sCRUDKey,$postuserid)) {
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

            $aViewUrls['output'] = $usersummary;
        }
        else
        {
            $this->getController()->error('Access denied');
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

    /**
    * surveypermission::delete()
    * Function responsible to delete a user/usergroup.
    * @param mixed $surveyid
    * @return void
    */
    function delete($surveyid)
    {

        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();

        $action = $_POST['action'];

        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : false;
        $postusergroupid = !empty($_POST['gid']) ? $_POST['gid'] : false;// Not used

        if($postuserid && !in_array($postuserid,getUserList('onlyuidarray')))
        {
            $this->getController()->error('Access denied');
        }
        elseif( $postusergroupid &&  !in_array($postusergroupid,getUserList('onlyuidarray')))
        {
            $this->getController()->error('Access denied');
        }

        if($action == "delsurveysecurity")
        {
            $addsummary = "<div class=\"header\">".$clang->gT("Deleting User")."</div>\n";
            $addsummary .= "<div class=\"messagebox\">\n";

            if( Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'delete') )
            {
                if (isset($postuserid))
                {
                    $dbresult = Permission::model()->deleteAll('uid = :uid AND entity_id = :sid AND entity = :entity',array(':uid' => $postuserid, ':sid' => $surveyid, ':entity' => 'survey'));
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
                $this->getController()->error('Access denied');
            }
            $addsummary .= "</div>\n";

            $aViewUrls['output'] = $addsummary;
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

    /**
    * surveypermission::surveyright()
    * Function responsible to process setting of permission of a user/usergroup.
    * @param mixed $surveyid
    * @return void
    */
    function surveyright($surveyid)
    {
        $aData['surveyid'] = $surveyid = sanitize_int($surveyid);
        $aViewUrls = array();

        $action = $_POST['action'];
        $clang = Yii::app()->lang;
        $imageurl = Yii::app()->getConfig('imageurl');
        $postuserid = !empty($_POST['uid']) ? $_POST['uid'] : false;
        $postusergroupid = !empty($_POST['ugid']) ? $_POST['ugid'] : false;

        if($postuserid && !in_array($postuserid,getUserList('onlyuidarray')))
        {
            $this->getController()->error('Access denied');
        }
        elseif( $postusergroupid &&  !in_array($postusergroupid,getUserGroupList(null, 'simplegidarray')))
        {
            $this->getController()->error('Access denied');
        }

        if ($action == "surveyrights" && Permission::model()->hasSurveyPermission($surveyid, 'surveysecurity', 'update'))
        {
            $addsummary = "<div class='header ui-widget-header'>".$clang->gT("Edit survey permissions")."</div>\n";
            $addsummary .= "<div class='messagebox ui-corner-all'>\n";
            $where = ' ';
            if($postuserid){
                if (!Permission::model()->hasGlobalPermission('superadmin','read'))
                {
                    $where .= "sid = :surveyid AND owner_id != :postuserid AND owner_id = :owner_id";
                    $resrow = Survey::model()->find($where,array(':surveyid' => $surveyid, ':owner_id' => Yii::app()->session['loginID'], ':postuserid' => $postuserid));
                }
            }
            else{
                $where .= "sid = :sid";
                $resrow = Survey::model()->find($where,array(':sid' => $surveyid));
                $iOwnerID=$resrow['owner_id'];
            }

            $aBaseSurveyPermissions = Permission::model()->getSurveyBasePermissions();
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
                $oResult = UserInGroup::model()->findAll('ugid = :ugid AND uid <> :uid AND uid <> :iOwnerID',array(':ugid' => $postusergroupid, ':uid' => Yii::app()->session['loginID'], ':iOwnerID' => $iOwnerID));
                if(count($oResult) > 0)
                {
                    foreach ($oResult as $aRow)
                    {
                        Permission::model()->setPermissions($aRow->uid, $surveyid, 'survey', $aPermissions);
                    }
                    $addsummary .= "<div class=\"successheader\">".$clang->gT("Survey permissions for all users in this group were successfully updated.")."</div>\n";
                }
            }
            else
            {
                if (Permission::model()->setPermissions($postuserid, $surveyid, 'survey', $aPermissions))
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
            $aViewUrls['output'] = $addsummary;
        }
        else
        {
            $this->getController()->error('Access denied');
        }

        $this->_renderWrappedTemplate('authentication', $aViewUrls, $aData);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerPackage('jquery-superfish');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
