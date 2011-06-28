<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Database extends AdminController {


    function __construct()
	{
		parent::__construct();
	}
    
    function index($action)
    {
        
        //global $clang;
        $clang = $this->limesurvey_lang;
        $postsid=returnglobal('sid');
        $postgid=returnglobal('gid');
        $postqid=returnglobal('qid');
        $postqaid=returnglobal('qaid');
        $databaseoutput = '';
        $surveyid = $this->input->post("sid");

        if ($action == "insertsurvey" && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))
        {
            
            
            $this->load->helper("surveytranslator");
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            // $this->input->post['language']
            
            $supportedLanguages = getLanguageData();
            
            $numberformatid = $supportedLanguages[$this->input->post('language')]['radixpoint'];
            
            $url = $this->input->post('url');
            if ($url == 'http://') {$url="";}
            $surveyls_title = $this->input->post('surveyls_title');
            if (!$surveyls_title)
            {
                
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be created because it did not have a title","js")."\")\n //-->\n</script>\n";
            } else
            {
                $this->load->helper('database');
                // Get random ids until one is found that is not used
                do
                {
                    $surveyid = sRandomChars(5,'123456789');
                    $isquery = "SELECT sid FROM ".$this->db->dbprefix('surveys')." WHERE sid=$surveyid";
                    $isresult = db_execute_assoc($isquery); // Checked
                }
                while ($isresult->num_rows()>0);
                
                
                $description = $this->input->post('description');
                $welcome = $this->input->post('welcome');
                $urldescp = $this->input->post('urldescrip');
                
                $template = $this->input->post('template');
                if (!$template) {$template='default';}
                if($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') != 1 && !hasTemplateManageRights($this->session->userdata('loginID'), $this->input->post('template'))) $template = "default";
        
                // insert base language into surveys_language_settings
                if ($this->config->item('filterxsshtml'))
                {
                    /**
                    require_once("../classes/inputfilter/class.inputfilter_clean.php");
                    $myFilter = new InputFilter('','',1,1,1);
        
                    $surveyls_title=$myFilter->process($surveyls_title);
                    $description=$myFilter->process($description);
                    $welcome=$myFilter->process($welcome);
                    $this->input->post['urldescrip']=$myFilter->process($this->input->post['urldescrip']); */
                }
                else
                {
                    $surveyls_title = html_entity_decode($surveyls_title, ENT_QUOTES, "UTF-8");
                    $description = html_entity_decode($description, ENT_QUOTES, "UTF-8");
                    $welcome = html_entity_decode($welcome, ENT_QUOTES, "UTF-8");
                    $urldescp = html_entity_decode($urldescp, ENT_QUOTES, "UTF-8");
                }
        
                //make sure only numbers are passed within the $this->input->post variable
                $dateformat = (int) $this->input->post('dateformat');
                $tokenlength = (int) $this->input->post('tokenlength');
        
                $expires = $this->input->post('expires');
                if (trim($expires)=='')
                {
                    $expires=null;
                }
                else
                {
                    $this->load->library('Date_Time_Converter',array($expires , "d.m.Y H:i"));
                    $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($expires , "d.m.Y H:i");
                    $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
                    $expires=$browsedatafield;
                }
                $startdate = $this->input->post('startdate');
                if (trim($startdate)=='')
                {
                    $startdate=null;
                }
                else
                {
                    $this->load->library('Date_Time_Converter',array($startdate , "d.m.Y H:i"));
                    $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($startdate , "d.m.Y H:i");
                    $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
                    $startdate=$browsedatafield;
                }
        
                
                $insertarray=array( 'sid'=>$surveyid,
                                    'owner_id'=>$this->session->userdata['loginID'],
                                    'admin'=>$this->input->post('admin'),
                                    'active'=>'N',
                                    'expires'=>$expires,
                                    'startdate'=>$startdate,
                                    'adminemail'=>$this->input->post('adminemail'),
                                    'bounce_email'=>$this->input->post('bounce_email'),
                                    'anonymized'=>$this->input->post('anonymized'),
                                    'faxto'=>$this->input->post('faxto'),
                                    'format'=>$this->input->post('format'),
                                    'savetimings'=>$this->input->post('savetimings'),
                                    'template'=>$template,
                                    'language'=>$this->input->post('language'),
                                    'datestamp'=>$this->input->post('datestamp'),
                                    'ipaddr'=>$this->input->post('ipaddr'),
                                    'refurl'=>$this->input->post('refurl'),
                                    'usecookie'=>$this->input->post('usecookie'),
                                    'emailnotificationto'=>$this->input->post('emailnotificationto'),
                                    'allowregister'=>$this->input->post('allowregister'),
                                    'allowsave'=>$this->input->post('allowsave'),
                                    'navigationdelay'=>$this->input->post('navigationdelay'),
                                    'autoredirect'=>$this->input->post('autoredirect'),
                                    'showXquestions'=>$this->input->post('showXquestions'),
                                    'showgroupinfo'=>$this->input->post('showgroupinfo'),
                                    'showqnumcode'=>$this->input->post('showqnumcode'),
                                    'shownoanswer'=>$this->input->post('shownoanswer'),
                                    'showwelcome'=>$this->input->post('showwelcome'),
                                    'allowprev'=>$this->input->post('allowprev'),
                                    'allowjumps'=>$this->input->post('allowjumps'),
                                    'nokeyboard'=>$this->input->post('nokeyboard'),
                                    'showprogress'=>$this->input->post('showprogress'),
                                    'printanswers'=>$this->input->post('printanswers'),
                //                            'usetokens'=>$this->input->post['usetokens'],
                                    'datecreated'=>date("Y-m-d"),
                                    'listpublic'=>$this->input->post('public'),
                                    'htmlemail'=>$this->input->post('htmlemail'),
                                    'tokenanswerspersistence'=>$this->input->post('tokenanswerspersistence'),
                                    'alloweditaftercompletion'=>$this->input->post('alloweditaftercompletion'),
                                    'usecaptcha'=>$this->input->post('usecaptcha'),
                                    'publicstatistics'=>$this->input->post('publicstatistics'),
                                    'publicgraphs'=>$this->input->post('publicgraphs'),
                                    'assessments'=>$this->input->post('assessments'),
                                    'emailresponseto'=>$this->input->post('emailresponseto'),
                                    'tokenlength'=>$tokenlength
                );
                
                /** $dbtablename=$this->db->dbprefix('surveys');
                $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
                $isresult = $connect->Execute($isquery) or safe_die ($isrquery."<br />".$connect->ErrorMsg()); // Checked */
                $this->load->model('surveys_model');
                $this->surveys_model->insertNewSurvey($insertarray);
        
                
                
                // Fix bug with FCKEditor saving strange BR types
                $surveyls_title=fix_FCKeditor_text($surveyls_title);
                $description=fix_FCKeditor_text($description);
                $welcome=fix_FCKeditor_text($welcome);
                
                $this->load->library('Limesurvey_lang',array($this->input->post('language')));
                $bplang = $this->limesurvey_lang; //new limesurvey_lang($this->input->post['language']);
                
                $aDefaultTexts=self::_aTemplateDefaultTexts($bplang,'unescaped');     
                $is_html_email = false;
                if ($this->input->post('htmlemail') && $this->input->post('htmlemail') == "Y")
                {
                    $is_html_email = true;
                    $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$is_html_email,'unescaped');
                }
                
                $insertarray=array( 'surveyls_survey_id'=>$surveyid,
                                    'surveyls_language'=>$this->input->post('language'),
                                    'surveyls_title'=>$surveyls_title,
                                    'surveyls_description'=>$description,
                                    'surveyls_welcometext'=>$welcome,
                                    'surveyls_urldescription'=>$this->input->post('urldescrip'),
                                    'surveyls_endtext'=>$this->input->post('endtext'),
                                    'surveyls_url'=>$url,
                                    'surveyls_email_invite_subj'=>$aDefaultTexts['invitation_subject'],
                                    'surveyls_email_invite'=>conditional_nl2br($aDefaultTexts['invitation'],$is_html_email,'unescaped'),
                                    'surveyls_email_remind_subj'=>$aDefaultTexts['reminder_subject'],
                                    'surveyls_email_remind'=>conditional_nl2br($aDefaultTexts['reminder'],$is_html_email,'unescaped'),
                                    'surveyls_email_confirm_subj'=>$aDefaultTexts['confirmation_subject'],
                                    'surveyls_email_confirm'=>conditional_nl2br($aDefaultTexts['confirmation'],$is_html_email,'unescaped'),
                                    'surveyls_email_register_subj'=>$aDefaultTexts['registration_subject'],
                                    'surveyls_email_register'=>conditional_nl2br($aDefaultTexts['registration'],$is_html_email,'unescaped'),
                                    'email_admin_notification_subj'=>$aDefaultTexts['admin_notification_subject'],
                                    'email_admin_notification'=>conditional_nl2br($aDefaultTexts['admin_notification'],$is_html_email,'unescaped'),
                                    'email_admin_responses_subj'=>$aDefaultTexts['admin_detailed_notification_subject'],
                                    'email_admin_responses'=>$aDefaultTexts['admin_detailed_notification'],
                                    'surveyls_dateformat'=>$dateformat,
                                    'surveyls_numberformat'=>$numberformatid
                                  );
                /**$dbtablename=db_table_name_nq('surveys_languagesettings');
                $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
                $isresult = $connect->Execute($isquery) or safe_die ($isquery."<br />".$connect->ErrorMsg()); // Checked */
                $this->load->model('surveys_languagesettings_model');
                $this->surveys_languagesettings_model->insertNewSurvey($insertarray);
                unset($bplang);
                
                $this->session->set_userdata('flashmessage',$clang->gT("Survey was successfully added."));                    
                
                
                // Update survey permissions
                self::_GiveAllSurveyPermissions($this->session->userdata('loginID'),$surveyid);
                
                $surveyselect = getsurveylist();
                
                // Create initial Survey table
                //include("surveytable_functions.php");
                //$creationResult = surveyCreateTable($surveyid);
                // Survey table could not be created
                //if ($creationResult !== true)
                //{
                //    safe_die ("Initial survey table could not be created, please report this as a bug."."<br />".$creationResult);
                //}
            }
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid));
            }
        }
        
                
        if (($action == "updatesurveylocalesettings") && bHasSurveyPermission($surveyid,'surveylocale','update'))
        {
            $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
            $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
            /**require_once("../classes/inputfilter/class.inputfilter_clean.php");
            $myFilter = new InputFilter('','',1,1,1);
            */
            foreach ($languagelist as $langname)
            {
                if ($langname)
                {
                    $url = $this->input->post('url_'.$langname);
                    if ($url == 'http://') {$url="";}
    
                    // Clean XSS attacks
                    /**if ($filterxsshtml) //not required. As we are using input class, XSS filetring is done automatically!
                    {
                        $_POST['short_title_'.$langname]=$myFilter->process($_POST['short_title_'.$langname]);
                        $_POST['description_'.$langname]=$myFilter->process($_POST['description_'.$langname]);
                        $_POST['welcome_'.$langname]=$myFilter->process($_POST['welcome_'.$langname]);
                        $_POST['endtext_'.$langname]=$myFilter->process($_POST['endtext_'.$langname]);
                        $_POST['urldescrip_'.$langname]=$myFilter->process($_POST['urldescrip_'.$langname]);
                        $_POST['url_'.$langname]=$myFilter->process($_POST['url_'.$langname]);
                    } 
                    else
                    {
                        $_POST['short_title_'.$langname] = html_entity_decode($_POST['short_title_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['description_'.$langname] = html_entity_decode($_POST['description_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['welcome_'.$langname] = html_entity_decode($_POST['welcome_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['endtext_'.$langname] = html_entity_decode($_POST['endtext_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['urldescrip_'.$langname] = html_entity_decode($_POST['urldescrip_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['url_'.$langname] = html_entity_decode($_POST['url_'.$langname], ENT_QUOTES, "UTF-8");
                    } */
    
                    // Fix bug with FCKEditor saving strange BR types
                    $short_title = $this->input->post('short_title_'.$langname);
                    $description = $this->input->post('description_'.$langname);
                    $welcome = $this->input->post('welcome_'.$langname);
                    $endtext = $this->input->post('endtext_'.$langname);
                    
                    $short_title=fix_FCKeditor_text($short_title);
                    $description=fix_FCKeditor_text($description);
                    $welcome=fix_FCKeditor_text($welcome);
                    $endtext=fix_FCKeditor_text($endtext);
                    
                    $data = array(
                    'surveyls_title' => $short_title,
                    'surveyls_description' => $description,
                    'surveyls_welcometext' => $welcome,
                    'surveyls_endtext' => $endtext,
                    'surveyls_url' => $url,
                    'surveyls_urldescription' => $this->input->post('urldescrip_'.$langname),
                    'surveyls_dateformat' => $this->input->post('dateformat_'.$langname),
                    'surveyls_numberformat' => $this->input->post('numberformat_'.$langname)
                    );
                    //In 'surveyls_survey_id' => $surveyid, it was initially $postsid. returnglobal not working properly!
                    $condition = array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $langname);
                    /**
                    $usquery = "UPDATE ".db_table_name('surveys_languagesettings')." \n"
                    . "SET surveyls_title='".db_quote($short_title)."', surveyls_description='".db_quote($description)."',\n"
                    . "surveyls_welcometext='".db_quote($welcome)."',\n"
                    . "surveyls_endtext='".db_quote($endtext)."',\n"
                    . "surveyls_url='".db_quote($url)."',\n"
                    . "surveyls_urldescription='".db_quote($_POST['urldescrip_'.$langname])."',\n"
                    . "surveyls_dateformat='".db_quote($_POST['dateformat_'.$langname])."',\n"
                    . "surveyls_numberformat='".db_quote($_POST['numberformat_'.$langname])."'\n"
                    . "WHERE surveyls_survey_id=".$postsid." and surveyls_language='".$langname."'"; */
                    $this->load->model('surveys_languagesettings_model');
                    
                    $usresult = $this->surveys_languagesettings_model->update($data,$condition);// or safe_die("Error updating local settings");   // Checked
                }
            }
            $this->session->set_userdata('flashmessage',$clang->gT("Survey text elements successfully saved."));
        }
        
        if ($databaseoutput != '')
        {
            echo $databaseoutput;
        }
        else
        {
            redirect(site_url('admin/survey/view/'.$surveyid));
        }
        
        
    }
    
    /**
    * Returns the default email template texts as array
    * 
    * @param mixed $oLanguage Required language translationb object
    * @param string $mode Escape mode for the translation function
    * @return array
    */
    function _aTemplateDefaultTexts($oLanguage, $mode='html'){
        return array(
          'admin_detailed_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME} with results",$mode),
          'admin_detailed_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}\n\n\nThe following answers were given by the participant:\n{ANSWERTABLE}",$mode),
          'admin_detailed_notification_css'=>'<style type="text/css">
                                                    .printouttable {
                                                      margin:1em auto;
                                                    }
                                                    .printouttable th {
                                                      text-align: center;
                                                    }
                                                    .printouttable td {
                                                      border-color: #ddf #ddf #ddf #ddf;
                                                      border-style: solid;
                                                      border-width: 1px;
                                                      padding:0.1em 1em 0.1em 0.5em;
                                                    }
    
                                                    .printouttable td:first-child {
                                                      font-weight: 700;
                                                      text-align: right;
                                                      padding-right: 5px;
                                                      padding-left: 5px;
    
                                                    }
                                                    .printouttable .printanswersquestion td{
                                                      background-color:#F7F8FF;
                                                    }
    
                                                    .printouttable .printanswersquestionhead td{
                                                      text-align: left;
                                                      background-color:#ddf;
                                                    }
    
                                                    .printouttable .printanswersgroup td{
                                                      text-align: center;        
                                                      font-weight:bold;
                                                      padding-top:1em;
                                                    }
                                                    </style>',
          'admin_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME}",$mode),
          'admin_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}",$mode),
          'confirmation_subject'=>$oLanguage->gT("Confirmation of your participation in our survey"),
          'confirmation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nthis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",$mode),
          'invitation_subject'=>$oLanguage->gT("Invitation to participate in a survey",$mode),
          'invitation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nyou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
          'reminder_subject'=>$oLanguage->gT("Reminder to participate in a survey",$mode),
          'reminder'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
          'registration_subject'=>$oLanguage->gT("Survey registration confirmation",$mode),
          'registration'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",$mode)
        );
    }
    
    /**
    * Gives all available survey permissions for a certain survey to a user 
    * 
    * @param mixed $iUserID  The User ID
    * @param mixed $iSurveyID The Survey ID
    */
    function _GiveAllSurveyPermissions($iUserID, $iSurveyID)
    {
         //$clang = $this->Limesurvey_lang;
         $aPermissions=aGetBaseSurveyPermissions();
         
         $aPermissionsToSet=array();
         foreach ($aPermissions as $sPermissionName=>$aPermissionDetails)
         {
             foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue)
             {
               if (in_array($sPermissionDetailKey,array('create','read','update','delete','import','export')) && $sPermissionDetailValue==true)
               {
                   $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey]=1;    
               }
                 
             }
         }
         
         self::_SetSurveyPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
    }
    
    /**
    * Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
    * 
    * @param int $iUserID The User ID
    * @param int $iSurveyID The Survey ID 
    * @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
    */
    function _SetSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
    {
        //global $connect, $surveyid;
        $iUserID=sanitize_int($iUserID);
        $condition = array('sid' => $iSurveyID, 'uid' => $iUserID);
        $this->load->model('survey_permissions_model');
        $this->survey_permissions_model->deleteSomeRecords($condition);
        //$sQuery = "delete from ".db_table_name('survey_permissions')." WHERE sid = {$iSurveyID} AND uid = {$iUserID}";
        //$connect->Execute($sQuery);
        $bResult=true;
        
        foreach($aPermissions as $sPermissionname=>$aPermissions)
        {
            if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
            if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
            if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
            if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
            if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
            if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
            if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
            {
                //$sQuery = "INSERT INTO ".db_table_name('survey_permissions')." (sid, uid, permission, create_p, read_p, update_p, delete_p, import_p, export_p)
               //           VALUES ({$iSurveyID},{$iUserID},'{$sPermissionname}',{$aPermissions['create']},{$aPermissions['read']},{$aPermissions['update']},{$aPermissions['delete']},{$aPermissions['import']},{$aPermissions['export']})";
                
                $data = array();
                $data = array(
                        'sid' => $iSurveyID,
                        'uid' => $iUserID,
                        'permission' => $sPermissionname,
                        'create_p' => $aPermissions['create'],
                        'read_p' => $aPermissions['read'],
                        'update_p' => $aPermissions['update'],
                        'delete_p' => $aPermissions['delete'],
                        'import_p' => $aPermissions['import'],
                        'export_p' => $aPermissions['export']
                        );
                $this->load->model('survey_permissions_model');
                $this->survey_permissions_model->insertSomeRecords($data);
                //$bResult=$connect->Execute($sQuery);
            }
        }
        //return $bResult;
    }



}