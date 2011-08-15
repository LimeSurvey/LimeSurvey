<?php
/*
 * This is the main controller for Participants Panel
 */
class participants extends AdminController
{
/*
 * This function is responsible for loading the view 'participantsPanel'
*/
function index()
{
    $this->load->model('participants_model');
    $this->load->model('participant_attribute_model');
    $userid = $this->session->userdata('loginID');
    self::_getAdminHeader();
    if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
    {
        $totalrecords = $this->participants_model->getParticipantCount();
    }
    else
    {
       $totalrecords=$this->participants_model->getParticipantsOwnerCount($userid);
    }
    $shared = $this->participants_model->getParticipantsSharedCount($userid);
    $owned = $this->participants_model->getParticipantOwnedCount($userid);
    $blacklisted = $this->participants_model->getBlacklistedCount($userid);
    $attributecount = $this->participant_attribute_model->getAttributeCount();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang,
                  'totalrecords' => $totalrecords,
                  'owned' => $owned,
                  'shared' => $shared,
                  'attributecount' => $attributecount,
                  'blacklisted' => $blacklisted
                  );    
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/summary_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'importCSV'
*/
function importCSV()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang);
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/importCSV_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'displayParticipants'
*/
function displayParticipants()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $this->load->model('users_model');
    $this->load->model('participant_attribute_model');
    $this->load->model('tobemergedlater_model');
    $this->load->model('participants_model');
    $getNames=$this->users_model->getSomeRecords(array('uid','full_name'));
    $attributes = $this->participant_attribute_model->getVisibleAttributes();
    $allattributes = $this->participant_attribute_model->getAllAttributes();
    $attributeValues =$this->participant_attribute_model->getAllAttributesValues();
    $surveynames = $this->tobemergedlater_model->getSurveyNames();
    $data = array('names'=> $getNames,
                  'attributes' => $attributes,
                  'allattributes' => $allattributes,
                  'attributeValues' => $attributeValues,
                  'surveynames' =>$surveynames,
                  'clang'=> $clang );
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/displayParticipants_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'blacklistControl'
*/
function blacklistControl()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang);
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'attributeControl'
*/
function attributeControl()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $this->load->model('participant_attribute_model');
    $data = array('clang'=> $clang,'result'=>$this->participant_attribute_model->getAttributes());
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/attributeControl_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'userControl'
*/
function userControl()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang,
                  'userideditable'=>$this->config->item("userideditable"));
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/userControl_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'userControl'
*/
function signupControl()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang);
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for loading the view 'sharePanel'
*/
function sharePanel()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang'=> $clang);
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/sharePanel_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function sends the shared participant info to the share panel using JSON encoding
 */
function getShareInfo_json()
{
    if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
    { 
        $this->load->model('participants_model');
        $this->load->model('users_model');
        $this->load->model('tobemergedlater_model');
        $records = $this->participants_model->getParticipantSharedAll();
        $data->page = 1;						
        $data->records =count($this->participants_model->getParticipantSharedAll());
        $data->total =ceil($data->records /10 );
        $i=0;
        foreach($records->result() as $row) 
        {
            $shared = $this->users_model->getName($row->shared_uid); //for conversion of uid to human readable names
            $owner = $this->users_model->getName($row->owner_uid);
            $data->rows[$i]['id']=$row->participant_id; 
            $data->rows[$i]['cell']=array($row->firstname,$row->lastname,$row->email,$shared->full_name,$row->shared_uid,$owner->full_name,$row->date_added,$row->can_edit);
            $i++;
        }
        echo json_encode($data); 
    }
    else
    {
        $this->load->model('participants_model');
        $this->load->model('users_model');
        $this->load->model('tobemergedlater_model');
        $records = $this->participants_model->getParticipantShared($this->session->userdata('loginID'));
        $data->page = 1;
        $data->records =count($this->participants_model->getParticipantShared($this->session->userdata('loginID')));
        $data->total =ceil($data->records /10 );
        $i=0;
        foreach($records->result() as $row)
        {
                $sharename = $this->users_model->getName($row->shared_uid); //for conversion of uid to human readable names
                $data->rows[$i]['id']=$row->participant_id;
                $data->rows[$i]['cell']=array($row->firstname,$row->lastname,$row->email,$sharename->full_name,$row->shared_uid,$row->date_added,$row->can_edit);
                $i++;
        }
        echo json_encode($data);
    }
}
/*
 * This function is to recieve ajax call from the share Participant's panel
 */
