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


/**
 * This is the main controller for Participants Panel
 */
class participantsaction extends Survey_Common_Action
{
    public function runWithParams($params)
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','read'))
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
        $aData['aAttributes'] = ParticipantAttributeName::model()->getAllAttributes();
        App()->getClientScript()->registerPackage('jqgrid');
        if (!empty($sScript))
        {
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', $sScript . '.js');
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
        App()->getClientScript()->registerPackage('bootstrap-multiselect');
        $aData['display']['menu_bars'] = false;

        // Add "_view" to urls
        if (is_array($aViewUrls))
        {
            array_walk($aViewUrls, function(&$url) { $url .= "_view"; });
        }
        elseif (is_string($aViewUrls))
        {
            $aViewUrls .= "_view";
        }
        else
        {
            // Complete madness
            throw new \InvalidArgumentException("aViewUrls must be either string or array");
        }

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    /**
     * Export to csv using optional search/filter
     *
     * @param type $search  CDCriteria?
     * @paran mixed $mAttributeIDs Empty array for no attributes, or array of attribute IDs or null for all attributes
     */
    private function csvExport($search = null, $aAttributeIDs=null) {
        if (!Permission::model()->hasGlobalPermission('participantpanel','export'))
        {
            die('No permission');
        }
        Yii::app()->loadHelper('export');
        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        $aAttributeIDs=array_combine($aAttributeIDs,$aAttributeIDs);
        $query = Participant::model()->getParticipants(0, 0, $aAttributeIDs, null, $search, $iUserID);
        if (!$query)
            return false;

        // Field names in the first row
        $fields = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
        $outputarray = array(); // The array to be passed to the export helper to be written to a csv file

        $outputarray[0] = $fields; //fields written to output array

        foreach ($aAttributeIDs as $value)
        {
            if ($value == 0)
            {
                continue;
            }
            $fields[] = 'a'.$value;
            $attributename = ParticipantAttributeName::model()->getAttributeNames($value);
            $outputarray[0][] = $attributename[0]['attribute_name'];
        }

        $fieldNeededKeys=array_fill_keys($fields, '');
        $fieldKeys = array_flip($fields);
        foreach ($query as $field => $aData)
        {
            $outputarray[] = array_merge($fieldNeededKeys,array_intersect_key($aData, $fieldKeys));
        }
        CPDBExport($outputarray, "central_" . time());
    }

    /**
     * Returns a string with the number of participants available for export or 0
     *
     * @param type $search
     * @return string|int
     */
    protected function csvExportCount($search = null)
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','export'))
        {
            return 0;
        }
        $attid = ParticipantAttributeName::model()->getVisibleAttributes();
        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }


        $count = Participant::model()->getParticipantsCount($attid, $search, $iUserID);

