<?php
/*
 * This is the main controller for Participants Panel
 */
class participantsaction extends CAction
{

/**
* Routes the action into correct subaction
*
* @access protected
* @param string $sa
* @param array $get_vars
* @return void
*/
protected function route($sa)
{
    return call_user_func_array(array($this, $sa),array());
}

function run($sa = '') {
    if(!empty($sa)) {
        $this->route($sa);
    }
    else {
        CController::redirect(Yii::app()->createUrl('admin/participants/sa/index'));
    }
}

/**
 * This function is responsible for loading the view 'participantsPanel'
 * @param null
 * @return Default cpdb page
*/
function index()
{
    //$this->load->model('participants_model');
    //$this->load->model('participant_attribute_model');
    $iUserID = Yii::app()->session['loginID'];
    $this->getController()->_getAdminHeader();
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) // if superadmin all the records in the cpdb will be displayed
    {
        $iTotalRecords = Participants::model()->count();        
    }
    else                                                 // if not only the participants on which he has right on (shared and owned)
    {
       $iTotalRecords=Participants::getParticipantsOwnerCount($iUserID);
    }
    // gets the count of participants, their attributes and other such details
    $iShared = Participants::getParticipantsSharedCount($iUserID);
    $iOwned = Participants::model()->count('owner_uid = '.$iUserID);
    $iBlacklisted = Participants::model()->count('owner_uid = '.$iUserID.' AND blacklisted = \'Y\'');
    $iAttributeCount = ParticipantAttributeNames::model()->count();
    $clang = $this->getController()->lang;
    $aData = array('clang'=> $clang,
                  'totalrecords' => $iTotalRecords,
                  'owned' => $iOwned,
                  'shared' => $iShared,
                  'attributecount' => $iAttributeCount,
                  'blacklisted' => $iBlacklisted
                  );
    // loads the participant panel and summary view
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/summary_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'importCSV'
 * @param null
 * @return import CSV view
*/
function importCSV()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $aData = array('clang'=> $clang);
    $this->load->view('admin/participants/participantsPanel_view',$aData);
    $this->load->view('admin/participants/importCSV_view',$aData);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'displayParticipants' which contains the main grid
 * @param null
 * @return display participants view
*/
function displayParticipants()
{
    $this->getController()->_js_admin_includes( Yii::app()->getConfig('generalscripts')."jquery/jqGrid/js/i18n/grid.locale-en.js");
    $this->getController()->_js_admin_includes( Yii::app()->getConfig('generalscripts')."jquery/jqGrid/js/jquery.jqGrid.min.js");
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/css/jquery.multiselect.css";
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/css/jquery.multiselect.filter.css";
    $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/displayParticipants.css";
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css";
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/jquery.ui.datepicker.css";
    Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
    $this->getController()->_getAdminHeader();
    $clang = $this->getController()->lang;
    //$this->load->model('users_model');
    //$this->load->model('participant_attribute_model');
    //$this->load->model('surveys_model');
    //$this->load->model('participants_model');
    $getNames= Yii::app()->db->createCommand()->select('uid,full_name')->from('{{users}}')->queryAll();
    $attributes = ParticipantAttributeNames::getVisibleAttributes();
    $allattributes = ParticipantAttributeNames::getAllAttributes();
    $attributeValues =ParticipantAttributeNames::getAllAttributesValues();
    // loads the survey names to be shown in add to survey
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN'])  // if user is superadmin, all survey names
    {
     $surveynames = Survey::getAllSurveyNames();
    }
    else                                                   // otherwise owned by him
    {
        $surveynames =  Survey::getSurveyNames();
    }
    // data to be passed to view
    $aData = array('names'=> $getNames,
                  'attributes' => $attributes,
                  'allattributes' => $allattributes,
                  'attributeValues' => $attributeValues,
                  'surveynames' =>$surveynames,
                  'clang'=> $clang );
    // loads the participant panel view and display participant view
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/displayParticipants_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'blacklistControl'
 * @param null
 * @return blacklist control view
*/
function blacklistControl()
{
    $this->getController()->_getAdminHeader();
    $clang = $this->getController()->lang;
    $aData = array('clang'=> $clang);
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'attributeControl'
 * @param null
 * @return attribute control view
*/
function attributeControl()
{

    $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/participants.css";
    Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
    $this->getController()->_getAdminHeader();
    $clang = $this->getController()->lang;
    $aData = array('clang'=> $clang,'result'=>ParticipantAttributeNames::getAttributes());
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/attributeControl_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'userControl'
 * @param null
 * @return user control view
*/
function userControl()
{
    $this->getController()->_getAdminHeader();
    $clang = $this->getController()->lang;
    $aData = array('clang'=> $clang,
                  'userideditable'=>Yii::app()->getConfig("userideditable"));
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/userControl_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function is responsible for loading the view 'sharePanel'
 * @param null
 * @return share panel view
*/
function sharePanel()
{
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/js/i18n/grid.locale-en.js");
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/js/jquery.jqGrid.min.js");
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/plugins/jquery.searchFilter.js");
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/src/grid.celledit.js");
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts')."sharePanel.js");
    $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts')."jquery/jqGrid/js/i18n/grid.locale-en.js");
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/ui.jqgrid.css";
    $css_admin_includes[] = Yii::app()->getConfig('generalscripts')."jquery/jqGrid/css/jquery.ui.datepicker.css";
    Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
    $this->getController()->_getAdminHeader();
    $clang = $this->getController()->lang;
    $aData = array('clang'=> $clang);
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/sharePanel_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/**
 * This function sends the shared participant info to the share panel using JSON encoding
 * This function is called after the share panel grid is loaded
 * This function returns the json depending on the user logged in by checking it from the session
 * @param it takes the session user data loginID
 * @return JSON encoded string containg sharing information
 */
function getShareInfo_json()
{
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) // If super administrator all the share info in the links table will be shown
    {
        $records = Participants::model()->getParticipantSharedAll();
        $aData->page = 1;
        $aData->records =count(Participants::model()->getParticipantSharedAll());
        $aData->total =ceil($aData->records /10 );
        $i=0;
        foreach($records as $row)
        {
            $oShared = User::model()->getName($row['share_uid']); //for conversion of uid to human readable names
            $owner = User::model()->getName($row['owner_uid']);
            $aData->rows[$i]['id']=$row['participant_id'];
            $aData->rows[$i]['cell']=array($row['firstname'],$row['lastname'],$row['email'],$oShared['full_name'],$row['share_uid'],$owner['full_name'],$row['date_added'],$row['can_edit']);
            $i++;
        }
        echo ls_json_encode($aData);
    }
    else            // otherwise only the shared participants by that user
    {
        $records = Participants::model()->getParticipantShared(Yii::app()->session['loginID']);
        $aData->page = 1;
        $aData->records =Participants::model()->getParticipantShared(Yii::app()->session['loginID'])->count();
        $aData->total =ceil($aData->records /10 );
        $i=0;
        foreach($records->readAll() as $row)
        {
                $sharename = Users::model()->getName($row->share_uid); //for conversion of uid to human readable names
                $aData->rows[$i]['id']=$row->participant_id;
                $aData->rows[$i]['cell']=array($row['firstname'],$row['lastname'],$row['email'],$sharename['full_name'],$row['share_uid'],$row['date_added'],$row['can_edit']);
                $i++;
        }
        echo ls_json_encode($aData);
    }
}
/**
 *  This function takes the edit call from the share panel, which either edits or deletes the share information
 *  Basically takes the call on can_edit
 *  @param takes parameters from post request
 *  @return NULL
 */
function editShareInfo()
{
    @$operation = $_POST['oper'];
    if($operation == 'del') // If operation is delete , it will delete, otherwise edit it
    {
        Participant_shares::model()->deleteRow($_POST);
    }
    $aData = array( 'participant_id' => @$_POST['participant_id'],
                   'can_edit' => @$_POST['can_edit'],
                   'share_uid' => @$_POST['shared_uid']);
    Participant_shares::model()->updateShare($aData);
}
/**
 * This funtion takes the delete call from the display participants and take appropriate action depending on the condition
 * @param takes from post
 * @return NULL
 */
function delParticipant()
{
    $selectoption = CHttpRequest::getPost('selectedoption');
    $participant_id = CHttpRequest::getPost('participant_id');
    // Deletes from participants only
    if($selectoption=="po")
    {
      Participants::deleteParticipant($participant_id);
    }
    // Deletes from central and token table
    elseif($selectoption=="ptt")
    {
       Participants::deleteParticipantToken($participant_id);
    }
    // Deletes from central , token and assosiated responses as well
    else
    {
       Participants::deleteParticipantTokenAnswer($participant_id);
    }
}
/**
 * This function is resposible for editing data on the jqGrid
 * @param takes from post
 * @return null
 */
function editParticipant()
{
    $operation = CHttpRequest::getPost('oper');
    //In case the uid is not editable, then user id is not posted and hence the current user is added in the uid
    if(CHttpRequest::getPost('owner_uid')=='')
    {
        $oid=Yii::app()->session['loginID'];
    }
    //otherwise the one which is posted is added
    else
    {
        $oid = CHttpRequest::getPost('owner_uid');
    }
    if(CHttpRequest::getPost('language')=='')
    {
        $lang=Yii::app()->session['adminlang'];
    }
    else
    {
        $lang = CHttpRequest::getPost('language');
    }
    // if edit it will update the row
    if($operation == 'edit')
    {
        $aData = array(
        'participant_id' => CHttpRequest::getPost('id'),
        'firstname' => CHttpRequest::getPost('firstname'),
        'lastname' => CHttpRequest::getPost('lastname'),
        'email' => CHttpRequest::getPost('email'),
        'language' => CHttpRequest::getPost('language'),
        'blacklisted' => CHttpRequest::getPost('blacklisted'),
        'owner_uid' => $oid);
        Participants::updateRow($aData);
    }
    // if add it will insert a new row
    elseif($operation == 'add')
    {
        $uuid = $this->gen_uuid();
        $aData = array('participant_id' => $uuid,
                      'firstname' => CHttpRequest::getPost('firstname'),
                      'lastname' => CHttpRequest::getPost('lastname'),
                      'email' => CHttpRequest::getPost('email'),
                      'language' => CHttpRequest::getPost('language'),
                      'blacklisted' => CHttpRequest::getPost('blacklisted'),
                      'owner_uid' => $oid);
        Participants::insertParticipant($aData);
    }
}
/**
 * This function is sotres the user control setting to the database
 * @param takes from post
 * @return null
 */
function storeUserControlValues()
{
    $this->load->model('users_model');
    $this->load->model('settings_global_model');
    $this->settings_global_model->updateSetting('userideditable',$_POST['userideditable']);
    redirect('admin/participants/userControl');
}
/**
 * This function recieves an ajax call containing the participant id in the fourth segment of the url
 * It fetches the links to get all the surveys with which a particular participant is assosiated with
 * @param from the uri segment
 * @return json string cotaining all the links information to be displayed in the subgrid
 */
function getSurveyInfo_json()
{
    //$this->load->model('survey_links_model');
    //$this->load->model('surveys_languagesettings_model');
    $participantid = CHttpRequest::getQuery('pid');
    $records = Yii::app()->db->createCommand()->select('token_id,survey_id,date_created')->from('{{survey_links}}')->where('participant_id = "'.$participantid.'"')->queryAll();
    $aData->page = 1;
    $aData->records = count ($records);
    $aData->total = ceil ($aData->records /10 );
    $i=0;
    foreach($records as $row)
    {
        $surveyname = Surveys_languagesettings::getSurveyNames($row['survey_id']);
        $aData->rows[$i]['cell']=array($surveyname[0]['surveyls_title'],"<a href=".Yii::app()->baseUrl."admin/tokens/browse"."/".$row['survey_id'].">".$row['survey_id'],$row['token_id'],$row['date_created']);
        $i++;
    }
    echo ls_json_encode($aData);
}
/**
 * This function returns the count of the participants in the CSV and show it in the title of the modal box
 * This is to give the user the hint to see the number of participants he is exporting
 * @param takes the search condition using post
 * @return the echo statement telling the number of participants exporting
 */
function exporttocsvcount()
{
    $searchconditionurl = CHttpRequest::getPost('searchcondition');
    $searchcondition = basename($searchconditionurl);
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants in the cpdb are counted
    {
        if($searchcondition != 'getParticipants_json') // if there is a search condition then only the participants that match the search criteria are counted
        {
            $condition = explode("||",$searchcondition);
            if(count($condition)==3)
            {
                $query = Participants::getParticipantsSearch($condition,0,0);
            }
            else
            {
                $query = Participants::getParticipantsSearchMultiple($condition,0,0);
            }
        }
        else // if no search criteria all the participants will be counted
        {
            $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
        }
    }
    else // If no search criteria it will simply return the number of participants
    {
        $iUserID = Yii::app()->session['loginID'];
        $query = Particiapnts::getParticipantsOwner($iUserID);
    }
    $clang = $this->getController()->lang;
    echo sprintf($clang->gT("Export %s participant(s) to CSV  "),count($query));
}
/**
 * This function returns the count of participants when using the export all button on the top
 * @param uses post data to get the user id
 * @return the echo statement telling the number of participants exporting
 */
function exporttocsvcountAll()
{
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants in the central table will be counted
    {
        $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }
    else // otherwise only the participants on which the logged in user has the rights
    {
        $iUserID = Yii::app()->session['loginID'];
        $query = Participants::getParticipantsOwner($iUserID);
    }
    $clang = $this->getController()->lang;
    if(count($query) > 0 ) // If count is greater than 0 it will show the message
    {
        echo sprintf($clang->gT("Export %s participant(s) to CSV  "),count($query));
    }
    else // else it will return a numeric count which will tell that there is no participant to be exported
    {
        echo count($query);
    }
}
/**
 * This function is responsible to export all the participants in the central table
 * @param get user id from the session variable
 * @return Exported CSV file
 */
function exporttocsvAll()
{
    Yii::app()->loadHelper("export");  // loads the export helper    
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be exported
    {
        $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }
    else // otherwise only the ones over which the user has rights on
    {
        $iUserID = Yii::app()->session['loginID'];
        $query = Participants::getParticipantsOwner($iUserID);
    }
    if(!$query)
       return false;
    // These are the consistent fields that will be exported
    $fields = array ('participant_id','firstname','lastname' ,'email' ,'language' ,'blacklisted','owner_uid' );
    $i = 0;
    $outputarray = array();
    foreach ($fields as $field)
    {
        $outputarray[0][$i]=$field; // The fields are being added to the index 0 of the array to be written to the header of the csv file
        $i++;
    }
    $attributenames = ParticipantAttributeNames::getAttributes();
    // Attribute names are being added to the index 0 of the array
    foreach($attributenames as $key=>$value)
    {
        $outputarray[0][$i]=$value['attribute_name'];
        $i++;
    }
    // Fetching the table data
    $i = 1;
    $j = 0;
    // Read through the query result and add it to the array
    // Please not it will give only basic field in the central database
    foreach($query as $field => $aData)
    {
        foreach ($fields as $field)
        {
            $outputarray[$i][$j]=$aData[$field];
            //increment the column
            $j++;
        }

        // it will iterate through the additional attributes that the user has choosen to export and will fetch the values
        // that are to be exported to the CSV file
        foreach($attributenames as $key=>$value)
        {
            $answer=ParticipantAttributeNames::getAttributeValue($aData['participant_id'],$value['attribute_id']);
            if(isset($answer['value']))
            { // if the attribute value is there for that attribute and the user then it will written to the array
                $outputarray[$i][$j]=$answer['value'];
                //increment the column
                $j++;
            }
            else
            { // otherwise blank value will be written to the array
                $outputarray[$i][$j]="";
                //increment the column
                $j++;
            }
        }
        // increment the row
        $i++;
    }
    // Load the helper and pass the array to be written to a CSV file
    
    cpdb_export($outputarray,"central_".time());
}
/**
 * This function is similar to export to all message where it counts the number to participants to be copied
 * and echo them to be displayed in modal box header
 * @param $_POST['searchcondition']
 * @return echo string containing the number of the participants that are to be copied to the survey
 */
function getaddtosurveymsg()
{
    $this->load->model('participants_model');
    $searchcondition = basename($this->input->post('searchcondition'));
    if($searchcondition != 'getParticipants_json') // If there is a search condition in the url of the jqGrid
    {
        $participantid = "";
        $condition = explode("||",$searchcondition);
        if(count($condition)==3) // If there is no and condition , if the count is equal to 3 that means only one condition
        {
            $query = $this->participants_model->getParticipantsSearch($condition,0,0);
        }
        else  // if there are 'and' and 'or' condition in the condition the count is to be greater than 3
        {
            $query = $this->participants_model->getParticipantsSearchMultiple($condition,0,0);
        }
        $clang = $this->limesurvey_lang;
        echo sprintf($clang->gT("%s participant(s) are to be copied "),count($query));
   }
    else // if there is no search condition the participants will be counted on the basis of who is logged in
    {
        $participantid = "";
        if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
            $query = $this->participants_model->getParticipantswithoutlimit();
        }
        else
        {
            $query = $this->participants_model->getParticipantsOwner($this->session->userdata('loginID'));
        }
        $clang = $this->limesurvey_lang;
        echo sprintf($clang->gT("%s participant(s) are to be copied "),count($query->result_array()));

    }
}
/**
 * This function is used for getting the id's of participants to be copied to the indivisual survey
 * @param : $_POST['searchcondition']
 * @return : echoes the participant id returned using the search id
 */
function getSearchIDs()
{
    $this->load->model('participants_model');
    $searchcondition = basename($this->input->post('searchcondition')); // get the search condition from the URL
    if($searchcondition != 'getParticipants_json') // if there is a search condition present
    {
        $participantid = "";
        $condition = explode("||",$searchcondition);  // explode the condition to teh array
        // format for the condition is field||condition||value
        if(count($condition)==3) // if count is 3 , then it's a single search
        {
            $query = $this->participants_model->getParticipantsSearch($condition,0,0);
        }
        else// if count is more than 3 , then it's a multiple search
        {
            $query = $this->participants_model->getParticipantsSearchMultiple($condition,0,0);
        }
        foreach($query as $key=>$value)
        {
            $participantid  = $participantid.",".$value['participant_id']; // combine the participant id's in an string

        }
        echo $participantid; //echo the participant id's
    }
    else// if no search condition
    {
        $participantid = ""; // initiallise the participant id to blank
        if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
            $query = $this->participants_model->getParticipantswithoutlimit(); // get all the participant id if it is a super admin
        }
        else // get participants on which the user has right on
        {
            $query = $this->participants_model->getParticipantsOwner($this->session->userdata('loginID'));
        }

        foreach($query->result_array() as $key=>$value)
        {
            $participantid  = $participantid.",".$value['participant_id']; // combine the participant id's in an string
        }
    echo $participantid; //echo the participant id's
    }
}
/**
 * This function is responsible for reading the CSV file line by line, check for duplicate participants
 * invalid participants and invalid attributes and copy them to the central table
 * This function is also responsible for creation of new attribute and mapping of old attribute to attribute in csv
 * @param $_POST['searchcondition']
 * @return summary of the csv upload
 */
function exporttocsv()
{
    Yii::app()->loadHelper('export');
    $searchconditionurl = CHttpRequest::getPost('searchcondition');
    $searchcondition = basename($searchconditionurl);
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be visible
    {
        if($searchcondition != 'getParticipants_json') // If there is a search condition then only does participants are exported
        {
            $condition = explode("||",$searchcondition);
            if(count($condition)==3) // Single search
            {
                $query = Participants::getParticipantsSearch($condition,0,0);
            }
            else //combined search
            {
                $query = Participants::getParticipantsSearchMultiple($condition,0,0);
            }
        } // else all the participants in the central table will be exported since it's superadmin
        else
        {
            $query = Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
        }

    }
    else
    {
        $iUserID = Yii::app()->session['loginID']; // else only the
        $query = Participants::getParticipantsOwner($iUserID);
    }
    if(!$query)
    return false;
    // Field names in the first row
    $fields = array ('participant_id','firstname','lastname' ,'email' ,'language' ,'blacklisted','owner_uid' );
    $i = 0;
    $outputarray = array();// The array to be passed to the export helper to be written to a csv file
    foreach ($fields as $field)
    {
        $outputarray[0][$i]=$field;//fields written to output array
        $i++;
    }
    if(CHttpRequest::getQuery('id') == "null")
    {
        $i = 1;
        $j = 0;
        foreach($query as $field => $aData)
        {
            foreach ($fields as $field)
            {
                $outputarray[$i][$j]=$aData[$field];
                $j++;
            }
            $i++;
         }
         cpdb_export($outputarray,"central_".time());
    }
    else
    {
        $attribute_id=explode(",",CHttpRequest::getQuery('id'));
        foreach($attribute_id as $key=>$value)
        {
            $attributename = ParticipantAttributeNames::getAttributeNames($value);
            $outputarray[0][$i]=$attributename[0]['attribute_name'];
            $i++;
        }
        $i = 1;
        $j = 0;
        // Fetching the table data
        foreach($query as $field => $aData)
        {
            foreach ($fields as $field)
            {
                $outputarray[$i][$j]=$aData[$field];
                $j++;
            }
            foreach($attribute_id as $key=>$value)
            {
                $answer=ParticipantAttributeNames::getAttributeValue($aData['participant_id'],$value);
                if(isset($answer['value']))
                {
                    $outputarray[$i][$j]=$answer['value'];
                    $j++;
                }
                else
                {
                    $outputarray[$i][$j]="";
                    $j++;
                }
            }
            $i++;
        }
        cpdb_export($outputarray,"central_".time());
    }
}
function getParticipantsResults_json()
{
    $page = CHttpRequest::getPost('page');
    $limit = CHttpRequest::getPost('rows');    
    $attid = ParticipantAttributeNames::getAttributeVisibleID();
    $participantfields = array('participant_id','can_edit','firstname','lastname','email','blacklisted','surveys','language','owner_uid');
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be visible
    {
        $searchcondition = CHttpRequest::getQuery('search');
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||",$searchcondition);
        if(count($condition)==3)
        {

            $records = Participants::getParticipantsSearch($condition,$page,$limit);
            $aData->page = $page;
            $aData->records = count (Participants::getParticipantsSearch($condition,0,0));
            $aData->total = ceil ($aData->records /$limit );
        }
        else
        {
            $records = Participants::getParticipantsSearchMultiple($condition,$page,$limit);
            $aData->page = $page;
            $aData->records = count (Participants::getParticipantsSearchMultiple($condition,0,0));
            $aData->total = ceil ($aData->records /$limit );
        }
        $i=0; 
        foreach($records as $row=>$value)
        {
            $username = User::getName($value['owner_uid']);//for conversion of uid to human readable names
            $surveycount = Participants::getSurveyCount($value['participant_id']);
            $sortablearray[$i]=array($value['participant_id'],"true",$value['firstname'],$value['lastname'],$value['email'],$value['blacklisted'],$surveycount,$value['language'],$username[0]['full_name']);// since it's the admin he has access to all editing on the participants inspite of what can_edit option is
            $attributes =  ParticipantAttributeNames::getParticipantVisibleAttribute($value['participant_id']);
            foreach($attid as $attributeid)
            {
                $answer=ParticipantAttributeNames::getAttributeValue($value['participant_id'],$attributeid['attribute_id']);
                if(isset($answer['value']))
                {
                    array_push($sortablearray[$i],$answer['value']);
                }
                else
                {
                    array_push($sortablearray[$i],"");
                }
            }
            $i++;
        }
        function subval_sort($a,$subkey,$order)
        {
            foreach($a as $k=>$v)
            {
                $b[$k] = strtolower($v[$subkey]);
            }
            if($order == "asc")
            {
                asort($b,SORT_REGULAR);
            }
            else
            {
                arsort($b,SORT_REGULAR);
            }
            foreach($b as $key=>$val)
            {
                $c[] = $a[$key];
            }
            return $c;
        }
        if(!empty($sortablearray))
        {
            $indexsort = array_search(CHttpRequest::getPost('sidx'), $participantfields);
            $sortedarray = subval_sort($sortablearray,$indexsort,CHttpRequest::getPost('sord'));
            $i=0;
            $count = count($sortedarray[0]);
            foreach($sortedarray as $key=>$value)
            {
                $aData->rows[$i]['id']=$value[0];   
                $aData->rows[$i]['cell'] = array();
                for($j=0 ; $j < $count ; $j++)
                {
                    array_push($aData->rows[$i]['cell'],$value[$j]);
                }
                $i++;
            }
        }
        echo ls_json_encode($aData);
    }
    else // Only the owned and shared participants will be visible
    {
        $searchcondition = CHttpRequest::getQuery('search');
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||",$searchcondition);
        if(count($condition)==3)
        {
            $records = Participants::getParticipantsSearch($condition,$page,$limit);
            $aData->page = $page;
        }
        else
        {
            $records = Participants::getParticipantsSearchMultiple($condition,$page,$limit);
            $aData->page = $page;
        }
        $i=0;
        foreach($records as $row=>$value)
        {
            if(Participants::is_owner($value['participant_id']))
            {
                $username = User::getName($value['owner_uid']);//for conversion of uid to human readable names
                $surveycount = Participants::getSurveyCount($value['participant_id']);
                $sortablearray[$i]=array($value['participant_id'],"true",$value['firstname'],$value['lastname'],$value['email'],$value['blacklisted'],$surveycount,$value['language'],$username[0]['full_name']);// since it's the admin he has access to all editing on the participants inspite of what can_edit option is
                $attributes =  ParticipantAttributeNames::getParticipantVisibleAttribute($value['participant_id']);
                foreach($attid as $attributeid)
                {
                    $answer=ParticipantAttributeNames::getAttributeValue($value['participant_id'],$attributeid['attribute_id']);
                    if(isset($answer['value']))
                    {
                        array_push($sortablearray[$i],$answer['value']);
                    }
                    else
                    {
                        array_push($sortablearray[$i],"");
                    }
                }
            $i++;
            }
        }
        function subval_sort($a,$subkey,$order)
        {
            foreach($a as $k=>$v)
            {
                $b[$k] = strtolower($v[$subkey]);
            }
            if($order == "asc")
            {
                asort($b,SORT_REGULAR);
            }
            else
            {
                arsort($b,SORT_REGULAR);
            }
            foreach($b as $key=>$val)
            {
                $c[] = $a[$key];
            }
            return $c;
        }
        if(!empty($sortablearray))
        {
            $aData->records = count($sortablearray);
            $aData->total = ceil (count($sortablearray) /$limit );
            $indexsort = array_search(CHttpRequest::getPost('sidx'), $participantfields);
            $sortedarray = subval_sort($sortablearray,$indexsort,CHttpRequest::getPost('sord'));
            $i=0;
            $count = count($sortedarray[0]);
            foreach($sortedarray as $key=>$value)
            {
                $aData->rows[$i]['id']=$value[0];
                $aData->rows[$i]['cell'] = array();
                for($j=0 ; $j < $count ; $j++)
                {
                    array_push($aData->rows[$i]['cell'],$value[$j]);
                }
                $i++;
            }
        }
        echo ls_json_encode($aData);
    }

}
/*
 * This function sends the data in JSON format extracted from the database to be displayed using the jqGrid
 * Parameters : None
 * Return Data : echo the JSON encoded participants data
 */
function getParticipants_json()
{
    $page = CHttpRequest::getPost('page');
    $limit = CHttpRequest::getPost('rows');
    //$this->load->model('participants_model');
    //$this->load->model('participant_attribute_model');
    //$this->load->model('users_model');
    $attid = ParticipantAttributeNames::getAttributeVisibleID();
    $participantfields = array('participant_id','can_edit','firstname','lastname','email','blacklisted','surveys','language','owner_uid');
    foreach($attid as $key=>$value)
    {
        array_push($participantfields,$value['attribute_name']);
    }
    if(Yii::app()->session['USER_RIGHT_SUPERADMIN']) //If super admin all the participants will be visible
    {
        $records = Participants::getParticipants($page,$limit);
        $aData->page = $page;
        $aData->records = Participants::model()->count();
        $aData->total = ceil ( $aData->records / $limit );
        $i=0;
        $sortablearray=array();
        foreach($records as $key => $row)
        {
            $username = User::getName(@$row['owner_uid']);//for conversion of uid to human readable names            
            $surveycount = Participants::getSurveyCount($row['participant_id']);
            $sortablearray[$i]=array(@$row['participant_id'],"true",@$row['firstname'],@$row['lastname'],@$row['email'],@$row['blacklisted'],$surveycount,@$row['language'] ,$username['full_name']);// since it's the admin he has access to all editing on the participants inspite of what can_edit option is
            $attributes =  ParticipantAttributeNames::getParticipantVisibleAttribute($row['participant_id']);
            foreach($attid as $attributeid)
            {
                $answer=ParticipantAttributeNames::getAttributeValue($row['participant_id'],$attributeid['attribute_id']);
                if(isset($answer['value']))
                {
                    array_push($sortablearray[$i],$answer['value']);
                }
                else
                {
                    array_push($sortablearray[$i],"");
                }
            }
            $i++;
        }
        function subval_sort($a,$subkey,$order)
        {
            foreach($a as $k=>$v)
            {
                $b[$k] = strtolower($v[$subkey]);
            }
            if($order == "asc")
            {
                asort($b,SORT_REGULAR);
            }
            else
            {
                arsort($b,SORT_REGULAR);
            }
            foreach($b as $key=>$val)
            {
                $c[] = $a[$key];
            }
            return $c;
        }
        $indexsort = array_search(CHttpRequest::getPost('sidx'), $participantfields);
        $sortedarray = subval_sort($sortablearray,$indexsort,CHttpRequest::getPost('sord'));
        $i=0;
        $count = count($sortedarray[0]);
        foreach($sortedarray as $key=>$value)
        {
            $aData->rows[$i]['id']=$value[0];
            $aData->rows[$i]['cell'] = array();
            for($j=0 ; $j < $count ; $j++)
            {
                array_push($aData->rows[$i]['cell'],$value[$j]);
            }
            $i++;
        }
        echo ls_json_encode($aData);
    }
    else // Only the owned and shared participants will be visible
    {

        $iUserID = Yii::app()->session['loginID'];
        $records = Participants::getParticipantsOwner($iUserID);
        $aData->page = $page;
        $aData->records = count($records);
        $aData->total = ceil($aData->records/$limit);
        $attid = ParticipantAttributeNames::getAttributeVisibleID();
        $i=0;
        foreach($records as $row)
        {
            $surveycount = Participants::getSurveyCount($row['participant_id']);
            $ownername = User::getName($row['owner_uid']); //for conversion of uid to human readable names
            $sortablearray[$i]=array($row['participant_id'],$row['can_edit'],$row['firstname'],$row['lastname'],$row['email'],$row['blacklisted'],$surveycount,$row['language'],$ownername[0]['full_name']);
            $attributes =  ParticipantAttributeNames::getParticipantVisibleAttribute($row['participant_id']);
            foreach($attid as $attributeid)
                {
                    $answer=ParticipantAttributeNames::getAttributeValue($row['participant_id'],$attributeid['attribute_id']);
                    if(isset($answer['value']))
                    {
                        array_push($sortablearray[$i],$answer['value']);
                    }
                    else
                    {
                        array_push($sortablearray[$i],"");
                    }
                }
            $i++;
        }
        function subval_sort($a,$subkey,$order)
        {
            foreach($a as $k=>$v)
            {
                $b[$k] = strtolower($v[$subkey]);
            }
            if($order == "asc")
            {
                asort($b,SORT_REGULAR);
            }
            else
            {
                arsort($b,SORT_REGULAR);
            }
            foreach($b as $key=>$val)
            {
                $c[] = $a[$key];
            }
            return $c;
        }
        $indexsort = array_search(CHttpRequest::getPost('sidx'), $participantfields);
        $sortedarray = subval_sort($sortablearray,$indexsort,CHttpRequest::getPost('sord'));
        $i=0;
        $count = count($sortedarray[0]);
        foreach($sortedarray as $key=>$value)
        {
            $aData->rows[$i]['id']=$value[0];
            $aData->rows[$i]['cell'] = array();
            for($j=0 ; $j < $count ; $j++)
            {
                array_push($aData->rows[$i]['cell'],$value[$j]);
            }
            $i++;
        }
        echo ls_json_encode($aData);
    }
}
/*
 * This function fetches the attributes of a participant to be displayed in the attribute subgrid
 */
function getAttribute_json()
{
    $participant_id=CHttpRequest::getQuery('pid');
    $records = ParticipantAttributeNames::getParticipantVisibleAttribute($participant_id);
    $getallattributes = ParticipantAttributeNames::getAttributes();
    $aData->page = 1;
    $aData->records = count ($records);
    $aData->total = ceil ($aData->records /10 );
    $aData->rows[0]['id']=$participant_id;
    $aData->rows[0]['cell'] = array();
    $i=0;
    $doneattributes = array();
    foreach($records as $row)
    {
        $aData->rows[$i]['id']=$row['participant_id']."_".$row['attribute_id'];
        $aData->rows[$i]['cell']=array("",$row['participant_id'],$row['attribute_type'],$row['attribute_name'],$row['value']);
        if($row['attribute_type']=="DD")
        {
            $attvalues = ParticipantAttributeNames::getAttributesValues($row['attribute_id']);
            if(!empty($attvalues))
            {
                $attval="";
                foreach($attvalues as $val)
                {
                    $attval .= $val['value'].":".$val['value'];
                    $attval .= ";";
                }
                $attval = substr($attval,0,-1);
                array_push($aData->rows[$i]['cell'],$attval);
            }
            else
            {
                array_push($aData->rows[$i]['cell'],"");
            }
        }
        else
        {
            array_push($aData->rows[$i]['cell'],"");
        }
        array_push($doneattributes,$row['attribute_id']);
        $i++;
    }
    if(count($doneattributes)==0)
    {
        $attributenotdone = ParticipantAttributeNames::getAttributes();
    }
    else
    {
        $attributenotdone = ParticipantAttributeNames::getnotaddedAttributes($doneattributes);
    }
    if($attributenotdone>0)
    {
        foreach($attributenotdone as $row)
        {

            $aData->rows[$i]['id']=$participant_id."_".$row['attribute_id'];
            $aData->rows[$i]['cell']=array("",$participant_id,$row['attribute_type'],$row['attribute_name'],"");
            if($row['attribute_type']=="DD")
            {
                $attvalues = ParticipantAttributeNames::getAttributesValues($row['attribute_id']);
                if(!empty($attvalues))
                {
                    $attval="";
                    foreach($attvalues as $val)
                    {
                        $attval .= $val['value'].":".$val['value'];
                        $attval .= ";";
                    }
                    $attval = substr($attval,0,-1);
                    array_push($aData->rows[$i]['cell'],$attval);
                }
                else
                {
                    array_push($aData->rows[$i]['cell'],"");
                }
            }
            else
            {
                array_push($aData->rows[$i]['cell'],"");
            }
            $i++;
        }
    }
    echo ls_json_encode($aData);
}
/*
 * This function gets the data from the form for add participants and pass it to the participants model
 * Parameters : everything is extracted from the post data
 * Return Data : None
 */
function storeParticipants()
{
    $aData = array('participant_id' => uniqid(),
    'firstname' => CHttpRequest::getPost('firstname'),
    'lastname' => CHttpRequest::getPost('lastname'),
    'email' => CHttpRequest::getPost('email'),
    'language' => CHttpRequest::getPost('language'),
    'blacklisted' => CHttpRequest::getPost('blacklisted'),
    'owner_uid' => CHttpRequest::getPost('owner_uid'));
    
    Participants::insertParticipant($aData);
}
/*
 * This function is responsible for showing the additional attribute for central database
 */
function viewAttribute()
{
    $attribute_id = CHttpRequest::getQuery('aid');
    $attributes=ParticipantAttributeNames::getAttribute($attribute_id);
    $attributenames = ParticipantAttributeNames::getAttributeNames($attribute_id);
    $attributevalues=ParticipantAttributeNames::getAttributesValues($attribute_id);
    $clang = $this->getController()->lang;
    $aData = array('attributes' => $attributes,
                  'attributevalues' => $attributevalues,
                  'attributenames' => $attributenames,
                  'clang'=> $clang);
    $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/participants.css";
    $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/viewAttribute.css";
    Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
    $this->getController()->_getAdminHeader();
    $this->getController()->render('/admin/participants/participantsPanel_view',$aData);
    $this->getController()->render('/admin/participants/viewAttribute_view',$aData);
    $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for saving the additional attribute. It iterates through all the new attributes added dynamically
 * and iterates through them
 */
function saveAttribute()
    {
        $aData = array('attribute_id'    => CHttpRequest::getQuery('aid'),
                      'attribute_type'  => CHttpRequest::getPost('attribute_type'),
                      'visible'  => CHttpRequest::getPost('visible'));
        ParticipantAttributeNames::saveAttribute($aData);
        foreach($_POST as $key=>$value)
        {
            if(strlen($key) == 2) // check for language code in the post variables this is a hack as the only way to check for language data
            {
                $langdata = array( 'attribute_id' => CHttpRequest::getQuery('aid'),
                                   'attribute_name' => $value,
                                   'lang' => $key     );

                ParticipantAttributeNames::saveAttributeLanguages($langdata);
            }
        }
        if(isset($_POST['attribute_value_name_1']))
        {
            $i=1;
            do
            {
                $attvaluename = 'attribute_value_name_'.$i;
                if(!empty($_POST[$attvaluename]))
                {
                    $aDatavalues[$i] = array('attribute_id' => CHttpRequest::getQuery('aid'),
                                            'value' => CHttpRequest::getPost($attvaluename));
                }
                $i++;
            }while(isset($_POST[$attvaluename]));
            ParticipantAttributeNames::storeAttributeValues($aDatavalues);
        }

        if(isset($_POST['editbox']))
        {
            $editattvalue = array('value_id'=> $_POST['value_id'],
                                  'attribute_id'=> $this->uri->segment(4),
                                  'value' => $_POST['editbox']);
            ParticipantAttributeNames::saveAttributeValue($editattvalue);
        }
        CController::redirect(Yii::app()->createUrl('admin/participants/sa/attributeControl'));
}
/*
 * This function is responsible for deleting the additional attribute.
 */
function delAttribute()
{
    $attribute_id = CHttpRequest::getQuery('aid');
    ParticipantAttributeNames::delAttribute($attribute_id);
    CController::redirect(Yii::app()->createUrl('/admin/participants/sa/attributeControl'));
}
/*
 * This function is responsible for deleting the additional attribute values in case of drop down.
 */
function delAttributeValues()
{
    $attribute_id = CHttpRequest::getQuery('aid');
    $value_id = CHttpRequest::getQuery('vid');
    ParticipantAttributeNames::delAttributeValues($attribute_id,$value_id);
    CController::redirect(Yii::app()->createUrl('/admin/participants/sa/viewAttribute/'.$attribute_id));
}
/*
 * This function is responsible for deleting the storing the additional attributes
 */
function storeAttributes()
{
    $i=1;
    do
    {
        $attname = 'attribute_name_'.$i;
        $atttype = 'attribute_type_'.$i;
        $visible = 'visible_'.$i;
        if(!empty($_POST[$attname]))
        {
            $aData = array('attribute_name' => CHttpRequest::getPost($attname),
                          'attribute_type' => CHttpRequest::getPost($atttype),
                          'visible' => CHttpRequest::getPost($visible));
            ParticipantAttributeNames::storeAttribute($aData);
        }
        $i++;
    }while(isset($_POST[$attname]));

    CController::redirect('attributeControl');
}
/*
 * This function is responsible for editing the additional attributes values
 */
function editAttributevalue()
{
    if(CHttpRequest::getPost('oper')=="edit")
    {
            $attributeid = explode("_",CHttpRequest::getPost('id'));
            $aData = array('participant_id' => CHttpRequest::getPost('participant_id'),'attribute_id' => $attributeid[1],'value' => CHttpRequest::getPost('attvalue'));
            ParticipantAttributeNames::editParticipantAttributeValue($aData);
    }
}
function attributeMapCSV()
{
    $config['upload_path'] = './tmp/uploads';
    $config['allowed_types'] = 'text/x-csv|text/plain|application/octet-stream|csv';
    $config['max_size']	= '1000';
    $this->load->library('upload',$config);
    $errorinupload = "";
    if (!$this->upload->do_upload())
    {
        $errorinupload = array('error' => $this->upload->display_errors());
        $this->session->unset_userdata('summary');
        $aData = array( 'errorinupload' => $errorinupload);
        $this->session->set_userdata('summary',$aData);
        self::_getAdminHeader();
        $clang = $this->limesurvey_lang;
        $aData = array('clang'=> $clang);
        $this->load->view('admin/participants/participantsPanel_view',$aData);
        $this->load->view('admin/participants/uploadSummary_view',$aData);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
    else
    {
        $aData = array('upload_data' => $this->upload->data());
        $filename = $this->upload->data('full_path');
        $the_full_file_path = base_url().'tmp/uploads/'.$filename['file_name'];
        $regularfields = array('firstname','participant_id','lastname','email','language','blacklisted','owner_uid');
        $csvread = fopen($the_full_file_path,'r');

        $seperator = $this->input->post('seperatorused');
        $firstline = fgetcsv($csvread, 1000,',');
        $selectedcsvfields = array();
        foreach($firstline as $key=>$value)
        {
            if(!in_array($value,$regularfields))
            {
                array_push($selectedcsvfields,$value);
            }
        }
        $linecount = count(file($the_full_file_path));
        $this->load->model('participant_attribute_model');
        //$separator = $this->input->post('seperatorused');
        self::_getAdminHeader();
        $clang = $this->limesurvey_lang;
        $attributes = $this->participant_attribute_model->getAttributes();
        $aData = array('clang'=> $clang,
                      'attributes'=> $attributes,
                      'firstline'=> $selectedcsvfields,
                      'fullfilepath' => $the_full_file_path,
                      'linecount' => $linecount-1
                );
        $this->load->view('admin/participants/attributeMapCSV_view',$aData);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
}
/*
 * Uploads the file to the server and process it for valid enteries and import them into database
 */
function uploadCSV()
{
            $this->session->unset_userdata('summary');
            $characterset = $this->input->post('characterset');
            $seperator = $this->input->post('seperatorused');
            $newarray = $this->input->post('newarray');
            $mappedarray = $this->input->post('mappedarray');
            $the_full_file_path = $this->input->post('fullfilepath');
            $errorinupload = "";
            $tokenlistarray = file($the_full_file_path);
            $this->load->model('participants_model');
            $this->load->model('participant_attribute_model');
            $recordcount = 0; $mandatory=0; $mincriteria=0; $imported = 0; $dupcount=0;
            $duplicatelist = array();$invalidemaillist=array(); $invalidformatlist=array();
            $invalidattribute=array(); $invalidparticipantid=array();
            // This allows to read file with MAC line endings too
            @ini_set('auto_detect_line_endings', true);
            // open it and trim the ednings
            $separator = $this->input->post('seperatorused');
            $uploadcharset=$this->input->post('characterset');
            if(!empty($newarray))
            {
                foreach($newarray as $key=>$value)
                {
                    $aData = array( 'attribute_type'=> 'TB','attribute_name'=> $value, 'visible' => 'FALSE');
                    $insertid=$this->participant_attribute_model->storeAttributeCSV($aData);
                    $mappedarray[$insertid] = $value;
                }
            }
            if(!isset($uploadcharset))
            {
                $uploadcharset='auto';
            }
            foreach ($tokenlistarray as $buffer)
            {
                $buffer=@mb_convert_encoding($buffer,"UTF-8",$uploadcharset);
                $firstname = ""; $lastname = ""; $email = ""; $language="";
                if ($recordcount==0)
                {
                // Pick apart the first line
                $buffer=removeBOM($buffer);
                $attrid = $this->participant_attribute_model->getAttributeID();
                $allowedfieldnames=array('participant_id','firstname','lastname','email','language','blacklisted');
                if(!empty($mappedarray))
                {
                    foreach($mappedarray as $key=>$value)
                    {
                        array_push($allowedfieldnames,$value);
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
                        $comma = substr_count($buffer,',');
                        $semicolon = substr_count($buffer,';');
                    if ($semicolon>$comma) $separator = ';'; else $separator = ',';
                }
                $firstline = convertCSVRowToArray($buffer,$separator,'"');
                $firstline=array_map('trim',$firstline);
                $ignoredcolumns=array();
                //now check the first line for invalid fields
                foreach ($firstline as $index=>$fieldname)
                {
                    $firstline[$index] = preg_replace("/(.*) <[^,]*>$/","$1",$fieldname);
                    $fieldname = $firstline[$index];
                    if (!in_array($fieldname,$allowedfieldnames))
                    {
                        $ignoredcolumns[]=$fieldname;
                    }
                }
                if (!in_array('firstname',$firstline) || !in_array('lastname',$firstline) || !in_array('email',$firstline))
                {
                    $recordcount=count($tokenlistarray);
                    break;
                }
            }
            else
            {
                $line = convertCSVRowToArray($buffer,$separator,'"');
                if (count($firstline)!=count($line))
                {
                    $invalidformatlist[]=$recordcount;
                    continue;
                }
                $writearray=array_combine($firstline,$line);
                //kick out ignored columns
                foreach ($ignoredcolumns  as $column)
                {
                    unset($writearray[$column]);
                }
                $invalidemail=false;
                $dupfound = false;
                $thisduplicate = 0;
                $filterduplicatefields=array('firstname','lastname','email');
                foreach($writearray as $value)
                {
                 //For duplicate  values
                   $aData = array(
                    'firstname' => $writearray['firstname'],
                    'lastname' => $writearray['lastname'],
                    'email' => $writearray['email'],
                    'owner_uid' => $this->session->userdata('loginID')
                    );
                    $aData=$this->participants_model->checkforDuplicate($aData);
                    if($aData == true )
                    {
                         $thisduplicate = 1;
                         $dupcount++;
                    }

                }

                if($thisduplicate == 1)
                {
                    $dupfound = true;
                    $duplicatelist[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
                }
                $invalidemail=false;
                $writearray['email'] = trim($writearray['email']);
                if($writearray['email']!='')
                {
                    $aEmailAddresses=explode(';',$writearray['email']);
                    foreach ($aEmailAddresses as $sEmailaddress)
                    {
                        if (!validate_email($sEmailaddress))
                        {
                            $invalidemail=true;
                            $invalidemaillist[]=$line[0]." ".$line[1]." (".$line[2].")";
                        }
                    }
                }

                if (!$dupfound && !$invalidemail)
                {
                    $uuid = $this->gen_uuid();
                    if (!isset($writearray['participant_id'])|| $writearray['participant_id'] == ""){$writearray['participant_id'] = $uuid;}
                    if (isset($writearray['emailstatus']) && trim($writearray['emailstatus']=='')){ unset($writearray['emailstatus']);}
                    if (!isset($writearray['language']) || $writearray['language'] == "") $writearray['language'] = "en";
                    if (!isset($writearray['blacklisted']) || $writearray['blacklisted'] == "") $writearray['blacklisted'] = "N";
                    $writearray['owner_uid'] = $this->session->userdata('loginID');
                    if (isset($writearray['validfrom']) && trim($writearray['validfrom']=='')){ unset($writearray['validfrom']);}
                    if (isset($writearray['validuntil']) && trim($writearray['validuntil']=='')){ unset($writearray['validuntil']);}

                    if($writearray['email'] == "" ||$writearray['firstname'] == ""||$writearray['lastname'] == "")
                    {
                        $mandatory++;
                    }
                    else
                    {
                      foreach($writearray as $key=> $value)
                       {
                          if(!empty($mappedarray))
                          {
                          if(in_array($key, $allowedfieldnames))
                            {
                              foreach($mappedarray as $attid=>$attname)
                              {
                                  if($attname == $key)
                                  {
                                      if(!empty($value))
                                      {
                                          $aData = array( 'participant_id'=>$writearray['participant_id'],
                                              'attribute_id'=>$attid,
                                              'value' => $value
                                              );
                                          $this->participant_attribute_model->saveParticipantAttributeValue($aData);
                                      }
                                      else
                                      {

                                      }
                                  }
                              }

                            }
                          }

                         }
                       }
                     $this->participants_model->insertParticipantCSV($writearray);
                   $imported++;
                }
                $mincriteria++;
             }
           $recordcount++;
        }
       unlink('tmp/uploads/'.basename($the_full_file_path));
       /*       $this->session->set_userdata('recordcount',$recordcount-1);
       $this->session->set_userdata('duplicatelist',$duplicatelist);
       $this->session->set_userdata('mincriteria',$mincriteria);
       $this->session->set_userdata('imported',$imported);
       $this->session->set_userdata('errorinupload',$errorinupload);
       $this->session->set_userdata('invalidemaillist',$invalidemaillist);
       $this->session->set_userdata('mandatory',$mandatory);
       $this->session->set_userdata('invalidattribute',$invalidattribute);
       $this->session->set_userdata('mandatory',$mandatory);
       $this->session->set_userdata('invalidparticipantid',$invalidparticipantid);*/
       $clang = $this->limesurvey_lang;
       $aData['clang']=$clang;
       $aData['recordcount'] = $recordcount-1;
       $aData['duplicatelist'] = $duplicatelist;
       $aData['mincriteria'] = $mincriteria;
       $aData['imported']= $imported;
       $aData['errorinupload'] = $errorinupload;
       $aData['invalidemaillist'] = $invalidemaillist;
       $aData['mandatory'] = $mandatory;
       $aData['invalidattribute']=$invalidattribute;
       $aData['mandatory']= $mandatory;
       $aData['invalidparticipantid'] = $invalidparticipantid;
       $this->load->view('admin/participants/uploadSummary_view',$aData);
}
function summaryview()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $aData = array('clang' => $clang);
    $this->load->view('admin/participants/participantsPanel_view',$aData);
    $this->load->view('admin/participants/uploadSummary_view',$aData);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for setting the session variables for attribute map page redirect
 */
function setSession()
{
    $this->session->unset_userdata('participantid');
    $this->session->set_userdata('participantid', $this->input->post('participantid'));
}
/*
 * funcion for generation of unique id
 */
function gen_uuid()
{
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    mt_rand( 0, 0xffff ),
    mt_rand( 0, 0x0fff ) | 0x4000,
    mt_rand( 0, 0x3fff ) | 0x8000,
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
/*
 * Stores the shared participant information in participant_shares
 */
function shareParticipants()
{
    //var_dump($_REQUEST);die();
    $clang = $this->getController()->lang;
    @$participant_id = $_REQUEST['participantid'];
    @$shareuserid = $_REQUEST['shareuser'];
    @$can_edit = $_REQUEST['can_edit'];
    @$ownerid = $_REQUEST['owner_uid'];
    Yii::app()->loadHelper('date');
    $format = 'DATE_W3C';
    $i=0;
    foreach($participant_id as $id )
    {
        $time = time();
        $aData = array('participant_id' =>$id,
                      'share_uid' => $shareuserid,
                      'date_added' => standard_date($format, $time),
                      'can_edit' => $can_edit);
        Participant_shares::model()->storeParticipantShare($aData);
        $i++;
    }
    echo sprintf($clang->gT("%s participants have been shared "),$i);
}
/*
 * This function is responsible for copying the participant from tokens to the central Database
 */
function addToCentral()
{
    $this->load->model('participants_model');
    $response=$this->participants_model->copyToCentral($this->input->post('surveyid'),$this->input->post('newarr'),$this->input->post('mapped'));
    $clang = $this->limesurvey_lang;
    echo sprintf($clang->gT("%s participants have been copied,%s participants have not been copied because they already exisit "),$response['success'],$response['duplicate']);
}
/*
 * This function is responsible for adding the participant to the specified survey
 */
function addToToken()
{

    $this->load->model('participants_model');
    $response = $this->participants_model->copytoSurvey($this->input->post('participantid'),$this->input->post('surveyid'),$this->input->post('attributeid'));
    $clang = $this->limesurvey_lang;
    echo sprintf($clang->gT("%s participants have been copied,%s participants have not been copied because they already exisit "),$response['success'],$response['duplicate']);
}
/*
 * This function is responsible for adding the participant to the specified survey with attribute mapping
 */
function addToTokenattmap()
{
    $participant_id= $this->input->post('participant_id');
    $surveyid = $this->input->post('surveyid');
    $mapped = $this->input->post('mapped');
    $newcreate = $this->input->post('newarr');
    $this->load->model('participants_model');
    $clang = $this->limesurvey_lang;
    $response=$this->participants_model->copytosurveyatt($surveyid,$mapped,$newcreate,$participant_id);
    echo sprintf($clang->gT("%s participants have been copied,%s participants have not been copied because they already exisit "),$response['success'],$response['duplicate']);

}
/*
 * This function is responsible for attribute mapping while copying participants from cpdb to token's table
 */
function attributeMap()
{
    self::_js_admin_includes($this->config->item('adminscripts')."attributeMap.js");
    $css_admin_includes[] = $this->config->item('styleurl')."admin/default/attributeMap.css";
    $this->config->set_item("css_admin_includes", $css_admin_includes);
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $surveyid = $this->input->post('survey_id');
    $redirect = $this->input->post('redirect');
    $count = $this->input->post('count');
    $participant_id = $this->input->post('participant_id');
    $this->load->model('participant_attribute_model');
    $attributes = $this->participant_attribute_model->getAttributes();
    $tokenfieldnames = array_values($this->db->list_fields("tokens_$surveyid"));
    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
    $selectedattribute = array();
    $selectedcentralattribute = array();
    $alreadymappedattid = array();
    $alreadymappedattname = array();
    $i=0;
    $j=0;
    foreach($tokenattributefieldnames as $key=>$value)
    {
        if(is_numeric($value[10]))
        {
            $selectedattribute[$i] = $value;
            $i++;
        }
        else
        {
            array_push($alreadymappedattid,substr($value,15));
        }
    }
    foreach($attributes as $row)
    {
        if(!in_array($row['attribute_id'],$alreadymappedattid))
        {
            $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
        }
        else
        {
            array_push($alreadymappedattname,$row['attribute_name']);
        }
    }
    $aData = array('clang'=> $clang,
                  'selectedcentralattribute' => $selectedcentralattribute,
                  'selectedtokenattribute' => $selectedattribute,
                  'alreadymappedattributename' => $alreadymappedattname,
                  'survey_id' => $surveyid,
                  'redirect' => $redirect,
                  'participant_id'=>$participant_id,
                  'count' => $count);
    $this->load->view('admin/participants/attributeMap_view',$aData);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for attribute mapping while copying participants from cpdb to token's table
 */
function attributeMapToken()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $surveyid = $this->uri->segment(4);
    $this->load->model('participant_attribute_model');
    $attributes = $this->participant_attribute_model->getAttributes();
    $tokenattributefieldnames=GetTokenFieldsAndNames($surveyid,TRUE);
    $selectedattribute = array();
    $selectedcentralattribute = array();
    $alreadymappedattid = array();
    $alreadymappedattdisplay = array();
    $i=0;
    $j=0;
    foreach($tokenattributefieldnames as $key=>$value)
    {
        if(is_numeric($key[10]))
        {
            $selectedattribute[$value] = $key;
        }
        else
        {
            array_push($alreadymappedattid,substr($key,15));
            array_push($alreadymappedattdisplay,$key);
        }
    }
    foreach($attributes as $row)
    {
        if(!in_array($row['attribute_id'],$alreadymappedattid))
        {
            $selectedcentralattribute[$row['attribute_id']] = $row['attribute_name'];
        }
    }
    $aData = array('clang'=> $clang,
                  'attribute' => $selectedcentralattribute,
                  'tokenattribute'=>$selectedattribute,
                  'alreadymappedattributename' => $alreadymappedattdisplay );
    $this->load->view('admin/participants/attributeMapToken_view',$aData);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
function mapCSVcancelled()
{
    unlink('tmp/uploads/'.basename($_POST['fullfilepath']));
}
function blacklistParticipant()
{
    $this->load->model('participants_model');
    $participant_id = $this->uri->segment(4);
    $survey_id = $this->uri->segment(5);
    $clang = $this->limesurvey_lang;
    if(!is_numeric($survey_id))
    {
        $blacklist = $this->uri->segment(5);
        if($blacklist=='Y' || $blacklist =='N')
        {
            $aData = array('blacklisted' => $blacklist,'participant_id' => $participant_id );
            $aData = $this->participants_model->blacklistparticipantglobal($aData);
            $aData['global'] = 1;
            $aData['clang'] = $clang;
            $aData['blacklist'] = $blacklist;
            $this->load->view('admin/participants/blacklist_view',$aData);
        }
        else
        {
            $aData['is_participant']=0;
            $aData['is_updated']=0;
            $aData['clang'] = $clang;
            $this->load->view('admin/participants/blacklist_view',$aData);
        }
    }
    else
    {
        $blacklist = $this->uri->segment(6);
        if( $blacklist=='Y' || $blacklist =='N')
        {
            $aData = array('blacklisted' => $blacklist);
            $aData = $this->participants_model->blacklistparticipantlocal($aData,$survey_id,$participant_id);$aData['global'] = 1;
            $aData['clang'] = $clang;
            $aData['local'] = 1;
            $aData['blacklist'] = $blacklist;
            $this->load->view('admin/participants/blacklist_view',$aData);
        }
        else
        {
            $aData['is_participant']=0;
            $aData['is_updated']=0;
            $aData['clang'] = $clang;
            $this->load->view('admin/participants/blacklist_view',$aData);
        }
    }
}
function saveVisible()
{
    ParticipantAttributeNames::saveAttributeVisible(CHttpRequest::getPost('attid'),CHttpRequest::getPost('visiblevalue'));
}
}
?>