function editShareInfo()
{
    $operation = $_POST['oper'];
    $this->load->model('participant_shares_model');
    $this->load->model('users_model');
    if($operation == 'del')
        {
            $this->participant_shares_model->deleteRow($_POST);
        }
    $data = array( 'participant_id' => $this->input->post('participant_id'),
                   'can_edit' => $this->input->post('can_edit'),
                   'shared_uid' => $this->input->post('shared_uid'));
    $this->participant_shares_model->updateShare($data);
}
function delParticipant()
{
    
    $this->load->model('participants_model');
    $selectoption = $this->input->post('selectedoption');
    $participant_id = $this->input->post('participant_id');
    if($selectoption=="po")
    {
      $this->participants_model->deleteParticipant($participant_id);
    }
    elseif($selectoption=="ptt")
    {
       $this->participants_model->deleteParticipantToken($participant_id);
    }
    else
    {
       $this->participants_model->deleteParticipantTokenAnswer($participant_id);
    }
    
}
/*
 * This function is resposible for editing data on the jqGrid
 */
function editParticipant()
{
    $operation = $_POST['oper'];
    $this->load->model('participants_model');
    $this->load->model('users_model');
    //In case the uid is not editable, the current user is added in the uid
    if($this->input->post('owner_uid')=='')
    {
        $oid=$this->session->userdata('loginID');
    }
    else
    {
        $oid = $_POST['owner_uid'];
    }
    if($this->input->post('language')=='')
    {
        $lang=$this->session->userdata('adminlang');
    }
    else
    {
        $lang = $_POST['language'];
    }
    if($operation == 'edit')
    {
        $data = array(
        'participant_id' => $_POST['id'],
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'email' => $_POST['email'],
        'language' => $_POST['language'],
        'blacklisted' => $_POST['blacklisted'],
        'owner_uid' => $oid);
        $this->participants_model->updateRow($data);
    }
    elseif($operation == 'add')
    {
        $uuid = $this->gen_uuid();
        $data = array('participant_id' => $uuid,
                      'firstname' => $_POST['firstname'],
                      'lastname' => $_POST['lastname'],
                      'email' => $_POST['email'],
                      'language' => $_POST['language'],
                      'blacklisted' => $_POST['blacklisted'],
                      'owner_uid' => $oid);
        $this->participants_model->insertParticipant($data);
    }
}
/*
 * This function is responsible for storeing values in the user control to the database
 */        
function storeUserControlValues()
{
    $this->load->model('users_model');
	$this->load->model('settings_global_model');
	$this->settings_global_model->updateSetting('userideditable',$_POST['userideditable']);
    redirect('admin/participants/userControl');
}
function getSurveyInfo_json()
{
        $this->load->model('survey_links_model');
        $this->load->model('surveys_languagesettings_model');
        $participantid = $this->uri->segment(4);
        $records = $this->survey_links_model->getLinkInfo($participantid);
        $data->page = 1;
        $data->records = count ($this->survey_links_model->getLinkInfo($participantid)->result_array());
        $data->total = ceil ($data->records /10 );
        $i=0;
        foreach($records->result() as $row)
        {
            $surveyname = $this->surveys_languagesettings_model->getSurveyNames($row->survey_id);
            $data->rows[$i]['cell']=array($surveyname->row()->surveyls_title,"<a href=".site_url("admin/tokens/browse")."/".$row->survey_id.">".$row->survey_id,$row->token_id,$row->date_created);
            $i++;
        }
        echo json_encode($data);
}
function exporttocsvcount()
{
        $this->load->model('participants_model');
        $searchconditionurl = $_POST['searchcondition'];
        //$searchcondition = explode("/",$searchconditionurl);
        $searchcondition = basename($searchconditionurl);
        if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
            if($searchcondition != 'getParticipants_json')
            {
              $condition = explode("||",$searchcondition);  
               if(count($condition)==3)
               {
                    $query = $this-> participants_model->getParticipantsSearch($condition);
               }
               else
               {
                    $query = $this-> participants_model->getParticipantsSearchMultiple($condition);
               }
            }
            else
            {
                $table_name = 'participants';
                $getquery = $this->db->get($table_name);
                $query = $getquery->result_array();
            }
            
        }
        else
        {
            $userid = $this->session->userdata('loginID');
            $query = $this->participants_model->getParticipantsOwner($userid);
        }
        $clang = $this->limesurvey_lang;        
        echo sprintf($clang->gT("Export %s participant(s) to CSV  "),count($query));
}
function exporttocsvcountAll()
{
        $this->load->model('participants_model');
       if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
                $table_name = 'participants';
                $getquery = $this->db->get($table_name);
                $query = $getquery->result_array();
        }
        else
        {
            $userid = $this->session->userdata('loginID');
            $query = $this->participants_model->getParticipantsOwner($userid);
        }
        $clang = $this->limesurvey_lang;        
        if(count($query) > 0 )
        {
            echo sprintf($clang->gT("Export %s participant(s) to CSV  "),count($query));
        }
        else
        {
            echo count($query);
        }
}
function exporttocsvAll()
{
        $this->load->model('participant_attribute_model');
        $this->load->model('participants_model');
        if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
                $table_name = 'participants';
                $getquery = $this->db->get($table_name);
                $query = $getquery->result_array();
        }
        else
        {
            $userid = $this->session->userdata('loginID');
            $query = $this->participants_model->getParticipantsOwner($userid);
        }
        
        if(!$query)
            return false;
        // Starting the PHPExcel library
        $this->load->library('admin/phpexcel/PHPExcel');
        $this->load->library('admin/phpexcel/PHPExcel/IOFactory');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
        $objPHPExcel->setActiveSheetIndex(0);
        // Field names in the first row
        $fields = array ('participant_id','firstname','lastname' ,'email' ,'language' ,'blacklisted','owner_uid' );
        $col = 0;
        foreach ($fields as $field)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
            $col++;
        }
             // Fetching the table data
            $row = 2;
            foreach($query as $field => $data)
            {
                $col = 0;
                foreach ($fields as $field)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$field]);
                    $col++;
                }
                $row++;
            }
                   
            $attributenames = $this->participant_attribute_model->getAttributes();
            foreach($attributenames as $key=>$value)
            {
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1,$value['attribute_name']);
                $col++;
            }
            // Fetching the table data
            $row = 2;
            foreach($query as $field => $data)
            {
                $col = 0;
                foreach ($fields as $field)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$field]);
                    $col++;
                }
                foreach($attributenames as $key=>$value)
                {
                    $answer=$this->participant_attribute_model->getAttributeValue($data['participant_id'],$value['attribute_id']);
                    if(isset($answer->value))
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,$answer->value);
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,"");
                    }
                    $col++;
                }
            $row++;
            }
        
