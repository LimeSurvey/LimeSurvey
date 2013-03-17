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
 *	$Id$
 */

function subval_sort($a, $subkey, $order)
{
    $b = array();
    $c = array();
    foreach ($a as $k => $v)
    {
        $b[$k] = strtolower($v[$subkey]);
    }
    if ($order == "asc")
    {
        asort($b, SORT_REGULAR);
    }
    else
    {
        arsort($b, SORT_REGULAR);
    }
    foreach ($b as $key => $val)
    {
        $c[] = $a[$key];
    }
    return $c;
}


/*
 * This is the main controller for Participants Panel
 */
class participantsaction extends Survey_Common_Action
{
    public function runWithParams($params)
    {
        if (!hasGlobalPermission('USER_RIGHT_PARTICIPANT_PANEL'))
        {
            die('No permission');
        }
        parent::runWithParams($params);
    }


    
    /**
     * Loads jqGrid for the view
     * @param string $sScript Subaction
     */
    private function _loadjqGrid($sScript = '', $aData = array())
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/js/i18n/grid.locale-en.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/js/jquery.jqGrid.min.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/plugins/jquery.searchFilter.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/src/grid.celledit.js');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jqGrid/css/ui.jqgrid.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jqGrid/css/jquery.ui.datepicker.css');
        
        if (!empty($sScript))
        {
            $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . $sScript . '.js');
            $this->_renderWrappedTemplate('participants', array('participantsPanel', $sScript), $aData);
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'participants', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars'] = false;

        foreach((array) $aViewUrls as $sViewUrl)
        {
            $a_ViewUrls[] = $sViewUrl . '_view';
        }

        parent::_renderWrappedTemplate($sAction, $a_ViewUrls, $aData);
    }
    
    /**
     * Export to csv using optional search/filter
     * 
     * @param type $search
     */
    private function csvExport($search = null) {
        Yii::app()->loadHelper('export');      
        
        $attid = ParticipantAttributeNames::model()->getVisibleAttributes();
        
        //If super admin all the participants will be visible
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }

        $query = Participants::model()->getParticipants(0, 0, $attid, null, $search, $iUserID);
        if (!$query)
            return false;

        // Field names in the first row
        $fields = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
        $outputarray = array(); // The array to be passed to the export helper to be written to a csv file
        
        $outputarray[0] = $fields; //fields written to output array

        // If attribute fields are selected, add them to the output
        $queryId = Yii::app()->request->getQuery('id');
        if (!is_null($queryId) && $queryId != "null") {
            $iAttributeId = explode(",", $queryId);
             foreach ($iAttributeId as $key => $value)
            {
                $fields[] = 'a'.$value;
                $attributename = ParticipantAttributeNames::model()->getAttributeNames($value);
                $outputarray[0][] = $attributename[0]['attribute_name'];
            }
        }       
      
        $fieldKeys = array_flip($fields);
        foreach ($query as $field => $aData)
        {
            $outputarray[] = array_intersect_key($aData, $fieldKeys);
        }
        CPDBExport($outputarray, "central_" . time());
    }
    
    /**
     * Returns a string with the number of participants available for export or 0
     * 
     * @param type $search
     * @return string|0
     */
    protected function csvExportCount($search = null)
    {
        $clang = $this->getController()->lang;
        
        $attid = ParticipantAttributeNames::model()->getVisibleAttributes();
        
        //If super admin all the participants will be visible
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        

        $count = Participants::model()->getParticipantsCount($attid, $search, $iUserID);

        if ($count > 0) {
            return sprintf($clang->gT("Export %s participant(s) to CSV"), $count);
        } else {
            return $count;
        }
    }

    /**
     * Loads the view 'participantsPanel'
     */
    function index()
    {
        $iUserID = Yii::app()->session['loginID'];

        // if superadmin all the records in the cpdb will be displayed
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $iTotalRecords = Participants::model()->count();
        }
        // if not only the participants on which he has right on (shared and owned)
        else
        {
            $iTotalRecords = Participants::model()->getParticipantsOwnerCount($iUserID);
        }

        // gets the count of participants, their attributes and other such details
        $aData = array(
            'totalrecords' => $iTotalRecords,
            'owned' => Participants::model()->count('owner_uid = ' . $iUserID),
            'shared' => Participants::model()->getParticipantsSharedCount($iUserID),
            'attributecount' => ParticipantAttributeNames::model()->count(),
            'blacklisted' => Participants::model()->count('owner_uid = ' . $iUserID . ' AND blacklisted = \'Y\'')
        );

        // loads the participant panel and summary view
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'summary'), $aData);
    }

    /**
     * Loads the view 'importCSV'
     */
    function importCSV()
    {
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'importCSV'));
    }

    /**
     * Loads the view 'displayParticipants' which contains the main grid
     */
    function displayParticipants()
    {
        $lang = Yii::app()->session['adminlang'];
        // loads the survey names to be shown in add to survey
        // if user is superadmin, all survey names
        $urlSearch=Yii::app()->request->getQuery('searchurl');
        $urlSearch=!empty($urlSearch) ? "getParticipantsResults_json/search/$urlSearch" : "getParticipants_json";

        //Get list of surveys.
        //Should be all surveys owned by user (or all surveys for super admin)
        $surveys = Survey::model();
        //!!! Is this even possible to execute?
        if (empty(Yii::app()->session['USER_RIGHT_SUPERADMIN']))
            $surveys->permission(Yii::app()->user->getId());

        $aSurveyNames = $surveys->model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language'), 'owner'))->findAll();

        /* Build a list of surveys that have tokens tables */
        $tSurveyNames=array();
        foreach($aSurveyNames as $row)
        {
            $row = array_merge($row->attributes, $row->languagesettings[0]->attributes);
            $bTokenExists = tableExists('{{tokens_' . $row['sid'] . '}}');
            if ($bTokenExists) //If tokens table exists
            {
                $tSurveyNames[]=$row;
            }
        }
        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeNames::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeNames::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeNames::model()->getAllAttributesValues(),
            'surveynames' => $aSurveyNames,
            'tokensurveynames' => $tSurveyNames,
            'urlsearch' => $urlSearch
        );

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/js/i18n/grid.locale-en.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')  . 'jquery/jqGrid/js/jquery.jqGrid.min.js');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('publicstyleurl') . 'jquery.multiselect.filter.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')  . 'displayParticipants.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jqGrid/css/ui.jqgrid.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jqGrid/css/jquery.ui.datepicker.css');

        // loads the participant panel view and display participant view
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'displayParticipants'), $aData);
    }

    /**
     * Loads the view 'blacklistControl'
     */
    function blacklistControl()
    {
        $aData = array(
            'blacklistallsurveys' => Yii::app()->getConfig('blacklistallsurveys'),
            'blacklistnewsurveys' => Yii::app()->getConfig('blacklistnewsurveys'),
            'blockaddingtosurveys' => Yii::app()->getConfig('blockaddingtosurveys'),
            'hideblacklisted' => Yii::app()->getConfig('hideblacklisted'),
            'deleteblacklisted' => Yii::app()->getConfig('deleteblacklisted'),
            'allowunblacklist' => Yii::app()->getConfig('allowunblacklist')
        );
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'blacklist'), $aData);
    }

    /**
     * Loads the view 'userControl'
     */
    function userControl()
    {
        $aData = array(
            'userideditable' => Yii::app()->getConfig('userideditable')
        );

        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'userControl'), $aData);
    }

    /**
     * Loads the view 'sharePanel'
     */
    function sharePanel()
    {
        $this->_loadjqGrid('sharePanel');
    }

    /**
     * Sends the shared participant info to the share panel using JSON encoding
     * Called after the share panel grid is loaded
     * Returns the json depending on the user logged in by checking it from the session
     * @return JSON encoded string containg sharing information
     */
    function getShareInfo_json()
    {
        $aData = new stdClass();
        $aData->page = 1;

        // If super administrator all the share info in the links table will be shown
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $records = Participants::model()->getParticipantSharedAll();
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                $oShared = User::model()->getName($row['share_uid']); //for conversion of uid to human readable names
                $owner = User::model()->getName($row['owner_uid']);
                $aData->rows[$i]['id'] = $row['participant_id']."--".$row['share_uid']; //This is the unique combination per record
                $aData->rows[$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $oShared[0]['full_name'], $row['share_uid'], $owner[0]['full_name'], $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        }
        // otherwise only the shared participants by that user
        else
        {
            $records = Participants::model()->getParticipantShared(Yii::app()->session['loginID']);
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                $sharename = User::model()->getName($row['share_uid']); //for conversion of uid to human readable names
                $aData->rows[$i]['id'] = $row['participant_id'];
                $aData['rows'][$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $sharename['full_name'], $row['share_uid'], $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        }
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     */
    function editShareInfo()
    {
        $operation = Yii::app()->request->getPost('oper');
        $shareIds = Yii::app()->request->getPost('id');
        if ($operation == 'del') // If operation is delete , it will delete, otherwise edit it
        {
            ParticipantShares::model()->deleteRow($shareIds);
        }
        else
        {
            $aData = array(
                'participant_id' => Yii::app()->request->getPost('participant_id'),
                'can_edit' => Yii::app()->request->getPost('can_edit'),
                'share_uid' => Yii::app()->request->getPost('shared_uid')
            );
            ParticipantShares::model()->updateShare($aData);
        }
    }

    /**
     * Loads the view 'attributeControl'
     */
    function attributeControl()
    {
        $this->_loadjqGrid('attributeControl');
    }

    /**
     * Sends the attributes info using JSON encoding
     * Called after the Attribute management grid is loaded
     * @return JSON encoded string containg sharing information
     */
    function getAttributeInfo_json()
    {
        $clang = Yii::app()->lang;
        $page = Yii::app()->request->getPost('page');
        $limit = Yii::app()->request->getPost('rows');
    	$limit = isset($limit) ? $limit : 50; //Stop division by zero errors

        $records = ParticipantAttributeNames::model()->with('participant_attribute_names_lang')->findAll();

        $attribute_types = array(
            'DD' => $clang->gT("Drop-down list"),
            'DP' => $clang->gT("Date"),
            'TB' => $clang->gT("Text box")
        );
        
        $aData = new stdClass();
        $aData->page = $page;
        $aData->records = count($records);
        $aData->total = ceil(ParticipantAttributeNames::model()->getAttributes(true) / $limit);

        $i = 0;
        foreach($records as $row) { //Iterate through each attribute
            $thisname="";
            foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                if($thisname=="") {$thisname=$names->attribute_name;} //Choose the first item by default
                if($names->lang == Yii::app()->session['adminlang']) {$thisname=$names->attribute_name;} //Override the default with the admin language version if found
            }
            $aData->rows[$i]['id'] = $row->attribute_id;
            $aData->rows[$i]['cell'] = array('', $thisname, $attribute_types[$row->attribute_type], $row->visible);
            $i++;
        }

        
        echo ls_json_encode($aData);
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     */
    function editAttributeInfo()
    {
        $clang = Yii::app()->lang;
        $operation = Yii::app()->request->getPost('oper');

        if ($operation == 'del' && Yii::app()->request->getPost('id'))
        {
            $aAttributeIds = (array) explode(',', Yii::app()->request->getPost('id'));
            $aAttributeIds = array_map('trim', $aAttributeIds);
            $aAttributeIds = array_map('intval', $aAttributeIds);

            foreach ($aAttributeIds as $iAttributeId)
            {
                ParticipantAttributeNames::model()->delAttribute($iAttributeId);
            }
        }
        elseif ($operation == 'add' && Yii::app()->request->getPost('attribute_name'))
        {
            $aData = array(
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible') == 'TRUE' ? 'TRUE' : 'FALSE'
            );
            echo ParticipantAttributeNames::model()->storeAttribute($aData);
        }
        elseif ($operation == 'edit' && Yii::app()->request->getPost('id'))
        {
            $aData = array(
                'attribute_id' => Yii::app()->request->getPost('id'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible') == 'TRUE' ? 'TRUE' : 'FALSE'
            );
            ParticipantAttributeNames::model()->saveAttribute($aData);
            $clang->eT("Attribute display setting updated");
        }

    }

    /**
     * Takes the delete call from the display participants and take appropriate action depending on the condition
     */
    function delParticipant()
    {
        $selectoption = Yii::app()->request->getPost('selectedoption');
        $iParticipantId = Yii::app()->request->getPost('participant_id');

        //echo $selectoption." -- ".$iParticipantId."<br />"; die();

        // Deletes from participants only
        if ($selectoption == 'po')
        {
            Participants::model()->deleteParticipants($iParticipantId);
        }
        // Deletes from central and token table
        elseif ($selectoption == 'ptt')
        {
            Participants::model()->deleteParticipantToken($iParticipantId);
        }
        // Deletes from central , token and assosiated responses as well
        elseif ($selectoption == 'ptta')
        {
            Participants::model()->deleteParticipantTokenAnswer($iParticipantId);
        }
    }

    /**
     * Resposible for editing data on the jqGrid
     */
    function editParticipant()
    {
        $operation = Yii::app()->request->getPost('oper');

        //In case the uid is not editable, then user id is not posted and hence the current user is added in the uid
        if (Yii::app()->request->getPost('owner_uid') == '')
        {
            $oid = Yii::app()->session['loginID'];
        }
        //otherwise the one which is posted is added
        else
        {
            $oid = Yii::app()->request->getPost('owner_uid');
        }
        if (Yii::app()->request->getPost('language') == '')
        {
            $lang = Yii::app()->session['adminlang'];
        }
        else
        {
            $lang = Yii::app()->request->getPost('language');
        }

        // if edit it will update the row
        if ($operation == 'edit')
        {
            $aData = array(
                'participant_id' => Yii::app()->request->getPost('id'),
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted'),
                'owner_uid' => $oid
            );
            Participants::model()->updateRow($aData);
        }
        // if add it will insert a new row
        elseif ($operation == 'add')
        {
            $uuid = $this->gen_uuid();
            $aData = array(
                'participant_id' => $uuid,
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted'),
                'owner_uid' => $oid
            );
            Participants::model()->insertParticipant($aData);
        }
    }

    /**
     * Stores the user control setting to the database
     */
    function storeUserControlValues()
    {
        if ($find = Settings_global::model()->findByPk('userideditable'))
        {
            Settings_global::model()->updateByPk('userideditable', array('stg_value'=>Yii::app()->request->getPost('userideditable')));
        }
        else
        {
            $stg = new Settings_global;
            $stg ->stg_name='userideditable';
            $stg ->stg_value=Yii::app()->request->getPost('userideditable');
            $stg->save();
        }
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl('admin/participants/sa/userControl'));
    }

    /**
     * Stores the blacklist setting to the database
     */
    function storeBlacklistValues()
    {
        $values = Array('blacklistallsurveys', 'blacklistnewsurveys', 'blockaddingtosurveys', 'hideblacklisted', 'deleteblacklisted', 'allowunblacklist', 'userideditable');
        foreach ($values as $value)
        {
            if ($find = Settings_global::model()->findByPk($value))
            {
                Settings_global::model()->updateByPk($value, array('stg_value'=>Yii::app()->request->getPost($value)));
            }
            else
            {
                $stg = new Settings_global;
                $stg ->stg_name=$value;
                $stg ->stg_value=Yii::app()->request->getPost($value);
                $stg->save();
            }
        }
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl('admin/participants/sa/blacklistControl'));
    }

    /**
     * Receives an ajax call containing the participant id in the fourth segment of the url
     * Supplies list of survey links - surveys of which this participant is on the tokens table
     * URL: [localurl]/limesurvey/admin/participants/getSurveyInfo_json/pid/[participant_id]
     * RETURNS: json data containing linked survey information (Survey name, survey id, token_id and date_added)
     */
    function getSurveyInfo_json()
    {
        $participantid = Yii::app()->request->getQuery('pid');
        $records = Survey_links::model()->findAllByAttributes((array('participant_id' => $participantid)));
        $aData = new stdClass();
        $aData->page = 1;
        $aData->records = count($records);
        $aData->total = ceil($aData->records / 10);
        $i = 0;
        foreach ($records as $row)
        {
            $oSurvey=Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->findByAttributes(array('sid' => $row['survey_id']));            
            foreach($oSurvey->languagesettings as $oLanguageSetting)
            {
                $surveyname= $oLanguageSetting->surveyls_title;
            }
            $surveylink = "";
            /* Check permissions of each survey before creating a link*/
            if (!hasSurveyPermission($row['survey_id'], 'tokens', 'read'))
            {
                $surveylink = $row['survey_id'];
            } else
            {
                $surveylink = '<a href=' . Yii::app()->getController()->createUrl("/admin/tokens/sa/browse/surveyid/{$row['survey_id']}") . '>' . $row['survey_id'].'</a>';
            }
            $aData->rows[$i]['cell'] = array($surveyname, $surveylink, $row['token_id'], $row['date_created'], $row['date_invited'], $row['date_completed']);
            $i++;
        }

        echo ls_json_encode($aData);
    }

    /**
     * Returns the count of the participants in the CSV and show it in the title of the modal box
     * This is to give the user the hint to see the number of participants he is exporting
     */
    function exporttocsvcount()
    {
        $searchconditionurl = Yii::app()->request->getPost('searchcondition');
        $searchcondition = basename($searchconditionurl);
        
        if ($searchcondition != 'getParticipants_json') // if there is a search condition then only the participants that match the search criteria are counted
        {
            $condition = explode("||", $searchcondition);
            $search = Participants::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search = null;
        }
        
        echo $this->csvExportCount($search);
    }

    /**
     * Outputs the count of participants when using the export all button on the top
     */
    function exporttocsvcountAll()
    {
        echo $this->csvExportCount();
    }

    /**
     * Responsible to export all the participants in the central table
     */
    function exporttocsvAll()
    {
        $this->csvExport(); // no search
    }

    /**
     * Similar to export to all message where it counts the number to participants to be copied
     * and echo them to be displayed in modal box header
     */
    function getaddtosurveymsg()
    {
        $searchcondition = basename(Yii::app()->request->getPost('searchcondition'));

        // If there is a search condition in the url of the jqGrid
        if ($searchcondition != 'getParticipants_json')
        {
            $participantid = "";
            $condition = explode("||", $searchcondition);

            $query = Participants::model()->getParticipantsSearchMultiple($condition, 0, 0);

            printf( $this->getController()->lang->gT("%s participant(s) are to be copied "), count($query));
        }
        // if there is no search condition the participants will be counted on the basis of who is logged in
        else
        {
            if (Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be visible
            {
                $count = Participants::model()->getParticipantsCountWithoutLimit();
            }
            else
            {
                $query = Participants::model()->getParticipantsOwner(Yii::app()->session['loginID']);
                $count = count($query);
            }

            printf($this->getController()->lang->gT("%s participant(s) are to be copied "), $count);
        }
    }

    /**
     * Gets the ids of participants to be copied to the individual survey
     */
    function getSearchIDs()
    {
        $searchcondition = basename(Yii::app()->request->getPost('searchcondition')); // get the search condition from the URL
        /* a search contains posted data inside $_POST['searchcondition'].
        * Each seperate query is made up of 3 fields, seperated by double-pipes ("|")
        * EG: fname||eq||jason||lname||ct||c
        *
        */
        if ($searchcondition != 'getParticipants_json') // if there is a search condition present
        {
            $participantid = "";
            $condition = explode("||", $searchcondition);  // explode the condition to the array
            $query = Participants::model()->getParticipantsSearchMultiple($condition, 0, 0);

            foreach ($query as $key => $value)
            {
                if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
                {
                    $participantid .= "," . $value['participant_id']; // combine the participant id's in an string
                } else
                {
                    if(Participants::model()->is_owner($value['participant_id']))
                    {
                        $participantid .= "," . $value['participant_id']; // combine the participant id's in an string
                    }
                }
            }
            echo $participantid; //echo the participant id's
        }
        else// if no search condition
        {
            $participantid = ""; // initiallise the participant id to blank
            if (Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be visible
            {
                $query = Participants::model()->getParticipantsWithoutLimit(); // get all the participant id if it is a super admin
            }
            else // get participants on which the user has right on
            {
                $query = Participants::model()->getParticipantsOwner(Yii::app()->session['loginID']);
            }

            foreach ($query as $key => $value)
            {
                $participantid = $participantid . "," . $value['participant_id']; // combine the participant id's in an string
            }
            echo $participantid; //echo the participant id's
        }
    }

    /**
     * Responsible for reading the CSV file line by line, check for duplicate participants
     * invalid participants and invalid attributes and copy them to the central table
     * Also responsible for creation of new attribute and mapping of old attribute to attribute in csv
     */
    function exporttocsv()
    {
        $searchconditionurl = Yii::app()->request->getPost('searchcondition');
        $searchcondition = basename($searchconditionurl);
        
        if ($searchcondition != 'getParticipants_json') // if there is a search condition then only the participants that match the search criteria are counted
        {
            $condition = explode("||", $searchcondition);
            $search = Participants::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search = null;
        }
        
        $this->csvExport($search);
    }

    /**
     * Equal to getParticipants_json() but now with a search
     */
    function getParticipantsResults_json()
    {
        $searchcondition = Yii::app()->request->getpost('searchcondition');
        $finalcondition = array();
        $condition = explode("||", $searchcondition);
        $search = Participants::model()->getParticipantsSearchMultipleCondition($condition);
        return $this->getParticipants_json($search);
    }

	/*
	   * Sends the data in JSON format extracted from the database to be displayed using the jqGrid
	*/
    function getParticipants_json($search = null)
    {
        $page = Yii::app()->request->getPost('page');
        $limit = Yii::app()->request->getPost('rows');
    	$limit = isset($limit) ? $limit : 50; //Stop division by zero errors

        $attid = ParticipantAttributeNames::model()->getVisibleAttributes();
        $participantfields = array('participant_id', 'can_edit', 'firstname', 'lastname', 'email', 'blacklisted', 'survey', 'language', 'owner_uid');
        foreach ($attid as $key => $value)
        {
            array_push($participantfields, $value['attribute_id']);
        }
        $sidx = Yii::app()->request->getPost('sidx');
        $sidx = !empty($sidx) ? $sidx : "lastname";
        $sord = Yii::app()->request->getPost('sord');
        $sord = !empty($sord) ? $sord : "asc";
        $order = $sidx. " ". $sord;
        
        $aData = new stdClass;
        
        //If super admin all the participants will be visible
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        $aData->records = Participants::model()->getParticipantsCount($attid, $search, $iUserID);
        $aData->total = ceil($aData->records / $limit);
        if ($page>$aData->total) {
            $page = $aData->total;
        }
        $aData->page = $page;
        $records = Participants::model()->getParticipants($page, $limit,$attid, $order, $search, $iUserID);
        
        
        $aRowToAdd=array();
        foreach ($records as $key => $row)
        {            
            if (array_key_exists('can_edit', $row)) {
                $sCanEdit = $row['can_edit'];
                if (is_null($sCanEdit)) {
                    $sCanEdit = 'true';
                }
            } else {
                // Super admin
                $sCanEdit = "true";
            }
            if (trim($row['ownername'])=='') {
                $row['ownername']=$row['username'];   
            }
            $aRowToAdd['cell'] = array($row['participant_id'], $sCanEdit, $row['firstname'], $row['lastname'], $row['email'], $row['blacklisted'], $row['survey'], $row['language'], $row['ownername']);
            $aRowToAdd['id'] = $row['participant_id'];
            unset($row['participant_id'], $row['firstname'], $row['lastname'], $row['email'], $row['blacklisted'], $row['language'],$row['ownername'],$row['owner_uid'], $row['can_edit'], $row['survey'], $row['username']);
            foreach($row as $key=>$attvalue)
            {
              $aRowToAdd['cell'][] = $attvalue;
            }
            
            $aData->rows[] = $aRowToAdd;
        }

        echo ls_json_encode($aData);
    }

    /*
     * Fetches the attributes of a participant to be displayed in the attribute subgrid
     */
    function getAttribute_json()
    {
        $iParticipantId = Yii::app()->request->getQuery('pid');
        $records = ParticipantAttributeNames::model()->getParticipantVisibleAttribute($iParticipantId);
        //$getallattributes = ParticipantAttributeNames::model()->with('participant_attribute_names_lang')->findAll();
        $records = subval_sort($records, "attribute_name", "asc");

        $i = 0;

        $doneattributes = array(); //If the user has any actual attribute values, they'll be stored here

        /* Iterate through each attribute owned by this user */
        foreach ($records as $row)
        {
            $outputs[$i] = array("", $row['participant_id']."_".$row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], $row['value']);
            /* Collect allowed values for a DropDown attribute */
            if ($row['attribute_type'] == "DD")
            {
                $attvalues = ParticipantAttributeNames::model()->getAttributesValues($row['attribute_id']);
                if (!empty($attvalues))
                {
                    $attval = "";
                    foreach ($attvalues as $val)
                    {
                        $attval .= $val['value'] . ":" . $val['value'];
                        $attval .= ";";
                    }
                    $attval = substr($attval, 0, -1);
                    array_push($outputs[$i], $attval);
                }
                else
                {
                    array_push($outputs[$i], "");
                }
            }
            else
            {
                array_push($outputs[$i], "");
            }
            array_push($doneattributes, $row['attribute_id']);
            $i++;
        }

        /* Build a list of attribute names for which this user has NO values stored, keep it in $attributenotdone */
        $attributenotdone=array();
        /* The user has NO values stored against any attribute */
        if (count($doneattributes) == 0)
        {
            $attributenotdone = ParticipantAttributeNames::model()->getAttributes();
        }
        /* The user has SOME values stored against attributes */
        else
        {
            $attributenotdone = ParticipantAttributeNames::model()->getnotaddedAttributes($doneattributes);
        }

        /* Go through the empty attributes and build an entry in the output for them */
        foreach ($attributenotdone as $row)
        {
            $outputs[$i] = array("", $iParticipantId."_".$row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], "");
            if ($row['attribute_type'] == "DD")
            {
                $attvalues = ParticipantAttributeNames::model()->getAttributesValues($row['attribute_id']);
                if (!empty($attvalues))
                {
                    $attval = "";
                    foreach ($attvalues as $val)
                    {
                        $attval .= $val['value'] . ":" . $val['value'];
                        $attval .= ";";
                    }
                    $attval = substr($attval, 0, -1);
                    array_push($outputs[$i], $attval);
                }
                else
                {
                    array_push($outputs[$i], "");
                }
            }
            else
            {
                array_push($outputs[$i], "");
            }
            $i++;
        }
        $outputs=subval_sort($outputs, 3, "asc");

        $aData = new stdClass();
        $aData->page = 1;
        $aData->rows[0]['id'] = $iParticipantId;
        $aData->rows[0]['cell'] = array();
        $aData->records = count($outputs);
        $aData->total = ceil($aData->records / 10);
        foreach($outputs as $key=>$output) {
            $aData->rows[$key]['id']=$output[1];
            $aData->rows[$key]['cell']=$output;
        }
        /* TODO: It'd be nice to do a natural sort on the attribute list at some point.
                 Currently they're returned in order of attributes WITH values, then WITHOUT values
         */

        echo ls_json_encode($aData);
    }

    /*
     * Gets the data from the form for add participants and pass it to the participants model
     */
    function storeParticipants()
    {
        $aData = array('participant_id' => uniqid(),
            'firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => Yii::app()->request->getPost('email'),
            'language' => Yii::app()->request->getPost('language'),
            'blacklisted' => Yii::app()->request->getPost('blacklisted'),
            'owner_uid' => Yii::app()->request->getPost('owner_uid'));

        Participants::model()->insertParticipant($aData);
    }

    /*
     * Responsible for showing the additional attribute for central database
     */
    function viewAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = array(
            'attributes' => ParticipantAttributeNames::model()->getAttribute($iAttributeId),
            'attributenames' => ParticipantAttributeNames::model()->getAttributeNames($iAttributeId),
            'attributevalues' => ParticipantAttributeNames::model()->getAttributesValues($iAttributeId)
        );

        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')       . 'participants.css');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')       . 'viewAttribute.css');

        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'viewAttribute'), $aData);
    }

    /*
     * Responsible for saving the additional attribute. It iterates through all the new attributes added dynamically
     * and iterates through them
     */
    function saveAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = array(
            'attribute_id' => $iAttributeId,
            'attribute_type' => Yii::app()->request->getPost('attribute_type'),
            'visible' => Yii::app()->request->getPost('visible')
        );
        ParticipantAttributeNames::model()->saveAttribute($aData);

        foreach ($_POST as $key => $value)
        {
            // check for language code in the post variables this is a hack as the only way to check for language data
            if (strlen($key) == 2)
            {
                $langdata = array(
                    'attribute_id' => $iAttributeId,
                    'attribute_name' => $value,
                    'lang' => $key
                );

                ParticipantAttributeNames::model()->saveAttributeLanguages($langdata);
            }
        }
        if (Yii::app()->request->getPost('langdata'))
        {
            $langdata = array(
                'attribute_id' => $iAttributeId,
                'attribute_name' => Yii::app()->request->getPost('attname'),
                'lang' => Yii::app()->request->getPost('langdata')
            );

            ParticipantAttributeNames::model()->saveAttributeLanguages($langdata);
        }
        /* Create new attribute value */
        if (Yii::app()->request->getPost('attribute_value_name_1') || Yii::app()->request->getPost('attribute_value_name_1') == "0")
        {
            $i = 1;
            $attvaluename = 'attribute_value_name_' . $i;
            while (array_key_exists($attvaluename, $_POST) && $_POST[$attvaluename] != "")
            {
                if ($_POST[$attvaluename] != "")
                {
                    $aDatavalues[$i] = array(
                        'attribute_id' => $iAttributeId,
                        'value' => Yii::app()->request->getPost($attvaluename)
                    );
                }
                $attvaluename = 'attribute_value_name_' . ++$i;
            };
            ParticipantAttributeNames::model()->storeAttributeValues($aDatavalues);
        }
        /* Save updated attribute values */
        if (Yii::app()->request->getPost('editbox') || Yii::app()->request->getPost('editbox')=="0")
        {
            $editattvalue = array(
                'attribute_id' => $iAttributeId,
                'value_id' => Yii::app()->request->getPost('value_id'),
                'value' => Yii::app()->request->getPost('editbox')
            );
            ParticipantAttributeNames::model()->saveAttributeValue($editattvalue);
        }
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl('admin/participants/sa/attributeControl'));
    }

    /*
     * Responsible for deleting the additional attribute values in case of drop down.
     */
    function delAttributeValues()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $iValueId = Yii::app()->request->getQuery('vid');
        ParticipantAttributeNames::model()->delAttributeValues($iAttributeId, $iValueId);
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl('/admin/participants/sa/viewAttribute/aid/' . $iAttributeId));
    }

    /*
     * Responsible for editing the additional attributes values
     */
    function editAttributevalue()
    {
        if (Yii::app()->request->getPost('oper') == "edit" && (Yii::app()->request->getPost('attvalue') || Yii::app()->request->getPost('attvalue')=="0"))
        {
        	$pid = explode('_',Yii::app()->request->getPost('participant_id'));
            $iAttributeId =  Yii::app()->request->getPost('attid');
            $aData = array('participant_id' => $pid[0], 'attribute_id' => $iAttributeId, 'value' => Yii::app()->request->getPost('attvalue'));
            ParticipantAttributeNames::model()->editParticipantAttributeValue($aData);
        }
    }

    function attributeMapCSV()
    {

        $clang = $this->getController()->lang;
        $sRandomFileName=randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $sRandomFileName;

        $aPathinfo = pathinfo($_FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        if (strtolower($sExtension)=='csv')
        {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
            $errorinupload = '';
            $filterblankemails = Yii::app()->request->getPost('filterbea');
        }
        else
        {
            $templateData['error_msg'] = sprintf($clang->gT("This is not a .csv file."), Yii::app()->getConfig('tempdir'));
            $errorinupload = array('error' => $this->upload->display_errors());
            Yii::app()->session['summary'] = array('errorinupload' => $errorinupload);
            $this->_renderWrappedTemplate('participants', array('participantsPanel', 'uploadSummary'));
        }
        

        if (!$bMoveFileResult)
        {
            $templateData['error_msg'] = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), Yii::app()->getConfig('tempdir'));
            $errorinupload = array('error' => $this->upload->display_errors());
            Yii::app()->session['summary'] = array('errorinupload' => $errorinupload);
            $this->_renderWrappedTemplate('participants', array('participantsPanel', 'uploadSummary'));
        }
        else
        {
            $aData = array('upload_data' => $_FILES['the_file']);
            $sFileName = $_FILES['the_file']['name'];

            $regularfields = array('firstname', 'participant_id', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
            $csvread = fopen($sFilePath, 'r');

            $seperator = Yii::app()->request->getPost('seperatorused');
            $firstline = fgetcsv($csvread, 1000, ',');
            $selectedcsvfields = array();
            foreach ($firstline as $key => $value)
            {
                $testvalue = preg_replace('/[^(\x20-\x7F)]*/','', $value); //Remove invalid characters from string
                if (!in_array(strtolower($testvalue), $regularfields))
                {
                    array_push($selectedcsvfields, $value);
                }
                $fieldlist[]=$value;
            }
            $linecount = count(file($sFilePath));

            $attributes = ParticipantAttributeNames::model()->model()->getAttributes();
            $aData = array(
                'attributes' => $attributes,
                'firstline' => $selectedcsvfields,
                'fullfilepath' => $sRandomFileName,
                'linecount' => $linecount - 1,
                'filterbea' => $filterblankemails,
                'participant_id_exists' => in_array('participant_id', $fieldlist)
            );
            $this->_renderWrappedTemplate('participants', 'attributeMapCSV', $aData);
        }
    }

    /*
     * Uploads the file to the server and process it for valid enteries and import them into database
     */
    function uploadCSV()
    {
        unset(Yii::app()->session['summary']);
        $characterset = Yii::app()->request->getPost('characterset');
        $seperator = Yii::app()->request->getPost('seperatorused');
        $newarray = Yii::app()->request->getPost('newarray');
        $mappedarray = Yii::app()->request->getPost('mappedarray');
        $filterblankemails = Yii::app()->request->getPost('filterbea');
        $overwrite = Yii::app()->request->getPost('overwrite');
        $sFilePath = Yii::app()->getConfig('tempdir') . '/' . basename(Yii::app()->request->getPost('fullfilepath'));
        $errorinupload = "";
        $recordcount = 0;
        $mandatory = 0;
        $mincriteria = 0;
        $imported = 0;
        $dupcount = 0;
        $overwritten = 0;
        $dupreason="nameemail"; //Default duplicate comparison method
        $duplicatelist = array();
        $invalidemaillist = array();
        $invalidformatlist = array();
        $invalidattribute = array();
        $invalidparticipantid = array();

        /* Adjust system settings to read file with MAC line endings */
        @ini_set('auto_detect_line_endings', true);
        /* Open the uploaded file into an array */
        $tokenlistarray = file($sFilePath);

        // open it and trim the endings
        $separator = Yii::app()->request->getPost('seperatorused');
        $uploadcharset = Yii::app()->request->getPost('characterset');
        /* The $newarray contains a list of fields that will be used
           to create new attributes */
		if (!empty($newarray))
        {
			/* Create a new entry in the lime_participant_attribute_names table,
			   and it's associated lime_participant_attribute_names_lang table
			   for each NEW attribute being created in this import process */
            foreach ($newarray as $key => $value)
            {
                $aData = array('attribute_type' => 'TB', 'attribute_name' => $value, 'visible' => 'FALSE');
                $insertid = ParticipantAttributeNames::model()->storeAttributeCSV($aData);
            	/* Keep a record of the attribute_id for this new attribute
            	   in the $mappedarray string. For example, if the new attribute
            	   has attribute_id of 35 and is called "gender",
            	   $mappedarray['35']='gender' */
				$mappedarray[$insertid] = $value;
            }
        }
        if (!isset($uploadcharset))
        {
            $uploadcharset = 'auto';
        }
        foreach ($tokenlistarray as $buffer) //Iterate through the CSV file line by line
        {
        	$buffer = @mb_convert_encoding($buffer, "UTF-8", $uploadcharset);
            $firstname = "";
            $lastname = "";
            $email = "";
            $language = "";
        	if ($recordcount == 0) {
            	//The first time we iterate through the file we look at the very
            	//first line, which contains field names, not values to import
                // Pick apart the first line
                $buffer = removeBOM($buffer);
                $attrid = ParticipantAttributeNames::model()->getAttributeID();
                $allowedfieldnames = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted');
                if (!empty($mappedarray))
                {
                    foreach ($mappedarray as $key => $value)
                    {
                        array_push($allowedfieldnames, strtolower($value));
                    }
                }
                //For Attributes
                switch ($separator)
                {
                    case 'comma':
                        $separator = ',';
                        break;
                    case 'semicolon':
                        $separator = ';';
                        break;
                    default:
                        $comma = substr_count($buffer, ',');
                        $semicolon = substr_count($buffer, ';');
                        if ($semicolon > $comma)
                            $separator = ';'; else
                            $separator = ',';
                }
                $firstline = convertCSVRowToArray($buffer, $separator, '"');
                $firstline = array_map('trim', $firstline);
                $ignoredcolumns = array();
                //now check the first line for invalid fields
                foreach ($firstline as $index => $fieldname)
                {
                    $firstline[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $fieldname);
                    $fieldname = $firstline[$index];
                    if (!in_array(strtolower($fieldname), $allowedfieldnames) && !in_array($fieldname,$mappedarray))
                    {
                        $ignoredcolumns[] = $fieldname;
                    } else {
                        $firstline[$index] = strtolower($fieldname);
                    }
                }
                if ((!in_array('firstname', $firstline) && !in_array('lastname', $firstline) && !in_array('email', $firstline)) && !in_array('participant_id', $firstline))
                {
                    $recordcount = count($tokenlistarray);
                    break;
                }
        	} else {
                // After looking at the first line, we now import the actual values
                $line = convertCSVRowToArray($buffer, $separator, '"');

                if (count($firstline) != count($line))
                {
                    $invalidformatlist[] = $recordcount;
                    continue;
                }
                $writearray = array_combine($firstline, $line);

                //kick out ignored columns
                foreach ($ignoredcolumns as $column)
                {
                    unset($writearray[$column]);
                }
                $invalidemail = false;
                $dupfound = false;
                $thisduplicate = 0;
                $filterduplicatefields = array('firstname', 'lastname', 'email');

                //Check for duplicate participants
                $aData = array(
                         'firstname' => $writearray['firstname'],
                         'lastname' => $writearray['lastname'],
                         'email' => $writearray['email'],
                         'owner_uid' => Yii::app()->session['loginID']
                         );
            	//HACK - converting into SQL instead of doing an array search
                if(in_array('participant_id', $firstline)) {
                    $dupreason="participant_id";
                    $aData = "participant_id = ".Yii::app()->db->quoteValue($writearray['participant_id']);
                } else {
                    $dupreason="nameemail";
                    $aData = "firstname = ".Yii::app()->db->quoteValue($writearray['firstname'])." AND lastname = ".Yii::app()->db->quoteValue($writearray['lastname'])." AND email = ".Yii::app()->db->quoteValue($writearray['email'])." AND owner_uid = '".Yii::app()->session['loginID']."'";
                }
                //End of HACK
				$aData = Participants::model()->checkforDuplicate($aData, "participant_id");
                if ($aData !== false) {
                    $thisduplicate = 1;
                    $dupcount++;
                    if($overwrite=="true")
                    {
                        //Although this person already exists, we want to update the mapped attribute values
                        if (!empty($mappedarray)) {
                            //The mapped array contains the attributes we are
                            //saving in this import
                            foreach ($mappedarray as $attid => $attname) {
                                if (!empty($attname)) {
                                    $bData = array('participant_id' => $aData,
                                                       'attribute_id' => $attid,
                                                       'value' => $writearray[$attname]);
                                         Participant_attribute::model()->updateParticipantAttributeValue($bData);
                                } else {
                                    //If the value is empty, don't write the value
                                }
                            }
                            $overwritten++;
                        }
                    }
                }
                if ($thisduplicate == 1) {
                    $dupfound = true;
                    $duplicatelist[] = $writearray['firstname'] . " " . $writearray['lastname'] . " (" . $writearray['email'] . ")";
                }

                //Checking the email address is in a valid format
                $invalidemail = false;
                $writearray['email'] = trim($writearray['email']);
                if ($writearray['email'] != '') {
                    $aEmailAddresses = explode(';', $writearray['email']);
                    foreach ($aEmailAddresses as $sEmailaddress) {
                        if (!validateEmailAddress($sEmailaddress)) {
                            $invalidemail = true;
                            $invalidemaillist[] = $line[0] . " " . $line[1] . " (" . $line[2] . ")";
                        }
                    }
                }
            	if (!$dupfound && !$invalidemail) {
            		//If it isn't a duplicate value or an invalid email, process the entry as a new participant

               	    //First, process the known fields
                    if (!isset($writearray['participant_id']) || $writearray['participant_id'] == "") {
                        $uuid = $this->gen_uuid(); //Generate a UUID for the new participant
                        $writearray['participant_id'] = $uuid;
                    }
                    if (isset($writearray['emailstatus']) && trim($writearray['emailstatus'] == '')) {
                        unset($writearray['emailstatus']);
                    }
                	if (!isset($writearray['language']) || $writearray['language'] == "") {
                		$writearray['language'] = "en";
                	}
                	if (!isset($writearray['blacklisted']) || $writearray['blacklisted'] == "") {
                        $writearray['blacklisted'] = "N";
                    }
                    $writearray['owner_uid'] = Yii::app()->session['loginID'];
                    if (isset($writearray['validfrom']) && trim($writearray['validfrom'] == '')) {
                        unset($writearray['validfrom']);
                    }
                    if (isset($writearray['validuntil']) && trim($writearray['validuntil'] == '')) {
                        unset($writearray['validuntil']);
                    }
                    $dontimport=false;
                    if (($filterblankemails == "accept" && $writearray['email'] == "") || $writearray['firstname'] == "" || $writearray['lastname'] == "") {
                    	//The mandatory fields of email, firstname and lastname
						//must be filled, but one or more are empty
                        $mandatory++;
                        $dontimport=true;
                    } else {
                        foreach ($writearray as $key => $value) {
                            if (!empty($mappedarray)) {
                            	//The mapped array contains the attributes we are
                            	//saving in this import
                            	if (in_array($key, $allowedfieldnames)) {
                                    foreach ($mappedarray as $attid => $attname) {
                                        if (strtolower($attname) == $key) {
                                            if (!empty($value)) {
                                                $aData = array('participant_id' => $writearray['participant_id'],
                                                               'attribute_id' => $attid,
                                                               'value' => $value);
                                             	ParticipantAttributeNames::model()->saveParticipantAttributeValue($aData);
                                            } else {
                                                //If the value is empty, don't write the value
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //If any of the mandatory fields are blank, then don't import this user
                    if(!$dontimport)
                    {
                        Participants::model()->insertParticipantCSV($writearray);
                        $imported++;
                    }
            	}
                $mincriteria++;
            }
            $recordcount++;
        }

        unlink($sFilePath);
        $clang = $this->getController()->lang;
        $aData = array();
        $aData['clang'] = $clang;
        $aData['recordcount'] = $recordcount - 1;
        $aData['duplicatelist'] = $duplicatelist;
        $aData['mincriteria'] = $mincriteria;
        $aData['imported'] = $imported;
        $aData['errorinupload'] = $errorinupload;
        $aData['invalidemaillist'] = $invalidemaillist;
        $aData['mandatory'] = $mandatory;
        $aData['invalidattribute'] = $invalidattribute;
        $aData['mandatory'] = $mandatory;
        $aData['invalidparticipantid'] = $invalidparticipantid;
        $aData['overwritten'] = $overwritten;
        $aData['dupreason'] = $dupreason;
        $this->getController()->render('/admin/participants/uploadSummary_view', $aData);
    }

    function summaryview()
    {
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'uploadSummary'));
    }

    /*
     * Responsible for setting the session variables for attribute map page redirect
     */
    function setSession()
    {
        unset(Yii::app()->session['participantid']);
        Yii::app()->session['participantid'] = Yii::app()->request->getPost('participantid');
    }

    /*
     * Generation of unique id
     */
    function gen_uuid()
    {
        return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
        );
    }

    /*
     * Stores the shared participant information in participant_shares
     */
    function shareParticipants()
    {
        $clang = $this->getController()->lang;
        $iParticipantId = Yii::app()->request->getPost('participantid');
        $iShareUserId = Yii::app()->request->getPost('shareuser');
        $bCanEdit = Yii::app()->request->getPost('can_edit');

        $i = 0;
        foreach ($iParticipantId as $iId)
        {
            $time = time();
            $aData = array('participant_id' => $iId,
                'share_uid' => $iShareUserId,
                'date_added' => date('Y-m-d H:i:s', $time),
                'can_edit' => $bCanEdit);
            ParticipantShares::model()->storeParticipantShare($aData);
            $i++;
        }

        printf($clang->gT("%s participants have been shared"), $i);
    }

    /*
     * Responsible for copying the participant from tokens to the central Database
     *
     * TODO: Most of the work for this function is in the participants model file
     *       but it doesn't belong there.
     */
    function addToCentral()
    {
        $newarr = Yii::app()->request->getPost('newarr');
        $mapped = Yii::app()->request->getPost('mapped');
        $overwriteauto = Yii::app()->request->getPost('overwriteauto');
        $overwriteman = Yii::app()->request->getPost('overwriteman');
        $createautomap = Yii::app()->request->getPost('createautomap');

        $response = Participants::model()->copyToCentral(Yii::app()->request->getPost('surveyid'), $newarr, $mapped, $overwriteauto, $overwriteman, $createautomap);
        $clang = $this->getController()->lang;

        printf($clang->gT("%s participants have been copied to the central participants table"), $response['success']);
        if($response['duplicate'] > 0) {
            echo "\r\n";
            printf($clang->gT("%s entries were not copied because they already existed"), $response['duplicate']);
        }
        if($response['overwriteman']=="true" || $response['overwriteauto']) {
            echo "\r\n";
            $clang->eT("Attribute values for existing participants have been updated from the token records");
        }
    }

    /*
     * Responsible for adding the participant to the specified survey
     */
    function addToToken()
    {
        $response = Participants::model()->copytoSurvey(Yii::app()->request
                                                         ->getPost('participantid'),
                                               Yii::app()->request
                                                         ->getPost('surveyid'), Yii::app()
                                                         ->request->getPost('attributeid')
                                               );
        $clang = $this->getController()->lang;

        printf($clang->gT("%s participants have been copied to the survey token table"), $response['success']);
        if($response['duplicate']>0) {
            echo "\r\n";
            printf($clang->gT("%s entries were not copied because they already existed"), $response['duplicate']);
        }
        if($response['overwrite']=="true") {
            echo "\r\n";
            $clang->eT("Attribute values for existing participants have been updated from the participants records");
        }
    }

    /*
     * Responsible for adding the participant to the specified survey with attribute mapping
     */
    function addToTokenattmap()
    {
        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $iSurveyId = Yii::app()->request->getPost('surveyid');
        $mapped = Yii::app()->request->getPost('mapped');
        $newcreate = Yii::app()->request->getPost('newarr');
        $overwriteauto = Yii::app()->request->getPost('overwrite');
        $overwriteman = Yii::app()->request->getPost('overwriteman');
        $overwritest = Yii::app()->request->getPost('overwritest');
        $createautomap = Yii::app()->request->getPost('createautomap');

        $clang = $this->getController()->lang;
        if (empty($newcreate[0])) { $newcreate = array(); }

        $response = Participants::model()->copytosurveyatt($iSurveyId, $mapped, $newcreate, $iParticipantId, $overwriteauto, $overwriteman, $overwritest, $createautomap);

        printf($clang->gT("%s participants have been copied to the survey token table"), $response['success']);
        if($response['duplicate']>0) {
            echo "\r\n";
            printf($clang->gT("%s entries were not copied because they already existed"), $response['duplicate']);
        }
        if($response['overwriteauto']=="true" || $response['overwriteman']=="true") {
            echo "\r\n";
            $clang->eT("Attribute values for existing participants have been updated from the participants records");
        }
    }

    /*
     * Responsible for attribute mapping while copying participants from cpdb to token's table
     */
    function attributeMap()
    {
        Yii::app()->loadHelper('common');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . "attributeMap.js");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl') ."attributeMap.css");

        $iSurveyId = Yii::app()->request->getPost('survey_id');
        $redirect = Yii::app()->request->getPost('redirect');
        $count = Yii::app()->request->getPost('count');
        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $attributes = ParticipantAttributeNames::model()->getAttributes();
        $tokenattributefieldnames = getTokenFieldsAndNames($iSurveyId, TRUE);
        /* $arr = Yii::app()->db
                         ->createCommand()
                         ->select('*')
                         ->from("{{tokens_$iSurveyId}}")
                         ->queryRow();

        if (is_array($arr))
        {
            $tokenfieldnames = array_keys($arr);
            $tokenattributefieldnames = array_filter($tokenfieldnames, 'filterForAttributes');
        }
        else
        {
            $tokenattributefieldnames = array();
        } */

        $selectedattribute = array(); //List of existing attribute fields that are not mapped
        $selectedcentralattribute = array(); //List of attributes that haven't already been mapped
        $alreadymappedattid = array(); //List of fields already mapped to this tokens table
        $alreadymappedattname = array();
        $i = 0;
        $j = 0;

        foreach ($tokenattributefieldnames as $key => $value)
        {
            if (is_numeric($key[10])) //Assumes that if the 11th character is a number, it must be a token-table created attribute
            {
                $selectedattribute[$key] = $value['description'];
                $i++;
            }
            else
            {
                array_push($alreadymappedattid, substr($key, 15));
            }
        }
        foreach ($attributes as $row)
        {
            if (!in_array($row['attribute_id'], $alreadymappedattid))
            {
                $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
            }
            else
            {
                array_push($alreadymappedattname, $row['attribute_name']);
            }
        }

        $aData = array(
            'selectedcentralattribute' => $selectedcentralattribute,
            'selectedtokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattname,
            'survey_id' => $iSurveyId,
            'redirect' => $redirect,
            'participant_id' => $iParticipantId,
            'count' => $count
        );

        $this->_renderWrappedTemplate('participants', 'attributeMap', $aData);
    }

    /*
     * This function is responsible for attribute mapping while copying participants from cpdb to token's table
     */

    function attributeMapToken()
    {
        Yii::app()->loadHelper('common');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . "attributeMapToken.js");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl') ."attributeMapToken.css");

        $iSurveyId = Yii::app()->request->getQuery('sid');
        $attributes = ParticipantAttributeNames::model()->getAttributes();
        $tokenattributefieldnames = getTokenFieldsAndNames($iSurveyId, TRUE);

        $selectedattribute = array();
        $selectedcentralattribute = array();
        $alreadymappedattid = array();
        $alreadymappedattdisplay = array();
        $alreadymappedattnames = array();
        $i = 0;
        $j = 0;

        foreach ($tokenattributefieldnames as $key => $value)
        {
            if (is_numeric($key[10]))
            {
                $selectedattribute[$value['description']] = $key;
            }
            else
            {
                $attributeid=substr($key,15);
                $continue=false;
                foreach($attributes as $attribute) {
                    if($attribute['attribute_id']==$attributeid) {
                        $continue=true;
                    }
                }
                if($continue) {
                    array_push($alreadymappedattid, $attributeid);
                    array_push($alreadymappedattdisplay, $key);
                    $alreadymappedattnames[$key]=$value['description'];
                } else {
                    $selectedattribute[$value['description']]=$key;
                }
            }
        }
        foreach ($attributes as $row)
        {
            if (!in_array($row['attribute_id'], $alreadymappedattid))
            {
                $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
            }
        }

        $aData = array(
            'attribute' => $selectedcentralattribute,
            'tokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattdisplay,
            'alreadymappedattdescription' => $alreadymappedattnames
        );

        $this->_renderWrappedTemplate('participants', 'attributeMapToken', $aData);
    }

    /**
    * This function deletes the uploaded csv file if the import is cancelled
    *
    */
    function mapCSVcancelled()
    {
        unlink(Yii::app()->getConfig('tempdir') . '/' . basename(Yii::app()->request->getPost('fullfilepath')));
    }


    function blacklistParticipant()
    {
        $this->load->model('participants_model');
        $iParticipantId = $this->uri->segment(4);
        $iSurveyId = $this->uri->segment(5);
        $clang = $this->limesurvey_lang;
        if (!is_numeric($iSurveyId))
        {
            $blacklist = $this->uri->segment(5);
            if ($blacklist == 'Y' || $blacklist == 'N')
            {
                $aData = array('blacklisted' => $blacklist, 'participant_id' => $iParticipantId);
                $aData = $this->participants_model->blacklistparticipantglobal($aData);
                $aData['global'] = 1;
                $aData['clang'] = $clang;
                $aData['blacklist'] = $blacklist;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
            else
            {
                $aData['is_participant'] = 0;
                $aData['is_updated'] = 0;
                $aData['clang'] = $clang;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
        }
        else
        {
            $blacklist = $this->uri->segment(6);
            if ($blacklist == 'Y' || $blacklist == 'N')
            {
                $aData = array('blacklisted' => $blacklist);
                $aData = $this->participants_model->blacklistparticipantlocal($aData, $iSurveyId, $iParticipantId);
                $aData['global'] = 1;
                $aData['clang'] = $clang;
                $aData['local'] = 1;
                $aData['blacklist'] = $blacklist;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
            else
            {
                $aData['is_participant'] = 0;
                $aData['is_updated'] = 0;
                $aData['clang'] = $clang;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
        }
    }

}

?>