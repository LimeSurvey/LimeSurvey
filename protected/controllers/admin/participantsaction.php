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
 */

use ls\models\Participant;
use ls\models\ParticipantAttributeName;
use ls\models\SettingGlobal;
use ls\models\Survey;
use ls\models\SurveyLink;
use ls\models\User;

function subval_sort($a, $subkey, $order)
{
    $b = [];
    $c = [];
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
        if (!App()->user->checkAccess('participantpanel'))
        {
            die('No permission');
        }
        parent::runWithParams($params);
    }



    /**
     * Loads jqGrid for the view
     * @param string $sScript Subaction
     */
    private function _loadjqGrid($sScript = '', $aData = [])
    {
        $aData['aAttributes'] = ParticipantAttributeName::model()->findAll();
        App()->getClientScript()->registerPackage('jqgrid');
        if (!empty($sScript))
        {
            App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . $sScript . '.js');
            $this->_renderWrappedTemplate('participants', ['participantsPanel', $sScript], $aData);
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'participants', $aViewUrls = [], $aData = [])
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
     * @param type $search  CDCriteria?
     * @paran mixed $mAttributeIDs Empty array for no attributes, or array of attribute IDs or null for all attributes
     */
    private function csvExport($search = null, $aAttributeIDs=null) {
        Yii::app()->loadHelper('export');

        //If super admin all the participants will be visible
        if (App()->user->checkAccess('superadmin'))
        {
            $iUserID = null;
        } else {
            $iUserID = App()->user->id;
        }
        $aAttributeIDs=array_combine($aAttributeIDs,$aAttributeIDs);
        $query = Participant::model()->getParticipants(0, 0, $aAttributeIDs, null, $search, $iUserID);
        if (!$query)
            return false;

        // Field names in the first row
        $fields = ['participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid'];
        $outputarray = []; // The array to be passed to the export helper to be written to a csv file

        $outputarray[0] = $fields; //fields written to output array

        // If attribute fields are selected, add them to the output
        if ($aAttributeIDs==null)
        {
            $aAttributes = ParticipantAttributeName::model()->findAll();
        }
        else
        {
            foreach ($aAttributeIDs as $value)
            {
                if ($value==0) continue;
                $fields[] = 'a'.$value;
                $attributename = ParticipantAttributeName::model()->getAttributeNames($value);
                $outputarray[0][] = $attributename[0]['attribute_name'];
            }
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
     * @return string|0
     */
    protected function csvExportCount($search = null)
    {
        $attid = ParticipantAttributeName::model()->getVisibleAttributes();

        //If super admin all the participants will be visible
        if (App()->user->checkAccess('superadmin'))
        {
            $iUserID = null;
        } else {
            $iUserID = App()->user->id;
        }


        $count = Participant::model()->getParticipantsCount($attid, $search, $iUserID);

        if ($count > 0) {
            return sprintf(ngT("Export %s participant to CSV|Export %s participants to CSV", $count),$count);
        } else {
            return $count;
        }
    }

    /**
     * Loads the view 'participantsPanel'
     */
    function index()
    {
        $iUserID = App()->user->id;

        // if superadmin all the records in the cpdb will be displayed
        if (App()->user->checkAccess('superadmin'))
        {
            $iTotalRecords = Participant::model()->count();
        }
        // if not only the participants on which he has right on (shared and owned)
        else
        {
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserID);
        }
        // gets the count of participants, their attributes and other such details
        $aData = [
            'totalrecords' => $iTotalRecords,
            'owned' => Participant::model()->count('owner_uid = ' . $iUserID),
            'shared' => Participant::model()->getParticipantsSharedCount($iUserID),
            'aAttributes' => ParticipantAttributeName::model()->findAll(),
            'attributecount' => ParticipantAttributeName::model()->count(),
            'blacklisted' => Participant::model()->count('owner_uid = ' . $iUserID . ' AND blacklisted = \'Y\'')
        ];
        // loads the participant panel and summary view
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'summary'], $aData);
    }

    /**
     * Loads the view 'importCSV'
     */
    function importCSV()
    {
        $aData = [
            'aAttributes' => ParticipantAttributeName::model()->findAll()
        ];
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'importCSV'],$aData);
    }

    /**
     * Loads the view 'displayParticipants' which contains the main grid
     */
    function displayParticipants()
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
        if (!App()->user->checkAccess('superadmin'))
            $surveys->permission(Yii::app()->user->getId());

        $aSurveyNames = $surveys->model()->with(['languagesettings'=> ['condition'=>'surveyls_language=language'], 'owner'])->findAll();

        /* Build a list of surveys that have tokens tables */
        $tSurveyNames= [];
        foreach($aSurveyNames as $row)
        {
            $row = array_merge($row->attributes, $row->defaultlanguage->attributes);
            $bTokenExists = tableExists('{{tokens_' . $row['sid'] . '}}');
            if ($bTokenExists) //If tokens table exists
            {
                $tSurveyNames[]=$row;
            }
        }
        // data to be passed to view
        $aData = [
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->findAll(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'surveynames' => $aSurveyNames,
            'tokensurveynames' => $tSurveyNames,
            'urlsearch' => $urlSearch,
            'sSearchCondition' => $sSearchCondition,
            'aAttributes' => ParticipantAttributeName::model()->findAll()
        ];
        App()->getClientScript()->registerPackage('jqgrid');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/'  . 'displayParticipants.css');


        // loads the participant panel view and display participant view
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'displayParticipants'], $aData);
    }

    /**
     * Loads the view 'blacklistControl'
     */
    function blacklistControl()
    {
        $aData = [
            'blacklistallsurveys' => Yii::app()->getConfig('blacklistallsurveys'),
            'blacklistnewsurveys' => Yii::app()->getConfig('blacklistnewsurveys'),
            'blockaddingtosurveys' => Yii::app()->getConfig('blockaddingtosurveys'),
            'hideblacklisted' => Yii::app()->getConfig('hideblacklisted'),
            'deleteblacklisted' => Yii::app()->getConfig('deleteblacklisted'),
            'allowunblacklist' => Yii::app()->getConfig('allowunblacklist'),
            'aAttributes' => ParticipantAttributeName::model()->findAll()
        ];
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'blacklist'], $aData);
    }

    /**
     * Loads the view 'userControl'
     */
    function userControl()
    {
        $aData = [
            'userideditable' => Yii::app()->getConfig('userideditable'),
            'aAttributes' => ParticipantAttributeName::model()->findAll()
        ];
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'userControl'], $aData);
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
        if (App()->user->checkAccess('superadmin'))
        {
            $records = Participant::model()->getParticipantSharedAll();
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                $oShared = User::model()->getName($row['share_uid']); //for conversion of uid to human readable names
                $owner = User::model()->getName($row['owner_uid']);
                $aData->rows[$i]['id'] = $row['participant_id']."--".$row['share_uid']; //This is the unique combination per record
                $aData->rows[$i]['cell'] = [$row['firstname'], $row['lastname'], $row['email'], $oShared[0]['full_name'], $row['share_uid'], $owner[0]['full_name'], $row['date_added'], $row['can_edit']];
                $i++;
            }

            echo ls_json_encode($aData);
        }
        // otherwise only the shared participants by that user
        else
        {
            $records = Participant::model()->getParticipantShared(App()->user->id);
            $aData->records = count($records);
            $aData->total = ceil($aData->records / 10);
            $i = 0;

            foreach ($records as $row)
            {
                $sharename = User::model()->getName($row['share_uid']); //for conversion of uid to human readable names
                $aData->rows[$i]['id'] = $row['participant_id'];
                $aData['rows'][$i]['cell'] = [$row['firstname'], $row['lastname'], $row['email'], $sharename['full_name'], $row['share_uid'], $row['date_added'], $row['can_edit']];
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
            ParticipantShare::model()->deleteRow($shareIds);
        }
        else
        {
            $aData = [
                'participant_id' => Yii::app()->request->getPost('participant_id'),
                'can_edit' => Yii::app()->request->getPost('can_edit'),
                'share_uid' => Yii::app()->request->getPost('shared_uid')
            ];
            ParticipantShare::model()->updateShare($aData);
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

        $page = Yii::app()->request->getPost('page');
        $limit = Yii::app()->request->getPost('rows');
        $limit = isset($limit) ? $limit : 50; //Stop division by zero errors
        $records = ParticipantAttributeName::model()->with('participant_attribute_names_lang')->findAll(['order'=>'attribute_name']);
        $attribute_types = [
            'DD' => gT("Drop-down list"),
            'DP' => gT("Date"),
            'TB' => gT("Text box")
        ];
        $aData = new stdClass();
        $aData->page = $page;
        $aData->records = count($records);
        $aData->total = ceil(ParticipantAttributeName::model()->getCPDBAttributes(true) / $limit);
        $i = 0;
        foreach($records as $row) { //Iterate through each attribute
            $sAttributeCaption=htmlspecialchars($row->defaultname); //Choose the first item by default
            foreach($row->participant_attribute_names_lang as $names) { //Iterate through each language version of this attribute
                if($names->lang == Yii::app()->session['adminlang']) {$sAttributeCaption= $sAttributeCaption.htmlspecialchars(" ({$names->attribute_name})");} //Override the default with the admin language version if found
            }
            $aData->rows[$i]['id'] = $row->attribute_id;
            $aData->rows[$i]['cell'] = ['', $sAttributeCaption, $attribute_types[$row->attribute_type], $row->visible];
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
            $aData = [
                'defaultname' => Yii::app()->request->getPost('attribute_name'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible')? 'TRUE' : 'FALSE'
            ];
            echo ParticipantAttributeName::model()->storeAttribute($aData);
        }
        elseif ($operation == 'edit' && Yii::app()->request->getPost('id'))
        {
            $aData = [
                'attribute_id' => Yii::app()->request->getPost('id'),
                'attribute_name' => Yii::app()->request->getPost('attribute_name'),
                'attribute_type' => Yii::app()->request->getPost('attribute_type'),
                'visible' => Yii::app()->request->getPost('visible','FALSE') != 'FALSE' ? 'TRUE' : 'FALSE'
            ];
            ParticipantAttributeName::model()->saveAttribute($aData);
            eT("Attribute display setting updated");
        }

    }

    /**
     * Takes the delete call from the display participants and take appropriate action depending on the condition
     */
    function delParticipant()
    {
        if (App()->user->checkAccess('participantpanel', ['crud' => 'delete']))
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
    function editParticipant()
    {
        $sOperation = Yii::app()->request->getPost('oper');

        // if edit it will update the row
        if ($sOperation == 'edit' && App()->user->checkAccess('participantpanel', ['crud' => 'update']) && Participant::model()->is_owner(Yii::app()->request->getPost('id')))
        {
            $aData = [
                'participant_id' => Yii::app()->request->getPost('id'),
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted')
            ];
            Participant::model()->updateRow($aData);
        }
        // if add it will insert a new row
        elseif ($sOperation == 'add' && App()->user->checkAccess ('participantpanel',['crud' => 'create']))
        {
            $uuid = $this->gen_uuid();
            $aData = [
                'participant_id' => $uuid,
                'firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'language' => Yii::app()->request->getPost('language'),
                'blacklisted' => Yii::app()->request->getPost('blacklisted'),
                'owner_uid' => App()->user->id,
                'created_by' => App()->user->id
            ];
            Participant::model()->insertParticipant($aData);
        }
    }

    /**
     * Stores the user control setting to the database
     */
    function storeUserControlValues()
    {
        if ($find = SettingGlobal::model()->findByPk('userideditable'))
        {
            SettingGlobal::model()->updateByPk('userideditable', ['stg_value'=>Yii::app()->request->getPost('userideditable')]);
        }
        else
        {
            $stg = new SettingGlobal;
            $stg ->stg_name='userideditable';
            $stg ->stg_value=Yii::app()->request->getPost('userideditable');
            $stg->save();
        }
        Yii::app()->getController()->redirect(['admin/participants/sa/userControl']);
    }

    /**
     * Stores the blacklist setting to the database
     */
    function storeBlacklistValues()
    {
        $values = ['blacklistallsurveys', 'blacklistnewsurveys', 'blockaddingtosurveys', 'hideblacklisted', 'deleteblacklisted', 'allowunblacklist', 'userideditable'];
        foreach ($values as $value)
        {
            if ($find = SettingGlobal::model()->findByPk($value))
            {
                SettingGlobal::model()->updateByPk($value, ['stg_value'=>Yii::app()->request->getPost($value)]);
            }
            else
            {
                $stg = new SettingGlobal;
                $stg ->stg_name=$value;
                $stg ->stg_value=Yii::app()->request->getPost($value);
                $stg->save();
            }
        }
        Yii::app()->getController()->redirect(['admin/participants/sa/blacklistControl']);
    }

    /**
     * Receives an ajax call containing the participant id in the fourth segment of the url
     * Supplies list of survey links - surveys of which this participant is on the tokens table
     * URL: [localurl]/limesurvey/admin/participants/getSurveyInfo_json/pid/[participant_id]
     * RETURNS: json data containing linked survey information (ls\models\Survey name, survey id, token_id and date_added)
     */
    function getSurveyInfo_json()
    {
        $participantid = Yii::app()->request->getQuery('pid');
        $records = SurveyLink::model()->findAllByAttributes((['participant_id' => $participantid]));
        $aData = new stdClass();
        $aData->page = 1;
        $aData->records = count($records);
        $aData->total = ceil($aData->records / 10);
        $i = 0;
        foreach ($records as $row)
        {
            $oSurvey=Survey::model()->with(['languagesettings'=> ['condition'=>'surveyls_language=language']])->findByAttributes(['sid' => $row['survey_id']]);
            foreach($oSurvey->languagesettings as $oLanguageSetting)
            {
                $surveyname= $oLanguageSetting->surveyls_title;
            }
            $surveylink = "";
            /* Check permissions of each survey before creating a link*/
            if (!App()->user->checkAccess('tokens', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $row['survey_id']]))
            {
                $surveylink = $row['survey_id'];
            } else
            {
                $surveylink = '<a href=' . Yii::app()->getController()->createUrl("/admin/tokens/sa/browse/surveyid/{$row['survey_id']}") . '>' . $row['survey_id'].'</a>';
            }
            $aData->rows[$i]['cell'] = [$surveyname, $surveylink, $row['token_id'], $row['date_created'], $row['date_invited'], $row['date_completed']];
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
        /** @var Participant $participant */
        $participant = Participant::model();
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
            if (!App()->user->checkAccess('superadmin')) //If super admin all the participants will be visible
            {
                $participant->accessibleTo(App()->user->id);
            }

            $count = $participant->count();

            printf(gT("%s participant(s) are to be copied "), $count);
        }
    }

    /**
     * Gets the ids of participants to be copied to the individual survey
     */
    function getSearchIDs()
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
                if (App()->user->checkAccess('superadmin'))
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
            if (App()->user->checkAccess('superadmin')) //If super admin all the participants will be visible
            {
                $query = Participant::model()->findAll(); // get all the participant id if it is a super admin
            }
            else // get participants on which the user has right on
            {
                $query = Participant::model()->accessibleTo(App()->user->id)->findAll();
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
    function exporttocsv()
    {
        if (Yii::app()->request->getPost('searchcondition','') != '') // if there is a search condition then only the participants that match the search criteria are counted
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
    function getParticipantsResults_json()
    {
        $searchcondition = Yii::app()->request->getpost('searchcondition');
        $finalcondition = [];
        $condition = explode("||", $searchcondition);
        $search = Participant::model()->getParticipantsSearchMultipleCondition($condition);
        return $this->getParticipants_json($search);
    }

    /*
       * Sends the data in JSON format extracted from the database to be displayed using the jqGrid
    */
    function getParticipants_json($search = null)
    {
        $page = (int) Yii::app()->request->getPost('page');
        $limit = (int) Yii::app()->request->getPost('rows');
        $limit = empty($limit) ? 50:$limit; //Stop division by zero errors

        $attid = ParticipantAttributeName::model()->getVisibleAttributes();
        $participantfields = ['participant_id', 'can_edit', 'firstname', 'lastname', 'email', 'blacklisted', 'survey', 'language', 'owner_uid'];
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
        if (App()->user->checkAccess('superadmin'))
        {
            $iUserID = null;
        } else {
            $iUserID = App()->user->id;
        }
        $aData->records = Participant::model()->getParticipantsCount($attid, $search, $iUserID);
        $aData->total = ceil($aData->records / $limit);
        if ($page>$aData->total) {
            $page = $aData->total;
        }
        $aData->page = $page;
        $records = Participant::model()->getParticipants($page, $limit,$attid, $order, $search, $iUserID);


        $aRowToAdd= [];
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
            $aRowToAdd['cell'] = [$row['participant_id'], $sCanEdit, htmlspecialchars($row['firstname']), htmlspecialchars($row['lastname']), htmlspecialchars($row['email']), $row['blacklisted'], $row['survey'], $row['language'], $row['ownername']];
            $aRowToAdd['id'] = $row['participant_id'];
            // add attribute values
            foreach($row as $key=>$attvalue)
            {
                if(preg_match('/^a\d+$/', $key))
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
    function getAttribute_json()
    {
        $iParticipantId = strip_tags(Yii::app()->request->getQuery('pid'));
        $records = ParticipantAttributeName::model()->getParticipantVisibleAttribute($iParticipantId);
        $records = subval_sort($records, "attribute_name", "asc");

        $i = 0;

        $doneattributes = []; //If the user has any actual attribute values, they'll be stored here

        /* Iterate through each attribute owned by this user */
        foreach ($records as $row)
        {
            $outputs[$i] = ["", $row['participant_id']."_".$row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], $row['value']];
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
        $attributenotdone= [];
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
            $outputs[$i] = ["", $iParticipantId."_".$row['attribute_id'], $row['attribute_type'], $row['attribute_id'], $row['attribute_name'], ""];
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
        $aData->rows[0]['cell'] = [];
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
     * Responsible for showing the additional attribute for central database
     */
    function viewAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = [
            'attributes' => ParticipantAttributeName::model()->getAttribute($iAttributeId),
            'attributenames' => ParticipantAttributeName::model()->getAttributeNames($iAttributeId),
            'attributevalues' => ParticipantAttributeName::model()->getAttributesValues($iAttributeId),
            'aAttributes' => ParticipantAttributeName::model()->findAll()
        ];
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/'.'participants.css');
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/'.'viewAttribute.css');
        App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . "viewAttribute.js");
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'viewAttribute'], $aData);
    }

    /*
     * Responsible for saving the additional attribute. It iterates through all the new attributes added dynamically
     * and iterates through them
     */
    function saveAttribute()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $aData = [
            'attribute_id' => $iAttributeId,
            'attribute_type' => Yii::app()->request->getPost('attribute_type'),
            'defaultname' => Yii::app()->request->getPost('defaultname'),
            'visible' => Yii::app()->request->getPost('visible')
        ];
        ParticipantAttributeName::model()->saveAttribute($aData);

        foreach ($_POST as $key => $value)
        {
            // check for language code in the post variables this is a hack as the only way to check for language data
            if (strlen($key) == 2)
            {
                $langdata = [
                    'attribute_id' => $iAttributeId,
                    'attribute_name' => $value,
                    'lang' => $key
                ];

                ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
            }
        }
        if (Yii::app()->request->getPost('langdata'))
        {
            $langdata = [
                'attribute_id' => $iAttributeId,
                'attribute_name' => Yii::app()->request->getPost('attname'),
                'lang' => Yii::app()->request->getPost('langdata')
            ];

            ParticipantAttributeName::model()->saveAttributeLanguages($langdata);
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
                    $aDatavalues[$i] = [
                        'attribute_id' => $iAttributeId,
                        'value' => Yii::app()->request->getPost($attvaluename)
                    ];
                }
                $attvaluename = 'attribute_value_name_' . ++$i;
            };
            ParticipantAttributeName::model()->storeAttributeValues($aDatavalues);
        }
        /* Save updated attribute values */
        if (Yii::app()->request->getPost('editbox') || Yii::app()->request->getPost('editbox')=="0")
        {
            $editattvalue = [
                'attribute_id' => $iAttributeId,
                'value_id' => Yii::app()->request->getPost('value_id'),
                'value' => Yii::app()->request->getPost('editbox')
            ];
            ParticipantAttributeName::model()->saveAttributeValue($editattvalue);
        }
        Yii::app()->getController()->redirect(['admin/participants/sa/attributeControl']);
    }

    /*
     * Responsible for deleting the additional attribute values in case of drop down.
     */
    function delAttributeValues()
    {
        $iAttributeId = Yii::app()->request->getQuery('aid');
        $iValueId = Yii::app()->request->getQuery('vid');
        ParticipantAttributeName::model()->delAttributeValues($iAttributeId, $iValueId);
        Yii::app()->getController()->redirect(['/admin/participants/sa/viewAttribute/aid/' . $iAttributeId]);
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
            if (App()->user->checkAccess('participantpanel', ['crud' => 'update']) && Participant::model()->is_owner($pid[0]))
            {
                $aData = ['participant_id' => $pid[0], 'attribute_id' => $iAttributeId, 'value' => Yii::app()->request->getPost('attvalue')];
                ParticipantAttributeName::model()->editParticipantAttributeValue($aData);
            }
        }
    }

    function attributeMapCSV()
    {

        if ($_FILES['the_file']['name']=='')
        {
            Yii::app()->setFlashMessage(gT('Please select a file to import!'),'error');
            Yii::app()->getController()->redirect(['admin/participants/sa/importCSV']);
        }
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
            $templateData['errorinupload']['error'] = gT("This is not a .csv file.");
            $templateData['aAttributes'] = ParticipantAttributeName::model()->findAll();
            $templateData['aGlobalErrors'] = [];
          //  $errorinupload = array('error' => $this->upload->display_errors());
          //  Yii::app()->session['summary'] = array('errorinupload' => $errorinupload);
            $this->_renderWrappedTemplate('participants', ['participantsPanel', 'uploadSummary'],$templateData);
            exit;
        }


        if (!$bMoveFileResult)
        {
            $templateData['error_msg'] = sprintf(gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), Yii::app()->getConfig('tempdir'));
            $errorinupload = ['error' => $this->upload->display_errors()];
            Yii::app()->session['summary'] = ['errorinupload' => $errorinupload];
            $this->_renderWrappedTemplate('participants', ['participantsPanel', 'uploadSummary'],
                ['aAttributes' => ParticipantAttributeName::model()->findAll()]);
        }
        else
        {
            $aData = ['upload_data' => $_FILES['the_file']];
            $sFileName = $_FILES['the_file']['name'];

            $regularfields = ['firstname', 'participant_id', 'lastname', 'email', 'language', 'blacklisted', 'owner_uid'];
            $oCSVFile = fopen($sFilePath, 'r');
            $aFirstLine = fgets($oCSVFile);
            rewind($oCSVFile);

            $sSeparator = Yii::app()->request->getPost('separatorused');
            if ($sSeparator=='auto')
            {
                $aCount[',']=substr_count($aFirstLine,',');
                $aCount[';']=substr_count($aFirstLine,';');
                $aCount['|']=substr_count($aFirstLine,'|');
                $aResult = array_keys($aCount, max($aCount));
                $sSeparator=$aResult[0];
            }
            $firstline = fgetcsv($oCSVFile, 1000, $sSeparator[0]);
            $selectedcsvfields = [];
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
            $aData = [
                'attributes' => $attributes,
                'firstline' => $selectedcsvfields,
                'fullfilepath' => $sRandomFileName,
                'linecount' => $iLineCount - 1,
                'filterbea' => $filterblankemails,
                'participant_id_exists' => in_array('participant_id', $fieldlist)
            ];
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstysleurl') . "attributeMapCSV.css");
        App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . "attributeMapCSV.js");

        $sAttributeMapJS="var copyUrl = '".App()->createUrl("admin/participants/sa/uploadCSV")."';\n"
                        ."var displayParticipants = '".App()->createUrl("admin/participants/sa/displayParticipants")."';\n"
                        ."var mapCSVcancelled = '".App()->createUrl("admin/participants/sa/mapCSVcancelled")."';\n"
                        ."var characterset = '".\ls\helpers\Sanitize::paranoid_string($_POST['characterset'])."';\n"
                        ."var okBtn = '".gT("OK")."';\n"
                        ."var processed = '".gT("Summary")."';\n"
                        ."var summary = '".gT("Upload summary")."';\n"
                        ."var notPairedErrorTxt = '".gT("You have to pair this field with an existing attribute.")."';\n"
                        ."var onlyOnePairedErrorTxt = '".gT("Only one CSV attribute is mapped with central attribute.")."';\n"
                        ."var cannotAcceptErrorTxt='".gT("This list cannot accept token attributes.")."';\n"
                        ."var separator = '".\ls\helpers\Sanitize::paranoid_string($_POST['separatorused'])."';\n"
                        ."var thefilepath = '".$sRandomFileName."';\n"
                        ."var filterblankemails = '".$filterblankemails."';\n";
        App()->getClientScript()->registerScript("sAttributeMapJS",$sAttributeMapJS,CClientScript::POS_BEGIN);
            $this->_renderWrappedTemplate('participants', 'attributeMapCSV', $aData);
        }
    }

  
    function summaryview()
    {
        $this->_renderWrappedTemplate('participants', ['participantsPanel', 'uploadSummary'],
            ['aAttributes' => ParticipantAttributeName::model()->findAll()]);
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
        $iParticipantId = Yii::app()->request->getPost('participantid');
        $iShareUserId = Yii::app()->request->getPost('shareuser');
        $bCanEdit = Yii::app()->request->getPost('can_edit');

        $i = 0;
        foreach ($iParticipantId as $iId)
        {
            $time = time();
            $aData = [
                'participant_id' => $iId,
                'share_uid' => $iShareUserId,
                'date_added' => date('Y-m-d H:i:s', $time),
                'can_edit' => $bCanEdit
            ];
            ParticipantShare::model()->storeParticipantShare($aData);
            $i++;
        }

        printf(gT("%s participants have been shared"), $i);
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

        $response = Participant::model()->copyToCentral(Yii::app()->request->getPost('surveyid'), $newarr, $mapped, $overwriteauto, $overwriteman, $createautomap);

        printf(gT("%s participants have been copied to the central participants table"), $response['success']);
        if($response['duplicate'] > 0) {
            echo "\r\n";
            printf(gT("%s entries were not copied because they already existed"), $response['duplicate']);
        }
        if($response['overwriteman']=="true" || $response['overwriteauto']) {
            echo "\r\n";
            eT("Attribute values for existing participants have been updated from the token records");
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

        if (empty($newcreate[0])) { $newcreate = []; }

        $response = Participant::model()->copyCPBDAttributesToTokens($iSurveyId, $mapped, $newcreate, $iParticipantId, $overwriteauto, $overwriteman, $overwritest, $createautomap);

        printf(gT("%s participants have been copied to the survey token table"), $response['success']);
        if($response['duplicate']>0) {
            echo "\r\n";
            printf(gT("%s entries were not copied because they already existed"), $response['duplicate']);
        }
        if($response['blacklistskipped']>0) {
            echo "\r\n";
            printf(gT("%s entries were skipped because they are blacklisted"), $response['blacklistskipped']);
        }
        if($response['overwriteauto']=="true" || $response['overwriteman']=="true") {
            echo "\r\n";
            eT("Attribute values for existing participants have been updated from the participants records");
        }
    }

    /*
     * Responsible for attribute mapping while copying participants from cpdb to token's table
     */
    function attributeMap()
    {
        Yii::app()->loadHelper('common');
        App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . "attributeMap.js");
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' ."attributeMap.css");

        $iSurveyId = Yii::app()->request->getPost('survey_id');
        $redirect = Yii::app()->request->getPost('redirect');
        $count = Yii::app()->request->getPost('count');
        $iParticipantId = Yii::app()->request->getPost('participant_id');
        $attributes = ParticipantAttributeName::model()->getCPDBAttributes();
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

        $selectedattribute = []; //List of existing attribute fields that are not mapped
        $selectedcentralattribute = []; //List of attributes that haven't already been mapped
        $alreadymappedattid = []; //List of fields already mapped to this tokens table
        $alreadymappedattname = [];
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

        $aData = [
            'selectedcentralattribute' => $selectedcentralattribute,
            'selectedtokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattname,
            'survey_id' => $iSurveyId,
            'redirect' => $redirect,
            'participant_id' => $iParticipantId,
            'count' => $count
        ];

        $this->_renderWrappedTemplate('participants', 'attributeMap', $aData);
    }

    /*
     * This function is responsible for attribute mapping while copying participants from tokens to CPDB
     */
    function attributeMapToken()
    {
        Yii::app()->loadHelper('common');
        App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . "attributeMapToken.js");
        App()->getClientScript()->registerCssFile(App()->publicUrl . '/styles/gringegreen/' ."attributeMapToken.css");

        $iSurveyID = (int)Yii::app()->request->getQuery('sid');
        $aCPDBAttributes = ParticipantAttributeName::model()->getCPDBAttributes();
        $aTokenAttributes = getTokenFieldsAndNames($iSurveyID, TRUE);

        $selectedattribute = [];
        $selectedcentralattribute = [];
        $alreadymappedattid = [];
        $alreadymappedattdisplay = [];
        $alreadymappedattnames = [];
        $i = 0;
        $j = 0;

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

        $aData = [
            'attribute' => $selectedcentralattribute,
            'tokenattribute' => $selectedattribute,
            'alreadymappedattributename' => $alreadymappedattdisplay,
            'alreadymappedattdescription' => $alreadymappedattnames
        ];

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
        if (!is_numeric($iSurveyId))
        {
            $blacklist = $this->uri->segment(5);
            if ($blacklist == 'Y' || $blacklist == 'N')
            {
                $aData = ['blacklisted' => $blacklist, 'participant_id' => $iParticipantId];
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
                $aData = ['blacklisted' => $blacklist];
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

}