$objPHPExcel->setActiveSheetIndex(0);
$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="central_'.$this->session->userdata('full_name').'.csv"');
header('Cache-Control: max-age=0');
$objWriter->save('php://output');

}

function exporttocsv()
{
        $this->load->model('participant_attribute_model');
        $this->load->model('participants_model');
        $searchconditionurl = $_POST['searchcondition'];
        //$searchcondition = explode("/",$searchconditionurl);
        $searchcondition = basename($searchconditionurl);
        if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
        {
            if($searchcondition != 'getParticipants_json')
            {
              $condition = explode("||",$searchcondition);  
               if(count($condition)==3)
               {
                    $query = $this-> participants_model->getParticipantsSearch($condition);
               }
               else
               {
                    $query = $this-> participants_model->getParticipantsSearchMultiple($condition);
               }
            }
            else
            {
                $table_name = 'participants';
                $getquery = $this->db->get($table_name);
                $query = $getquery->result_array();
            }
            
        }
        else
        {
            $userid = $this->session->userdata('loginID');
            $query = $this->participants_model->getParticipantsOwner($userid);
        }
        
        if(!$query)
            return false;
        // Starting the PHPExcel library
        $this->load->library('admin/phpexcel/PHPExcel');
        $this->load->library('admin/phpexcel/PHPExcel/IOFactory');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
        $objPHPExcel->setActiveSheetIndex(0);
        // Field names in the first row
        $fields = array ('participant_id','firstname','lastname' ,'email' ,'language' ,'blacklisted','owner_uid' );
        $col = 0;
        foreach ($fields as $field)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
            $col++;
        }
        //echo $attribute_id;
        if($this->uri->segment(4) == "null")
        {
            // Fetching the table data
            $row = 2;
            foreach($query as $field => $data)
            {
                $col = 0;
                foreach ($fields as $field)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$field]);
                    $col++;
                }
                $row++;
            }
                   
        }
        else
        {
            $attribute_id=explode(",",$this->uri->segment(4));
            foreach($attribute_id as $key=>$value)
            {
                $attributename = $this->participant_attribute_model->getAttributeName($value);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1,$attributename->attribute_name);
                $col++;
            }
            // Fetching the table data
            $row = 2;
            foreach($query as $field => $data)
            {
                $col = 0;
                foreach ($fields as $field)
                {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data[$field]);
                    $col++;
                }
                foreach($attribute_id as $key=>$value)
                {
                    $answer=$this->participant_attribute_model->getAttributeValue($data['participant_id'],$value);
                    if(isset($answer->value))
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,$answer->value);
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,"");
                    }
                    $col++;
                }
            $row++;
            }
        }
$objPHPExcel->setActiveSheetIndex(0);
$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="central_'.$this->session->userdata('full_name').'.csv"');
header('Cache-Control: max-age=0');
$objWriter->save('php://output');
        
}