        if ($count > 0) {
            return ngT("Export {n} participant to CSV|Export {n} participants to CSV", $count);
        } else {
            return $count;
        }
    }

    /**
     * Loads the view 'participantsPanel'
     */
    public function index()
    {
        $iUserID = Yii::app()->session['loginID'];

        // if superadmin all the records in the cpdb will be displayed
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $iTotalRecords = Participant::model()->count();
        }
        // if not only the participants on which he has right on (shared and owned)
        else
        {
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserID);
        }
        // gets the count of participants, their attributes and other such details
        $aData = array(
            'totalrecords' => $iTotalRecords,
            'owned' => Participant::model()->count('owner_uid = ' . $iUserID),
            'shared' => Participant::model()->getParticipantsSharedCount($iUserID),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributecount' => ParticipantAttributeName::model()->count(),
            'blacklisted' => Participant::model()->count('owner_uid = ' . $iUserID . ' AND blacklisted = \'Y\'')
        );
        // loads the participant panel and summary view
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'summary'), $aData);
    }

    /**
     * Loads the view 'importCSV'
     */
    public function importCSV()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','import'))
        {
            die('No permission');
        }
        $aData = array(
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes()
        );
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'importCSV'),$aData);
    }

    /**
     * Loads the view 'displayParticipants' which contains the main grid
     */
    public function displayParticipants()
    {
        $lang = Yii::app()->session['adminlang'];
        // loads the survey names to be shown in add to survey
        // if user is superadmin, all survey names
        $sSearchCondition=Yii::app()->request->getPost('searchcondition','');
        $urlSearch=!empty($sSearchCondition) ? "getParticipantsResults_json" : "getParticipants_json";

        //Get list of surveys.
        //Should be all surveys owned by user (or all surveys for super admin)
        $surveys = Survey::model();
        //!!! Is this even possible to execute?
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
            $surveys->permission(Yii::app()->user->getId());

        $aSurveyNames = $surveys->model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language'), 'owner'))->findAll();

        /* Build a list of surveys that have tokens tables */
        $tSurveyNames=array();
        foreach($aSurveyNames as $row)
        {
            $row = array_merge($row->attributes, $row->defaultlanguage->attributes);
            $bTokenExists = tableExists('{{tokens_' . $row['sid'] . '}}');
            if ($bTokenExists) //If tokens table exists
            {
                $tSurveyNames[]=$row;
            }
        }

        // if superadmin all the records in the cpdb will be displayed
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $iTotalRecords = Participant::model()->count();
        }
        // if not only the participants on which he has right on (shared and owned)
        else
        {
            $iUserId = Yii::app()->user->getId();
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserId);
        }

        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'surveynames' => $aSurveyNames,
            'tokensurveynames' => $tSurveyNames,
            'urlsearch' => $urlSearch,
            'sSearchCondition' => $sSearchCondition,
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'totalrecords' => $iTotalRecords,
        );
        App()->getClientScript()->registerPackage('jqgrid');

        // loads the participant panel view and display participant view


        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'displayParticipants'), $aData);
    }

    /**
     * Loads the view 'blacklistControl'
     */
    public function blacklistControl()
    {
        $aData = array(
            'blacklistallsurveys' => Yii::app()->getConfig('blacklistallsurveys'),
            'blacklistnewsurveys' => Yii::app()->getConfig('blacklistnewsurveys'),
            'blockaddingtosurveys' => Yii::app()->getConfig('blockaddingtosurveys'),
            'hideblacklisted' => Yii::app()->getConfig('hideblacklisted'),
            'deleteblacklisted' => Yii::app()->getConfig('deleteblacklisted'),
            'allowunblacklist' => Yii::app()->getConfig('allowunblacklist'),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes()
        );
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'blacklist'), $aData);
    }

    /**
     * Loads the view 'userControl'
     */
    public function userControl()
    {
        $aData = array(
            'userideditable' => Yii::app()->getConfig('userideditable'),
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes()
        );
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'userControl'), $aData);
    }

    /**
     * Loads the view 'sharePanel'
     */
    public function sharePanel()
    {
        $this->_loadjqGrid('sharePanel');
    }

    /**
     * Sends the shared participant info to the share panel using JSON encoding
     * Called after the share panel grid is loaded
     * Returns the json depending on the user logged in by checking it from the session
     * @return JSON encoded string containg sharing information
     */
    public function getShareInfo_json()
    {
        $aData = new stdClass();
        $aData->page = 1;

        // If super administrator all the share info in the links table will be shown
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $records = Participant::model()->getParticipantSharedAll();
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                //for conversion of uid to human readable names
                $iShareUserId = $row['share_uid'];
                if ($iShareUserId != 0) {
                    $oShared = User::model()->getName($iShareUserId);
                    $sSharename = $oShared['full_name'];
                } else {
                    $sSharename = 'All users';
                }
                $owner = User::model()->getName($row['owner_uid']);
                $aData->rows[$i]['id'] = $row['participant_id']."--".$row['share_uid']; //This is the unique combination per record
                $aData->rows[$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $sSharename, $row['share_uid'], $owner['full_name'], $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        }
        // otherwise only the shared participants by that user
        else
        {
            $records = Participant::model()->getParticipantShared(Yii::app()->session['loginID']);
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                $iShareUserId = $row['share_uid'];//for conversion of uid to human readable names
                if ($iShareUserId != 0) {
                    $oShared = User::model()->getName($iShareUserId);
                    $sSharename = $oShared['full_name'];
                } else {
                    $sSharename = 'All users';
                }
                $aData->rows[$i]['id'] = $row['participant_id'];
                $aData['rows'][$i]['cell'] = array($row['firstname'], $row['lastname'], $row['email'], $sSharename, $row['share_uid'], $row['date_added'], $row['can_edit']);
                $i++;
            }

            echo ls_json_encode($aData);
        }
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     */
    public function editShareInfo()
    {
        $operation = Yii::app()->request->getPost('oper');
        $shareIds = Yii::app()->request->getPost('id');
        if ($operation == 'del') // If operation is delete , it will delete, otherwise edit it
        {
            ParticipantShare::model()->deleteRow($shareIds);
        }
        else
        {
            $aData = array(
                    'participant_id' => Yii::app()->request->getPost('participant_id'),
                    'can_edit' => Yii::app()->request->getPost('can_edit'),
                    'share_uid' => Yii::app()->request->getPost('shared_uid')
                    );
            ParticipantShare::model()->updateShare($aData);
        }
    }

    /**
     * Loads the view 'attributeControl'
     */
    public function attributeControl()
    {
        $this->_loadjqGrid('attributeControl');
    }

    /**
     * Sends the attributes info using JSON encoding
     * Called after the Attribute management grid is loaded
     * @return JSON encoded string containg sharing information
     */
    public function getAttributeInfo_json()
    {

        $page = (int)Yii::app()->request->getPost('page');
        $limit = (int)Yii::app()->request->getPost('rows');
        $limit = empty($limit) ? $limit : 50; //Stop division by zero errors
        $records = ParticipantAttributeName::model()->with('participant_attribute_names_lang')->findAll(array('order'=>'attribute_name'));
        $attribute_types = array(
            'DD' => gT("Drop-down list"),
            'DP' => gT("Date"),
            'TB' => gT("Text box")
        );
        $aData = new stdClass();
        $aData->page = $page;
        $aData->records = count($records);
        $aData->total = ceil(count(ParticipantAttributeName::model()->getCPDBAttributes()) / $limit);
        $i = 0;
        foreach($records as $row) { //Iterate through each attribute
            $sAttributeCaption=htmlspecialchars($row->defaultname); //Choose the first item by default
            foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                if($names->lang == Yii::app()->session['adminlang']) {$sAttributeCaption= $sAttributeCaption.htmlspecialchars(" ({$names->attribute_name})");} //Override the default with the admin language version if found
            }
            $aData->rows[$i]['id'] = $row->attribute_id;
            $aData->rows[$i]['cell'] = array('', $sAttributeCaption, $attribute_types[$row->attribute_type], $row->visible);
            $i++;
        }


        echo ls_json_encode($aData);
    }

    /**
     * Takes the edit call from the share panel, which either edits or deletes the share information
     * Basically takes the call on can_edit
     */
    public function editAttributeInfo()
    {

        $operation = Yii::app()->request->getPost('oper');

        if ($operation == 'del' && Yii::app()->request->getPost('id'))
        {
            $aAttributeIds = (array) explode(',', Yii::app()->request->getPost('id'));
            $aAttributeIds = array_map('trim', $aAttributeIds);
            $aAttributeIds = array_map('intval', $aAttributeIds);

            foreach ($aAttributeIds as $iAttributeId)
            {
                ParticipantAttributeName::model()->delAttribute($iAttributeId);
            }
        }
        elseif ($operation == 'add' && Yii::app()->request->getPost('attribute_name'))
        {
            $aData = array(
                'defaultname' => Yii::app()->request->getPost('attribute_name'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible')? 'TRUE' : 'FALSE'
            );
            echo ParticipantAttributeName::model()->storeAttribute($aData);
        }
        elseif ($operation == 'edit' && Yii::app()->request->getPost('id'))
        {
            $aData = array(
                'attribute_id' => Yii::app()->request->getPost('id'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible','FALSE') != 'FALSE' ? 'TRUE' : 'FALSE'
            );
            ParticipantAttributeName::model()->saveAttribute($aData);
            eT("Attribute display setting updated");
        }

    }

    /**
     * Takes the delete call from the display participants and take appropriate action depending on the condition
     */
    public function delParticipant()
    {
        if (Permission::model()->hasGlobalPermission('participantpanel','delete'))
        {
            $selectoption = Yii::app()->request->getPost('selectedoption');
            $iParticipantId = Yii::app()->request->getPost('participant_id');

            //echo $selectoption." -- ".$iParticipantId."<br />"; die();

            // Deletes from participants only
            if ($selectoption == 'po')
            {
                Participant::model()->deleteParticipants($iParticipantId);
            }
            // Deletes from central and token table
            elseif ($selectoption == 'ptt')
            {
                Participant::model()->deleteParticipantToken($iParticipantId);
            }
            // Deletes from central , token and assosiated responses as well
            elseif ($selectoption == 'ptta')
            {
                Participant::model()->deleteParticipantTokenAnswer($iParticipantId);
            }
        }
    }

    /**
     * Resposible for editing data on the jqGrid
     */
    public function editParticipant()
    {
        $sOperation = Yii::app()->request->getPost('oper');

        // if edit it will update the row
        if ($sOperation == 'edit' && Permission::model()->hasGlobalPermission('participantpanel','update') && Participant::model()->is_owner(Yii::app()->request->getPost('id')))
        {
            $aData = array(
                'participant_id' => Yii::app()->request->getPost('id'),
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted')
            );
            Participant::model()->updateRow($aData);
        }
        // if add it will insert a new row
        elseif ($sOperation == 'add' && Permission::model()->hasGlobalPermission('participantpanel','create'))
        {
            $uuid = Participant::gen_uuid();
            $aData = array(
                'participant_id' => $uuid,
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted'),
                'owner_uid' => Yii::app()->session['loginID'],
                'created_by' => Yii::app()->session['loginID']
            );
            Participant::model()->insertParticipant($aData);
        }
    }

    /**
     * Stores the user control setting to the database
     */
    public function storeUserControlValues()
    {
        if ($find = SettingGlobal::model()->findByPk('userideditable'))
        {
            SettingGlobal::model()->updateByPk('userideditable', array('stg_value'=>Yii::app()->request->getPost('userideditable')));
        }
        else
        {
            $stg = new SettingGlobal;
            $stg ->stg_name='userideditable';
            $stg ->stg_value=Yii::app()->request->getPost('userideditable');
            $stg->save();
        }
        Yii::app()->getController()->redirect(array('admin/participants/sa/userControl'));
    }

    /**
     * Stores the blacklist setting to the database
     */
    public function storeBlacklistValues()
    {
        $values = Array('blacklistallsurveys', 'blacklistnewsurveys', 'blockaddingtosurveys', 'hideblacklisted', 'deleteblacklisted', 'allowunblacklist', 'userideditable');
        foreach ($values as $value)
        {
            if ($find = SettingGlobal::model()->findByPk($value))
            {
                SettingGlobal::model()->updateByPk($value, array('stg_value'=>Yii::app()->request->getPost($value)));
            }
            else
            {
                $stg = new SettingGlobal;
                $stg ->stg_name=$value;
                $stg ->stg_value=Yii::app()->request->getPost($value);
                $stg->save();
            }
        }
        Yii::app()->getController()->redirect(array('admin/participants/sa/blacklistControl'));
    }

    /**
     * Receives an ajax call containing the participant id in the fourth segment of the url
     * Supplies list of survey links - surveys of which this participant is on the tokens table
     * URL: [localurl]/limesurvey/admin/participants/getSurveyInfo_json/pid/[participant_id]
     * RETURNS: json data containing linked survey information (Survey name, survey id, token_id and date_added)
     */
    public function getSurveyInfo_json()
    {
        $participantid = Yii::app()->request->getQuery('pid');
        $records = SurveyLink::model()->findAllByAttributes((array('participant_id' => $participantid)));
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
            if (!Permission::model()->hasSurveyPermission($row['survey_id'], 'tokens', 'read'))
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
    public function exporttocsvcount()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','export'))
        {
            die('No permission');
        }
        $searchconditionurl = Yii::app()->request->getPost('searchURL');
        $searchcondition  = Yii::app()->request->getPost('searchcondition');
        $searchconditionurl = basename($searchconditionurl);

        if ($searchconditionurl != 'getParticipants_json') // if there is a search condition then only the participants that match the search criteria are counted
        {
            $condition = explode("||", $searchcondition);
            $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search = null;
        }

        echo $this->csvExportCount($search);
    }

    /**
     * Outputs the count of participants when using the export all button on the top
     */
    public function exporttocsvcountAll()
    {
        echo $this->csvExportCount();
    }

    /**
     * Responsible to export all the participants in the central table
     */
    public function exporttocsvAll()
    {
        $this->csvExport(); // no search
    }

    /**
     * Similar to export to all message where it counts the number to participants to be copied
     * and echo them to be displayed in modal box header
     */
    public function getaddtosurveymsg()
    {
        $searchcondition = basename(Yii::app()->request->getPost('searchcondition'));

        // If there is a search condition in the url of the jqGrid
        if ($searchcondition != 'getParticipants_json')
        {
            $participantid = "";
            $condition = explode("||", $searchcondition);

            $query = Participant::model()->getParticipantsSearchMultiple($condition, 0, 0);

            printf( gT("%s participant(s) are to be copied "), count($query));
        }
        // if there is no search condition the participants will be counted on the basis of who is logged in
        else
        {
            if (Permission::model()->hasGlobalPermission('superadmin','read')) //If super admin all the participants will be visible
            {
                $count = Participant::model()->getParticipantsCountWithoutLimit();
            }
            else
            {
                $query = Participant::model()->getParticipantsOwner(Yii::app()->session['loginID']);
                $count = count($query);
            }

            printf(gT("%s participant(s) are to be copied "), $count);
        }
    }

    /**
     * Gets the ids of participants to be copied to the individual survey
     */
    public function getSearchIDs()
    {
        $searchcondition = Yii::app()->request->getPost('searchcondition'); // get the search condition from the URL
        $sSearchURL = basename(Yii::app()->request->getPost('searchURL')); // get the search condition from the URL
        /* a search contains posted data inside $_POST['searchcondition'].
         * Each separate query is made up of 3 fields, separated by double-pipes ("|")
         * EG: fname||eq||jason||lname||ct||c
         *
         */
        if ($sSearchURL != 'getParticipants_json') // if there is a search condition present
        {
            $participantid = "";
            $condition = explode("||", $searchcondition);  // explode the condition to the array
            $query = Participant::model()->getParticipantsSearchMultiple($condition, 0, 0);

            foreach ($query as $key => $value)
            {
                if (Permission::model()->hasGlobalPermission('superadmin','read'))
                {
                    $participantid .= "," . $value['participant_id']; // combine the participant id's in an string
                } else
                {
                    if(Participant::model()->is_owner($value['participant_id']))
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
            if (Permission::model()->hasGlobalPermission('superadmin','read')) //If super admin all the participants will be visible
            {
                $query = Participant::model()->getParticipantsWithoutLimit(); // get all the participant id if it is a super admin
            }
            else // get participants on which the user has right on
            {
                $query = Participant::model()->getParticipantsOwner(Yii::app()->session['loginID']);
            }

            foreach ($query as $key => $value)
            {
                $participantid = $participantid . "," . $value['participant_id']; // combine the participant id's in an string
            }
            echo $participantid; //echo the participant id's
        }
    }

    /**
     * Exports participants as CSV - receiver function for the GUI
     */
    public function exporttocsv()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','export'))
        {
            die('No permission');
        }

        if (Yii::app()->request->getPost('searchcondition','') !== '') // if there is a search condition then only the participants that match the search criteria are counted
        {
            $condition = explode("%7C%7C", Yii::app()->request->getPost('searchcondition',''));
            $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        } else {
            $search = null;
        }
        $aAttributes=explode('+',Yii::app()->request->getPost('attributes',''));
        $this->csvExport($search,$aAttributes);
    }

    /**
     * Equal to getParticipants_json() but now with a search
     */
    public function getParticipantsResults_json()
    {
        $searchcondition = Yii::app()->request->getpost('searchcondition');
        $finalcondition = array();
        $condition = explode("||", $searchcondition);
        $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        return $this->getParticipants_json($search);
    }

    /*
     * Sends the data in JSON format extracted from the database to be displayed using the jqGrid
     */
    public function getParticipants_json($search = null)
    {
        $page = (int) Yii::app()->request->getPost('page');
        $limit = (int) Yii::app()->request->getPost('rows');
        $limit = empty($limit) ? 50:$limit; //Stop division by zero errors

        $attid = ParticipantAttributeName::model()->getVisibleAttributes();
        $participantfields = array('participant_id', 'can_edit', 'firstname', 'lastname', 'email', 'blacklisted', 'survey', 'language', 'owner_uid');
        foreach ($attid as $key => $value)
        {
            array_push($participantfields, 'a'.$value['attribute_id']);
        }
        $sidx = Yii::app()->request->getPost('sidx');
        $sidx = in_array($sidx,$participantfields) ? $sidx : "lastname";
        $sord = Yii::app()->request->getPost('sord');
        $sord = ($sord=='desc') ? 'desc' : 'asc';
        $order = $sidx. " ". $sord;


        $aData = new stdClass;

        //If super admin all the participants will be visible
        if (Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            $iUserID = null;
        } else {
            $iUserID = Yii::app()->session['loginID'];
        }
        $aData->records = Participant::model()->getParticipantsCount($attid, $search, $iUserID);
        $aData->total = ceil($aData->records / $limit);
        if ($page>$aData->total) {
            $page = $aData->total;
        }
        $aData->page = $page;
        $records = Participant::model()->getParticipants($page, $limit,$attid, $order, $search, $iUserID);


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
            $aRowToAdd['cell'] = array($row['participant_id'], $sCanEdit, htmlspecialchars($row['firstname']), htmlspecialchars($row['lastname']), htmlspecialchars($row['email']), $row['blacklisted'], $row['survey'], $row['language'], $row['ownername']);
            $aRowToAdd['id'] = $row['participant_id'];
            // add attribute values
            foreach($row as $key=>$attvalue)
            {
                if(preg_match('/^a\d+$/', $key) )
                {
                    $aRowToAdd['cell'][] = $attvalue;
                }
            }

            $aData->rows[] = $aRowToAdd;
        }

        echo ls_json_encode($aData);
    }

    /*
     * Fetches the attributes of a participant to be displayed in the attribute subgrid
     */
    public function getAttribute_json()
    {
        $iParticipantId = strip_tags(Yii::app()->request->getQuery('pid'));
        $records = ParticipantAttributeName::model()->getParticipantVisibleAttribute($iParticipantId);
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
                $attvalues = ParticipantAttributeName::model()->getAttributesValues($row['attribute_id']);
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
            $attributenotdone = ParticipantAttributeName::model()->getCPDBAttributes();
        }
        /* The user has SOME values stored against attributes */
        else
        {
            $attributenotdone = ParticipantAttributeName::model()->getnotaddedAttributes($doneattributes);
        }

        /* Go through the empty attributes and build an entry in the output for them */
        foreach ($attributenotdone as $row)
        {
            $outputs[$i] = array("", $iParticipantId."_".$row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], "");
            if ($row['attribute_type'] == "DD")
            {
                $attvalues = ParticipantAttributeName::model()->getAttributesValues($row['attribute_id']);
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

    /**
     * Responsible for showing the additional attribute for CPDB
     * Edit attribute form
     *
     * @return void
     */
    public function viewAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = array(
                'attributes' => ParticipantAttributeName::model()->getAttribute($iAttributeId),
                'attributenames' => ParticipantAttributeName::model()->getAttributeNames($iAttributeId),
                'attributevalues' => ParticipantAttributeName::model()->getAttributesValues($iAttributeId),
                'aAttributes' => ParticipantAttributeName::model()->getAllAttributes()
                );
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'viewAttribute.js');
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'viewAttribute'), $aData);
    }

    /**
     * Responsible for saving the additional attribute. It iterates through all the new attributes added dynamically
     * and iterates through them
     *
     * @return void
     */
    public function saveAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = array(
                'attribute_id' => $iAttributeId,
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'defaultname' => Yii::app()->request->getPost('defaultname'),
                'visible' => Yii::app()->request->getPost('visible')
                );
        ParticipantAttributeName::model()->saveAttribute($aData);
        Yii::app()->setFlashMessage(gT('Attribute was saved.'), 'info');

        // Save translations
        if (isset($_POST['lang']))
        {
            foreach ($_POST['lang'] as $lang => $translation)
            {
                $langdata = array(
                    'attribute_id' => $iAttributeId,
                    'attribute_name' => $translation,
                    'lang' => $lang
                );

                ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
            }
        }

        // TODO: What's the Difference between lang and langdata?
        if (Yii::app()->request->getPost('langdata'))
        {
            $langdata = array(
                'attribute_id' => $iAttributeId,
                'attribute_name' => Yii::app()->request->getPost('attname'),
                'lang' => Yii::app()->request->getPost('langdata')
            );

            ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
        }

        /* New attribute value */
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
            ParticipantAttributeName::model()->storeAttributeValues($aDatavalues);
        }
        /* Save updated attribute values */
        if (Yii::app()->request->getPost('editbox') || Yii::app()->request->getPost('editbox')=="0")
        {
            $editattvalue = array(
                'attribute_id' => $iAttributeId,
                'value_id' => Yii::app()->request->getPost('value_id'),
                'value' => Yii::app()->request->getPost('editbox')
            );
            ParticipantAttributeName::model()->saveAttributeValue($editattvalue);
        }
        Yii::app()->getController()->redirect(array('admin/participants/sa/attributeControl'));
    }

    /*
     * Responsible for deleting the additional attribute values in case of drop down.
     */
    public function delAttributeValues()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $iValueId = Yii::app()->request->getQuery('vid');
        ParticipantAttributeName::model()->delAttributeValues($iAttributeId, $iValueId);
        Yii::app()->getController()->redirect(array('/admin/participants/sa/viewAttribute/aid/' . $iAttributeId));
    }

    /*
     * Responsible for editing the additional attributes values
     */
    public function editAttributevalue()
    {
        if (Yii::app()->request->getPost('oper') == "edit" && isset($_POST['attvalue']))
        {
            $pid = explode('_',Yii::app()->request->getPost('participant_id'));
            $iAttributeId =  Yii::app()->request->getPost('attid');
            if (Permission::model()->hasGlobalPermission('participantpanel','update') && Participant::model()->is_owner($pid[0]))
            {
                $aData = array('participant_id' => $pid[0], 'attribute_id' => $iAttributeId, 'value' => Yii::app()->request->getPost('attvalue'));
                ParticipantAttributeName::model()->editParticipantAttributeValue($aData);
            }
        }
    }

    /**
     * Show the drag-n-drop form for CSV attributes
     */
    public function attributeMapCSV()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','import'))
        {
            die('No permission');
        }
        if ($_FILES['the_file']['name']=='')
        {
            Yii::app()->setFlashMessage(gT('Please select a file to import!'),'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
        }
        $sRandomFileName=randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $sRandomFileName;
        $aPathinfo = pathinfo($_FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        if ($_FILES['the_file']['error']==1 || $_FILES['the_file']['error']==2)
        {
            Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize()/1024/1024),'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
            exit;
        }
        elseif (strtolower($sExtension)=='csv')
        {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
            $filterblankemails = Yii::app()->request->getPost('filterbea');
        }
        else
        {
            Yii::app()->setFlashMessage(gT("This is not a .csv file."),'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
            exit;
        }
        if (!$bMoveFileResult)
        {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),'error');
            Yii::app()->getController()->redirect(array('admin/participants/sa/importCSV'));
            exit;
        }
        else
        {
            $regularfields = array('firstname', 'participant_id', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid');
            $oCSVFile = fopen($sFilePath, 'r');
            $aFirstLine = fgets($oCSVFile);
            rewind($oCSVFile);

            $sSeparator = Yii::app()->request->getPost('separatorused');
            if ($sSeparator=='auto')
            {
                $aCount = array();
                $aCount[',']=substr_count($aFirstLine,',');
                $aCount[';']=substr_count($aFirstLine,';');
                $aCount['|']=substr_count($aFirstLine,'|');
                $aResult = array_keys($aCount, max($aCount));
                $sSeparator=$aResult[0];
            }
            $firstline = fgetcsv($oCSVFile, 1000, $sSeparator[0]);
            $selectedcsvfields = array();
            $fieldlist = array();
            foreach ($firstline as $key => $value)
            {
                $testvalue = preg_replace('/[^(\x20-\x7F)]*/','', $value); //Remove invalid characters from string
                if (!in_array(strtolower($testvalue), $regularfields))
                {
                    array_push($selectedcsvfields, $value);
                }
                $fieldlist[]=$value;
            }
            $iLineCount = count(array_filter(array_filter(file($sFilePath),'trim')));

            $attributes = ParticipantAttributeName::model()->model()->getCPDBAttributes();
            $aData = array(
                'attributes' => $attributes,
                'firstline' => $selectedcsvfields,
                'fullfilepath' => $sRandomFileName,
                'linecount' => $iLineCount - 1,
                'filterbea' => $filterblankemails,
                'participant_id_exists' => in_array('participant_id', $fieldlist)
            );
            App()->getClientScript()->registerPackage('qTip2');
            App()->getClientScript()->registerPackage('jquery-nestedSortable');
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'attributeMapCSV.js');

            $sAttributeMapJS="var copyUrl = '".App()->createUrl("admin/participants/sa/uploadCSV")."';\n"
            ."var displayParticipants = '".App()->createUrl("admin/participants/sa/displayParticipants")."';\n"
            ."var mapCSVcancelled = '".App()->createUrl("admin/participants/sa/mapCSVcancelled")."';\n"
            ."var characterset = '".sanitize_paranoid_string($_POST['characterset'])."';\n"
            ."var okBtn = '".gT("OK")."';\n"
            ."var processed = '".gT("Summary")."';\n"
            ."var summary = '".gT("Upload summary")."';\n"
            ."var notPairedErrorTxt = '".gT("You have to pair this field with an existing attribute.")."';\n"
            ."var onlyOnePairedErrorTxt = '".gT("Only one CSV attribute is mapped with central attribute.")."';\n"
            ."var cannotAcceptErrorTxt='".gT("This list cannot accept token attributes.")."';\n"
            ."var separator = '".sanitize_paranoid_string($_POST['separatorused'])."';\n"
            ."var thefilepath = '".$sRandomFileName."';\n"
            ."var filterblankemails = '".sanitize_paranoid_string($filterblankemails)."';\n";
            App()->getClientScript()->registerScript("sAttributeMapJS",$sAttributeMapJS,CClientScript::POS_BEGIN);
            $this->_renderWrappedTemplate('participants', 'attributeMapCSV', $aData);
        }
    }

    /**
     * Uploads the file to the server and process it for valid enteries and import them into database
     * Also creates attributes from the mapping drag-n-drop form.
     */
    public function uploadCSV()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','import'))
        {
            die('No permission');
        }
        unset(Yii::app()->session['summary']);
        $mappedarray = Yii::app()->request->getPost('mappedarray',false);
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
        $aGlobalErrors=array();
        /* If no mapped array */
        if(!$mappedarray)
            $mappedarray=array();
        /* Adjust system settings to read file with MAC line endings */
        @ini_set('auto_detect_line_endings', true);
        /* Open the uploaded file into an array */
        $tokenlistarray = file($sFilePath);

        // open it and trim the endings
        $separator = Yii::app()->request->getPost('separatorused');
        $uploadcharset = Yii::app()->request->getPost('characterset');
        /* The $newarray contains a list of fields that will be used
        to create attributes */
        $newarray = Yii::app()->request->getPost('newarray');
        if (!empty($newarray))
        {
            /* Create a new entry in the lime_participant_attribute_names table,
            and it's associated lime_participant_attribute_names_lang table
            for each NEW attribute being created in this import process */
            foreach ($newarray as $key => $value)
            {
                $aData = array('attribute_type' => 'TB', 'defaultname' => $value, 'visible' => 'FALSE');
                $insertid = ParticipantAttributeName::model()->storeAttributeCSV($aData);
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
            if ($recordcount == 0) {
                //The first time we iterate through the file we look at the very
                //first line, which contains field names, not values to import
                // Pick apart the first line
                $buffer = removeBOM($buffer);
                $allowedfieldnames = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted');
                $aFilterDuplicateFields = array('firstname', 'lastname', 'email');
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
                $firstline = str_getcsv($buffer, $separator, '"');
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
                $line = str_getcsv($buffer, $separator, '"');
                // Discard lines where the number of fields do not match
                if (count($firstline) != count($line))
                {
                    $invalidformatlist[] = $recordcount.','.count($line).','.count($firstline);
                    $recordcount++;
                    continue;
                }
                $writearray = array_combine($firstline, $line);
                //kick out ignored columns
                foreach ($ignoredcolumns as $column)
                {
                    unset($writearray[$column]);
                }
                // Add aFilterDuplicateFields not in CSV to writearray : quick fix
                foreach($aFilterDuplicateFields as $sFilterDuplicateField){
                    if(!in_array($sFilterDuplicateField, $firstline))
                        $writearray[$sFilterDuplicateField]="";
                }
                $dupfound = false;
                $thisduplicate = 0;

                //Check for duplicate participants
                //HACK - converting into SQL instead of doing an array search
                if(in_array('participant_id', $firstline)) {
                    $dupreason="participant_id";
                    $aData = "participant_id = ".Yii::app()->db->quoteValue($writearray['participant_id']);
                } else {
                    $dupreason="nameemail";
                    $aData = "firstname = ".Yii::app()->db->quoteValue($writearray['firstname'])." AND lastname = ".Yii::app()->db->quoteValue($writearray['lastname'])." AND email = ".Yii::app()->db->quoteValue($writearray['email'])." AND owner_uid = '".Yii::app()->session['loginID']."'";
                }
                //End of HACK
                $aData = Participant::model()->checkforDuplicate($aData, "participant_id");
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
                                        'value' => $writearray[strtolower($attname)]);
                                    ParticipantAttribute::model()->updateParticipantAttributeValue($bData);
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
                    // Ignore additional email addresses
                    $sEmailaddress = $aEmailAddresses[0];
                    if (!validateEmailAddress($sEmailaddress)) {
                        $invalidemail = true;
                        $invalidemaillist[] = $line[0] . " " . $line[1] . " (" . $line[2] . ")";
                    }
                }
                if (!$dupfound && !$invalidemail) {
                    //If it isn't a duplicate value or an invalid email, process the entry as a new participant

                    //First, process the known fields
                    if (!isset($writearray['participant_id']) || $writearray['participant_id'] == "") {
                        $uuid = Participant::gen_uuid(); //Generate a UUID for the new participant
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
                    if (($filterblankemails == "accept" && $writearray['email'] == "")) {
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
                                                ParticipantAttributeName::model()->saveParticipantAttributeValue($aData);
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
                        Participant::model()->insertParticipantCSV($writearray);
                        $imported++;
                    }
                }
                $mincriteria++;
            }
            $recordcount++;
        }

        unlink($sFilePath);
        $aData = array();
        $aData['recordcount'] = $recordcount - 1;
        $aData['duplicatelist'] = $duplicatelist;
        $aData['mincriteria'] = $mincriteria;
        $aData['imported'] = $imported;
        $aData['errorinupload'] = $errorinupload;
        $aData['invalidemaillist'] = $invalidemaillist;
        $aData['aInvalidFormatlist'] = $invalidformatlist;
        $aData['mandatory'] = $mandatory;
        $aData['invalidattribute'] = $invalidattribute;
        $aData['invalidparticipantid'] = $invalidparticipantid;
        $aData['overwritten'] = $overwritten;
        $aData['dupreason'] = $dupreason;
        $aData['aGlobalErrors'] = $aGlobalErrors;
        $this->getController()->renderPartial('/admin/participants/uploadSummary_view', $aData);
    }

    public function summaryview()
    {
        $this->_renderWrappedTemplate('participants', array('participantsPanel', 'uploadSummary'),array('aAttributes' => ParticipantAttributeName::model()->getAllAttributes()));
    }

    /**
     * Responsible for setting the session variables for attribute map page redirect
     */
    public function setSession()
    {
        unset(Yii::app()->session['participantid']);
        Yii::app()->session['participantid'] = Yii::app()->request->getPost('participantid');
    }

    /**
     * Stores the shared participant information in participant_shares
     */
    public function shareParticipants()
    {
        $iParticipantId = Yii::app()->request->getPost('participantid');
        $iShareUserId = Yii::app()->request->getPost('shareuser');
        $bCanEdit = Yii::app()->request->getPost('can_edit');

        // Some input validation needed
        if ($iShareUserId == '') {
            printf(gT("Please select a user"));
            return;
        }

        $i = 0;
    //  $iShareUserId == 0 means any user
        if (Permission::model()->hasGlobalPermission('participantpanel','update') && $iShareUserId !== '')
            foreach ($iParticipantId as $iId)
            {
                $time = time();
                $aData = array('participant_id' => $iId,
                    'share_uid' => $iShareUserId,
                    'date_added' => date('Y-m-d H:i:s', $time),
                    'can_edit' => $bCanEdit);
                ParticipantShare::model()->storeParticipantShare($aData);
                $i++;
        }
        printf(gT("%s participants have been shared"), $i);
    }

    /**
     * Responsible for copying the participant from tokens to the central Database
     *
     * TODO: Most of the work for this function is in the participants model file
     *       but it doesn't belong there.
     */
    public function addToCentral()
    {
        $newarr = Yii::app()->request->getPost('newarr');
        $mapped = Yii::app()->request->getPost('mapped');
        $overwriteauto = Yii::app()->request->getPost('overwriteauto');
        $overwriteman = Yii::app()->request->getPost('overwriteman');
        $createautomap = Yii::app()->request->getPost('createautomap');

        $response = Participant::model()->copyToCentral(Yii::app()->request->getPost('surveyid'), $newarr, $mapped, $overwriteauto, $overwriteman, $createautomap);

        echo "<p>";
        printf(gT("%s participants have been copied to the central participants table"), "<span class='badge alert-success'>" . $response['success'] . "</span>&nbsp;");
        echo "</p>";
        if($response['duplicate'] > 0) {
            echo "<p>";
            printf(gT("%s entries were not copied because they already existed"), "<span class='badge alert-warning'>" . $response['duplicate'] . "</span>&nbsp;");
            echo "</p>";
        }
        if($response['overwriteman']=="true" || $response['overwriteauto']) {
            echo "<p>";
            eT("Attribute values for existing participants have been updated from the token records");
            echo "</p>";
        }
    }

    /**
     * Responsible for adding the participant to the specified survey with attribute mapping
     * Used when mapping CPDB participants to survey tokens with attributes.
     * Called when user clicks "Continue" in that form.
     *
     * Echoes a result message witch will be displayed as a bootstrap modal
     *
     * @return void
     */
    public function addToTokenattmap()
    {
        $participantIdsString = Yii::app()->request->getPost('participant_id'); // TODO: This is a comma separated string of ids
        $participantIds = explode(",", $participantIdsString);

        $surveyId = Yii::app()->request->getPost('surveyid');

        /**
         * mapped can take values like
         *   mapped[attribute_38] = 39
         * meaning that an attribute is mapped onto another.
         */
        $mappedAttributes = Yii::app()->request->getPost('mapped');

        /**
         * newarr takes values like
         *   newarr[] = 39
         */
        $newAttributes = Yii::app()->request->getPost('newarr');

        $options = array();
        $options['overwriteauto'] = Yii::app()->request->getPost('overwrite') === 'true';
        $options['overwriteman'] = Yii::app()->request->getPost('overwriteman') === 'true';
        $options['overwritest'] = Yii::app()->request->getPost('overwritest') === 'true';
        $options['createautomap'] = Yii::app()->request->getPost('createautomap') === 'true';

        // TODO: Why?
        if (empty($newAttributes[0]))
        {
            $newAttributes = array();
        }

        if (empty($mappedAttributes))
        {
            $mappedAttributes = array();
        }

        try
        {
            $response = Participant::model()->copyCPDBAttributesToTokens($surveyId, $participantIds, $mappedAttributes, $newAttributes, $options);
        }
        // This exception carries error messages
        catch (CPDBException $e)
        {
            echo $e->getMessage();
            return;
        }
        catch (Exception $e)
        {
            printf("Error: Could not copy attributes to tokens: file %s, line %s; %s", $e->getFile(), $e->getLine(), $e->getMessage());
            return;
        }

        echo "<p>";
        printf(gT("%s participants have been copied to the survey token table"), "<span class='badge alert-success'>" . $response['success'] . "</span>");
        echo "</p>";
        if($response['duplicate']>0) {
            echo "<p>";
            printf(gT("%s entries were not copied because they already existed"), "<span class='badge alert-warning'>" . $response['duplicate'] . "</span>");
            echo "</p>";
        }
        if($response['blacklistskipped']>0) {
            echo "<p>";
            printf(gT("%s entries were skipped because they are blacklisted"), "<span class='badge alert-danger'>" . $response['blacklistskipped'] . "</span>");
            echo "</p>";
        }
        if($response['overwriteauto']=="true" || $response['overwriteman']=="true") {
            echo "<p>";
            eT("Attribute values for existing participants have been updated from the participants records");
            echo "</p>";
        }
    }

    /**
     * Show form for attribute mapping while copying participants from CPDB to token's table
     */
    public function attributeMap()
    {
        Yii::app()->loadHelper('common');
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'attributeMap.js');

        $iSurveyId = Yii::app()->request->getPost('survey_id');
        $redirect = Yii::app()->request->getPost('redirect');
        $count = Yii::app()->request->getPost('count');
        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $CPDBAttributes = ParticipantAttributeName::model()->getCPDBAttributes();
        $tokenAttributes = getTokenFieldsAndNames($iSurveyId, TRUE);

        $selectedattribute = array(); //List of existing attribute fields that are not mapped
        $selectedcentralattribute = array(); //List of attributes that haven't already been mapped
        $alreadymappedattid = array(); //List of fields already mapped to this tokens table
        $alreadymappedattname = array();

        foreach ($tokenAttributes as $attributeId => $attribute)  // attributeId like 'attribute_1'
        {
            if (is_numeric($attributeId[10])) //Assumes that if the 11th character is a number, it must be a token-table created attribute
            {
                $selectedattribute[$attributeId] = $attribute['description'];
            }
            else
            {
                array_push($alreadymappedattid, substr($attributeId, 15));
            }
        }
        foreach ($CPDBAttributes as $row)
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

        // Check for automatic mappings
        // TODO: Maybe do this with SQL instead?
        $automaticallyMappedAttributes = $this->getAutomaticallyMappedAttributes($tokenAttributes, $CPDBAttributes);

        // Remove automatic mappings from CPDB list (they should only be in right-most list)
        foreach ($automaticallyMappedAttributes as $autoAttr)
        {
            unset($selectedcentralattribute[$autoAttr['cpdbAttribute']['attribute_id']]);
        }

        $aData = array(
            'selectedcentralattribute' => $selectedcentralattribute,
            'selectedtokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattname,
            'automaticallyMappedAttributes' => $automaticallyMappedAttributes,
            'survey_id' => $iSurveyId,
            'redirect' => $redirect,
            'participant_id' => $iParticipantId,
            'count' => $count
        );

        if (count($selectedcentralattribute) === 0)
        {
            Yii::app()->setFlashMessage(gT("There are no unmapped attributes"), 'info');
        }

        $this->_renderWrappedTemplate('participants', 'attributeMap', $aData);
    }

    /**
     * This function is responsible for attribute mapping while copying participants from tokens to CPDB
     */
    public function attributeMapToken()
    {
        Yii::app()->loadHelper('common');
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'attributeMapToken.js');
        $this->registerCssFile( 'ADMIN', 'attributeMapToken.css' );

        $iSurveyID = (int)Yii::app()->request->getQuery('sid');
        $aCPDBAttributes = ParticipantAttributeName::model()->getCPDBAttributes();
        $aTokenAttributes = getTokenFieldsAndNames($iSurveyID, TRUE);

        $selectedattribute = array();
        $selectedcentralattribute = array();
        $alreadymappedattid = array();
        $alreadymappedattdisplay = array();
        $alreadymappedattnames = array();

        foreach ($aTokenAttributes as $key => $value)
        {
            if ($value['cpdbmap']=='')
            {
                $selectedattribute[$value['description']] = $key;
            }
            else
            {
                $attributeid=$value['cpdbmap'];
                $continue=false;
                foreach($aCPDBAttributes as $attribute) {
                    if($attribute['attribute_id']==$attributeid) {
                        $continue=true;
                    }
                }
                if($continue) {
                    $alreadymappedattid[]=$attributeid;
                    $alreadymappedattdisplay[]=$key;
                    $alreadymappedattnames[$key]=$value['description'];
                } else {
                    $selectedattribute[$value['description']]=$key;
                }
            }
        }
        foreach ($aCPDBAttributes as $row)
        {
            if (!in_array($row['attribute_id'], $alreadymappedattid))
            {
                $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
            }
        }

        if (count($selectedattribute) === 0)
        {
            Yii::app()->setFlashMessage(gT("There are no unmapped attributes"), 'warning');
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
    public function mapCSVcancelled()
    {
        if (!Permission::model()->hasGlobalPermission('participantpanel','import'))
        {
            die('No permission');
        }
        unlink(Yii::app()->getConfig('tempdir') . '/' . basename(Yii::app()->request->getPost('fullfilepath')));
    }

    public function blacklistParticipant()
    {
        $this->load->model('participants_model');
        $iParticipantId = $this->uri->segment(4);
        $iSurveyId = $this->uri->segment(5);

        $aData = array();

        if (!is_numeric($iSurveyId))
        {
            $blacklist = $this->uri->segment(5);
            if ($blacklist == 'Y' || $blacklist == 'N')
            {
                $aData = array('blacklisted' => $blacklist, 'participant_id' => $iParticipantId);
                $aData = $this->participants_model->blacklistparticipantglobal($aData);
                $aData['global'] = 1;
                $aData['blacklist'] = $blacklist;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
            else
            {
                $aData['is_participant'] = 0;
                $aData['is_updated'] = 0;
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
                $aData['local'] = 1;
                $aData['blacklist'] = $blacklist;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
            else
            {
                $aData['is_participant'] = 0;
                $aData['is_updated'] = 0;
                $this->load->view('admin/participants/blacklist_view', $aData);
            }
        }
    }

    /**
     * Return array of automatic mappings, pairing token attributes with CPDB attributes
     *
     * @param array $tokenAttributes
     * @param array $CPDBAttributes
     * @return array
     */
    private function getAutomaticallyMappedAttributes(array $tokenAttributes, array $CPDBAttributes)
    {
        $result = array();
        foreach ($tokenAttributes as $attributeId => $tokenAttribute)  // attributeId like 'attribute_1'
        {
            if ($tokenAttribute['cpdbmap'] !== '')
            {
                foreach ($CPDBAttributes as $CPDBAttribute)
                {
                    if ($CPDBAttribute['attribute_id'] == intval($tokenAttribute['cpdbmap']))
                    {
                        $result[$attributeId] = array(
                            'tokenAttributeId' => $attributeId,
                            'tokenAttribute' => $tokenAttribute,
                            'cpdbAttribute' => $CPDBAttribute
                        );
                    }
                }
            }
        }

        return $result;
    }

}