function getParticipantsResults_json()
{
    $this->load->model('participants_model');
    $this->load->model('participant_attribute_model');
    $this->load->model('users_model');
    $attid = $this->participant_attribute_model->getAttributeVisibleID();
    if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
    {
        $searchcondition = $this->uri->segment(4);
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||",$searchcondition);        
        if(count($condition)==3)
        {
            
            $records = $this-> participants_model->getParticipantsSearch($condition);
            $data->page = 1;
            $data->records = count ($this->participants_model->getParticipantsSearch($condition));
            $data->total = ceil ($data->records /10 );
        
        }
        else
        {
            $records = $this-> participants_model->getParticipantsSearchMultiple($condition);
            $data->page = 1;
            $data->records = count ($this->participants_model->getParticipantsSearch($condition));
            $data->total = ceil ($data->records /10 );
        }
        
     }
     
    $i=0;
foreach($records as $row=>$value)
        {   
            $data->rows[$i]['id']=$value['participant_id'];
            $username = $this->users_model->getName($value['owner_uid']);//for conversion of uid to human readable names
            $surveycount = $this->participants_model->getSurveyCount($value['participant_id']);
            $data->rows[$i]['cell']=array($value['participant_id'],"true",$value['firstname'],$value['lastname'],$value['email'],$value['blacklisted'],$surveycount,$value['language'],$username->full_name);// since it's the admin he has access to all editing on the participants inspite of what can_edit option is 
            $attributes =  $this->participant_attribute_model->getParticipantVisibleAttribute($value['participant_id']);
            foreach($attid as $attributeid)
            {
                $answer=$this->participant_attribute_model->getAttributeValue($value['participant_id'],$attributeid['attribute_id']);
                if(isset($answer->value))
                {
                    array_push($data->rows[$i]['cell'],$answer->value);
                }
                else
                {
                    array_push($data->rows[$i]['cell'],"");
                }
            }
            $i++;
        }
        echo json_encode($data);
    
    /*else // Only the owned and shared participants will be visible
    {
        $userid = $this->session->userdata('loginID');
        $records = $this->participants_model->getParticipantsOwner($userid);
        $data->page = 1;
        $data->records = count ($this->participants_model->getParticipantsOwner($userid)->result_array());
        $data->total = ceil ($data->records /10 );
        $attid = $this->participant_attribute_model->getAttributeVisibleID();
        $i=0;
        foreach($records->result() as $row)
        {
            $surveycount = $this->participants_model->getSurveyCount($row->participant_id);
            $ownername = $this->users_model->getName($row->owner_uid); //for conversion of uid to human readable names
            $data->rows[$i]['id']=$row->participant_id;
            $data->rows[$i]['cell']=array($row->participant_id,$row->can_edit,$row->firstname,$row->lastname,$row->email,$row->blacklisted,$surveycount,$row->language,$ownername->full_name);
            $attributes =  $this->participant_attribute_model->getParticipantVisibleAttribute($row->participant_id);
            foreach($attid as $attributeid)
                {
                    $answer=$this->participant_attribute_model->getAttributeValue($row->participant_id,$attributeid['attribute_id']);
                    if(isset($answer->value))
                    {
                        array_push($data->rows[$i]['cell'],$answer->value);
                    }
                    else
                    {
                        array_push($data->rows[$i]['cell'],"");
                    }                    
                }
            $i++;
        }
        echo json_encode($data);
    }
    */
}
/*
 * This function sends the data in JSON format extracted from the database to be displayed using the jqGrid
 * Parameters : None
 * Return Data : echo the JSON encoded participants data
 */
function getParticipants_json()
{
    $this->load->model('participants_model');
    $this->load->model('participant_attribute_model');
    $this->load->model('users_model');
    $attid = $this->participant_attribute_model->getAttributeVisibleID();
    if($this->session->userdata('USER_RIGHT_SUPERADMIN')) //If super admin all the participants will be visible
    {
        $records = $this-> participants_model->getParticipants();
        $data->page = 1;
        $data->records = count ($this->participants_model->getParticipants()->result_array());
        $data->total = ceil ($data->records /10 );
        $i=0;
        foreach($records->result() as $row)
        {   
            $data->rows[$i]['id']=$row->participant_id;
            $username = $this->users_model->getName($row->owner_uid);//for conversion of uid to human readable names
            $surveycount = $this->participants_model->getSurveyCount($row->participant_id);
            $data->rows[$i]['cell']=array($row->participant_id,"true",$row->firstname,$row->lastname,$row->email,$row->blacklisted,$surveycount,$row->language ,$username->full_name);// since it's the admin he has access to all editing on the participants inspite of what can_edit option is 
            $attributes =  $this->participant_attribute_model->getParticipantVisibleAttribute($row->participant_id);
            foreach($attid as $attributeid)
            {
                $answer=$this->participant_attribute_model->getAttributeValue($row->participant_id,$attributeid['attribute_id']);
                if(isset($answer->value))
                {
                    array_push($data->rows[$i]['cell'],$answer->value);
                }
                else
                {
                    array_push($data->rows[$i]['cell'],"");
                }
            }
            $i++;
        }
        echo json_encode($data);
    }
    else // Only the owned and shared participants will be visible
    {
        $userid = $this->session->userdata('loginID');
        $records = $this->participants_model->getParticipantsOwner($userid);
        $data->page = 1;
        $data->records = count ($this->participants_model->getParticipantsOwner($userid)->result_array());
        $data->total = ceil ($data->records /10 );
        $attid = $this->participant_attribute_model->getAttributeVisibleID();
        $i=0;
        foreach($records->result() as $row)
        {
            $surveycount = $this->participants_model->getSurveyCount($row->participant_id);
            $ownername = $this->users_model->getName($row->owner_uid); //for conversion of uid to human readable names
            $data->rows[$i]['id']=$row->participant_id;
            $data->rows[$i]['cell']=array($row->participant_id,$row->can_edit,$row->firstname,$row->lastname,$row->email,$row->blacklisted,$surveycount,$row->language,$ownername->full_name);
            $attributes =  $this->participant_attribute_model->getParticipantVisibleAttribute($row->participant_id);
            foreach($attid as $attributeid)
                {
                    $answer=$this->participant_attribute_model->getAttributeValue($row->participant_id,$attributeid['attribute_id']);
                    if(isset($answer->value))
                    {
                        array_push($data->rows[$i]['cell'],$answer->value);
                    }
                    else
                    {
                        array_push($data->rows[$i]['cell'],"");
                    }                    
                }
            $i++;
        }
        echo json_encode($data);
    }
}
/*
 * This function fetches the attributes of a participant to be displayed in the attribute subgrid
 */        
function getAttribute_json()
{
    $this->load->model('participant_attribute_model');
    $participant_id=$this->uri->segment(4);
    $records = $this->participant_attribute_model->getParticipantVisibleAttribute($participant_id);
    $getallattributes = $this->participant_attribute_model->getAttributes();
    $data->page = 1;
    $data->records = count ($records);
    $data->total = ceil ($data->records /10 );
    $data->rows[0]['id']=$participant_id;
    $data->rows[0]['cell'] = array();
    $i=0;
    $doneattributes = array();
    foreach($records as $row)
    {
        $data->rows[$i]['id']=$row['participant_id']."_".$row['attribute_id']; 
        $data->rows[$i]['cell']=array("",$row['participant_id'],$row['attribute_type'],$row['attribute_name'],$row['value'],"Y");
        if($row['attribute_type']=="DD")
        {
            $attvalues = $this->participant_attribute_model->getAttributesValues($row['attribute_id']);
            if(!empty($attvalues))
            {
                $attval="";
                foreach($attvalues as $val)
                {   
                    $attval .= $val['value'].":".$val['value'];
                    $attval .= ";";
                }
                $attval = substr($attval,0,-1);
                array_push($data->rows[$i]['cell'],$attval);
            }
            else
            {
                array_push($data->rows[$i]['cell'],"");   
            }
        }
        else
        {
            array_push($data->rows[$i]['cell'],"");   
        }
        array_push($doneattributes,$row['attribute_id']);  
        $i++;
    }
    if(count($doneattributes)==0)
    {
        $attributenotdone = $this->participant_attribute_model->getAttributes();
    }
    else
    {
        $attributenotdone = $this->participant_attribute_model->getnotaddedAttributes($doneattributes);
    }
    if($attributenotdone>0)
    {
        foreach($attributenotdone as $row)
        {
            
            $data->rows[$i]['id']=$this->uri->segment(4)."_".$row['attribute_id']; 
            $data->rows[$i]['cell']=array("",$this->uri->segment(4),$row['attribute_type'],$row['attribute_name'],"","N");
            if($row['attribute_type']=="DD")
            {
                $attvalues = $this->participant_attribute_model->getAttributesValues($row['attribute_id']);
                if(!empty($attvalues))
                {   
                    $attval="";
                    foreach($attvalues as $val)
                    {   
                        $attval .= $val['value'].":".$val['value'];
                        $attval .= ";";
                    }   
                    $attval = substr($attval,0,-1);
                    array_push($data->rows[$i]['cell'],$attval);
                }
                else
                {
                    array_push($data->rows[$i]['cell'],"");   
                }
            }
            else
            {
                array_push($data->rows[$i]['cell'],"");   
            }
            $i++;
        }
    }
    echo json_encode($data);
}
/*
 * This function gets the data from the form for add participants and pass it to the participants model
 * Parameters : everything is extracted from the post data
 * Return Data : None
 */
function storeParticipants()
{
    $data = array('participant_id' => uniqid(),
    'firstname' => $this->input->post('firstname'),
    'lastname' => $this->input->post('lastname'),
    'email' => $this->input->post('email'),
    'language' => $this->input->post('language'),
    'blacklisted' => $this->input->post('blacklisted'),
    'owner_uid' => $this->input->post('owner_uid'));
    $this->load->model('participants_model');
    $this->participants_model->insertParticipant($data);
}
/*
 * This function is responsible for showing the additional attribute for central database
 */
function viewAttribute()
{
    $this->load->model('participant_attribute_model');
    $attribute_id = $this->uri->segment(4);
    $attributes=$this->participant_attribute_model->getAttribute($attribute_id);
    $attributenames = $this->participant_attribute_model->getAttributeNames($attribute_id);
    $attributevalues=$this->participant_attribute_model->getAttributesValues($attribute_id);
    $clang = $this->limesurvey_lang;
    $data = array('attributes' => $attributes,
                  'attributevalues' => $attributevalues,
                  'attributenames' => $attributenames,
                  'clang'=> $clang);
    self::_getAdminHeader();
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/viewAttribute_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
/*
 * This function is responsible for saving the additional attribute. It iterates through all the new attributes added dynamically 
 * and iterates through them
 */
function saveAttribute()
    {
        $this->load->model('participant_attribute_model');
        $data = array('attribute_id'    => $this->uri->segment(4),
                      'attribute_type'  => $this->input->post('attribute_type'),
                      'visible'  => $this->input->post('visible'));
        $this->participant_attribute_model->saveAttribute($data);
        foreach($_POST as $key=>$value)
        {
            if(strlen($key) == 2) // check for language code in the post variables this is a hack as the only way to check for language data
            {   
                $langdata = array( 'attribute_id' => $this->uri->segment(4),
                                   'attribute_name' => $value,
                                   'lang' => $key     );
                
                $this->participant_attribute_model->saveAttributeLanguages($langdata);
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
                    $datavalues[$i] = array('attribute_id' => $this->uri->segment(4),
                                            'value' => $this->input->post($attvaluename));
                }
                $i++;
            }while(isset($_POST[$attvaluename]));
            $this->participant_attribute_model->storeAttributeValues($datavalues);
        }
        
        if(isset($_POST['editbox']))
        {
            $editattvalue = array('value_id'=> $_POST['value_id'],
                                  'attribute_id'=> $this->uri->segment(4),
                                  'value' => $_POST['editbox']);
            $this->participant_attribute_model->saveAttributeValue($editattvalue);
        }
        redirect('admin/participants/attributeControl');
}
/*
 * This function is responsible for deleting the additional attribute.
 */
function delAttribute()
{
    $attribute_id = $this->uri->segment(4);
    $this->load->model('participant_attribute_model');
    $this->participant_attribute_model->delAttribute($attribute_id);
    redirect('admin/participants/attributeControl');
}
/*
 * This function is responsible for deleting the additional attribute values in case of drop down.
 */
function delAttributeValues()
{
    $attribute_id = $this->uri->segment(4);
    $value_id = $this->uri->segment(5);
    $this->load->model('participant_attribute_model');
    $this->participant_attribute_model->delAttributeValues($attribute_id,$value_id);
    redirect('admin/participants/viewAttribute/'.$attribute_id);
}
/*
 * This function is responsible for deleting the storing the additional attributes
 */
function storeAttributes()
{
    $this->load->model('participant_attribute_model');
    $i=1;
    do
    {
        $attname = 'attribute_name_'.$i;
        $atttype = 'attribute_type_'.$i;
        $visible = 'visible_'.$i;
        if(!empty($_POST[$attname]))
        {
            $data = array('attribute_name' => $this->input->post($attname),
                          'attribute_type' => $this->input->post($atttype),
                          'visible' => $this->input->post($visible));
            $this->participant_attribute_model->storeAttribute($data);
        }
        $i++;
    }while(isset($_POST[$attname]));
    
    redirect('admin/participants/attributeControl');
}
/*
 * This function is responsible for editing the additional attributes values
 */
function editAttributevalue()
{
    $this->load->model('participant_attribute_model');
    if($this->input->post('oper')=="edit")
    {
        $attributeid = split("_",$this->input->post('id'));
        if($this->input->post('attap')=="Y")
        {
         
            $data = array('participant_id' => $this->input->post('participant_id'),'attribute_id' => $attributeid[1],'value' => $this->input->post('attvalue'));
            $this->participant_attribute_model->editParticipantAttributeValue($data);
        }
        else
        {
            $data = array('participant_id' => $this->input->post('participant_id'),'attribute_id' => $attributeid[1],'value' => $this->input->post('attvalue'));
            $this->participant_attribute_model->saveParticipantAttributeValue($data);
        }
    }
}
function attributeMapCSV()
{   
    $config['upload_path'] = './tmp/uploads';
    $config['allowed_types'] = 'text/x-csv|text/plain|application/octet-stream|csv';
    $config['max_size']	= '100';
    $this->load->library('upload',$config);
    $errorinupload = "";
    if (!$this->upload->do_upload())
    {
        $errorinupload = array('error' => $this->upload->display_errors());
        $this->session->unset_userdata('summary');
        $data = array( 'errorinupload' => $errorinupload);
        $this->session->set_userdata('summary',$data);
        self::_getAdminHeader();
        $clang = $this->limesurvey_lang;
        $data = array('clang'=> $clang);
        $this->load->view('admin/Participants/participantsPanel_view',$data);
        $this->load->view('admin/Participants/uploadSummary_view',$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
    else
    {
        $data = array('upload_data' => $this->upload->data());
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
        $data = array('clang'=> $clang,
                      'attributes'=> $attributes,
                      'firstline'=> $selectedcsvfields,
                      'fullfilepath' => $the_full_file_path,
                      'linecount' => $linecount-1
                );
        $this->load->view('admin/Participants/attributeMapCSV_view',$data);
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
}
/*
 * Uploads the file to the server and process it for valid enteries and import them into database
 */
function uploadCSV()
{
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
                    $data = array( 'attribute_type'=> 'TB','attribute_name'=> $value, 'visible' => 'FALSE');
                    $insertid=$this->participant_attribute_model->storeAttributeCSV($data);
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
                   $data = array(
                    'firstname' => $writearray['firstname'],
                    'lastname' => $writearray['lastname'],
                    'email' => $writearray['email'],
                    'owner_uid' => $this->session->userdata('loginID')
                    );
                    $result=$this->participants_model->checkforDuplicate($data);
                    if($result == true )
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
                if (!$dupfound)
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
                                          $data = array( 'participant_id'=>$writearray['participant_id'],
                                              'attribute_id'=>$attid,
                                              'value' => $value
                                              );
                                          $this->participant_attribute_model->saveParticipantAttributeValue($data);
                                      }
                                      else
                                      {
                                          
                                      }
                                  }
                              }
                              
                            }
                          }
                         /* if(is_numeric($key) && in_array($key, $allowedfieldnames))
                           {
                              $alreadythere = $this->participant_attribute_model->getAttributeValue($writearray['participant_id'],$key);
                              if(!isset($alreadythere->value))
                                {
                                    $getattType = $this->participant_attribute_model->getAttributeType($key);
                                    if($getattType->attribute_type=='TB')
                                    {
                                           $attdata = array('participant_id'=>$writearray['participant_id'],
                                                'attribute_id' => $key,
                                                'value' => $value);
                                    $this->participant_attribute_model->saveParticipantAttributeValue($attdata);
                                   }

                                    else if($getattType->attribute_type=='DP')
                                    {
                                    @$arr=explode(".",$value); // splitting the array
                                    @$dd=intval($arr[0]); // first element of the array is month
                                    @$mm=intval($arr[1]); // second element is date
                                    @$yy=intval($arr[2]); // third element is year
                                    if(checkdate($mm,$dd,$yy)){

                                                $attdata = array('participant_id'=>$writearray['participant_id'],
                                                'attribute_id' => $key,
                                                'value' => $value   );
                                                $this->participant_attribute_model->saveParticipantAttributeValue($attdata);

                                         }
                                    else{
                                        if(!in_array($writearray['participant_id'],$invalidparticipantid))
                                        {

                                        $invalidattribute[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
                                        $invalidparticipantid[] = $writearray['participant_id'];
                                        }
                                    }
                                }
                             else if($getattType->attribute_type=='DD')
                             {
                                 $getattval = $this->participant_attribute_model->getAttributesValues($key);
                                 $values = array();
                                 foreach($getattval as $val)
                                 {
                                     array_push($values, $val['value']);
                                 }

                                    if(in_array($value, $values))
                                    {
                                            $attdata = array('participant_id'=>$writearray['participant_id'],
                                            'attribute_id' => $key,
                                            'value' => $value   );
                                            $this->participant_attribute_model->saveParticipantAttributeValue($attdata);

                                    }
                                    else
                                    {
                                        if(!in_array($writearray['participant_id'],$invalidparticipantid))
                                        {$invalidattribute[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
                                        $invalidparticipantid[] = $writearray['participant_id'];     }
                                    }

                             }
                           }
                          }*/
                       }
                       
                       $this->participants_model->insertParticipantCSV($writearray);
                      $imported++;
                    }
                }
                $mincriteria++;
             }
           $recordcount++;
        }
       
       unlink('tmp/uploads/'.basename($the_full_file_path));

       self::_getAdminHeader();
       $clang = $this->limesurvey_lang;
       
       $data = array(   'duplicatelist' => $duplicatelist,
                        'recordcount' => $recordcount-1,
                        'mincriteria' =>  $mincriteria,
                        'imported' => $imported,
                        'dupcount' => count($duplicatelist),
                        'errorinupload' => $errorinupload,
                        'invalidemaillist'=> $invalidemaillist,
                        'mandatory' => $mandatory,
                        'invalidattribute' => $invalidattribute,
                        'invalidparticipantid' => $invalidparticipantid   );
       $this->session->unset_userdata('summary');
       $this->session->set_userdata('summary',$data);
       //redirect('admin/participants/summaryview');
}
function summaryview()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $data = array('clang' => $clang);
    $this->load->view('admin/Participants/participantsPanel_view',$data);
    $this->load->view('admin/Participants/uploadSummary_view',$data);
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
    $this->load->model('participant_shares_model');
    $this->load->model('users_model');
    $clang = $this->limesurvey_lang;
    $participant_id = $this->input->post('participantid');
    $shareuserid = $this->input->post('shareuser');
    $can_edit = $this->input->post('can_edit');
    $ownerid = $this->input->post('owner_uid');
    $this->load->helper('date');
    $format = 'DATE_W3C';
    $i=0;
    foreach($participant_id as $id )
    {
        $time = time();
        $data = array('participant_id' =>$id,
                      'shared_uid' => $shareuserid,
                      'date_added' => standard_date($format, $time),
                      'can_edit' => $can_edit);
        $this->participant_shares_model->storeParticipantShare($data);
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
    $surveyid = $this->input->post('surveyid');
    $mapped = $this->input->post('mapped');
    $newcreate = $this->input->post('newarr');
    $this->load->model('participants_model');
    $clang = $this->limesurvey_lang;
    $response=$this->participants_model->copytosurveyatt($surveyid,$mapped,$newcreate);
    echo sprintf($clang->gT("%s participants have been copied,%s participants have not been copied because they already exisit "),$response['success'],$response['duplicate']);    
    
}
/*
 * This function is responsible for attribute mapping while copying participants from cpdb to token's table
 */
function attributeMap()
{
    self::_getAdminHeader();
    $clang = $this->limesurvey_lang;
    $surveyid = $this->uri->segment(4);
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
    $data = array('clang'=> $clang,
                  'selectedcentralattribute' => $selectedcentralattribute,
                  'selectedtokenattribute' => $selectedattribute,
                  'alreadymappedattributename' => $alreadymappedattname
                 );
    $this->load->view('admin/Participants/attributeMap_view',$data);
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
    $data = array('clang'=> $clang,
                  'attribute' => $selectedcentralattribute,
                  'tokenattribute'=>$selectedattribute,
                  'alreadymappedattributename' => $alreadymappedattdisplay );
    $this->load->view('admin/Participants/attributeMapToken_view',$data);
    self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
}
function isValidGuid($guid)
{
    return (!empty($guid) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $guid));
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
                $data = array('blacklisted' => $blacklist,'participant_id' => $participant_id );
                $result = $this->participants_model->blacklistparticipantglobal($data);
                $result['global'] = 1;
                $result['clang'] = $clang;
                $result['blacklist'] = $blacklist;
                $this->load->view('admin/Participants/blacklist_view',$result);
            }
            else
            {
                $result['is_participant']=0;
                $result['is_updated']=0;
                $result['clang'] = $clang;
                $this->load->view('admin/Participants/blacklist_view',$result);
            }
        }
        else
        {
            $blacklist = $this->uri->segment(6);
            if( $blacklist=='Y' || $blacklist =='N')
            {
                $data = array('blacklisted' => $blacklist);
                $result = $this->participants_model->blacklistparticipantlocal($data,$survey_id,$participant_id);$result['global'] = 1;
                $result['clang'] = $clang;
                $result['local'] = 1;
                $result['blacklist'] = $blacklist;
                $this->load->view('admin/Participants/blacklist_view',$result);
                
            }
            else
            {
                $result['is_participant']=0;
                $result['is_updated']=0;
                $result['clang'] = $clang;
                $this->load->view('admin/Participants/blacklist_view',$result);
   
            }
            
        }
           
        
    }
}
?>
