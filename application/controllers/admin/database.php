<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Database extends AdminController {


    function __construct()
	{
		parent::__construct();
	}
    
    function index($action)
    {
        echo "hi";
        global $clang;
        $postsid=returnglobal('sid');
        $postgid=returnglobal('gid');
        $postqid=returnglobal('qid');
        $postqaid=returnglobal('qaid');
        $databaseoutput = '';
        if ($action == "insertsurvey" && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))
        {
            
            
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            // $this->input->post['language']
        
            $supportedLanguages = getLanguageData();
            $numberformatid = $supportedLanguages[$this->input->post('language')]['radixpoint'];
            
            $url = $this->input->post('url');
            if ($url == 'http://') {$url="";}
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
                
                $surveyls_title = $this->input->post('surveyls_title');
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
                                    'admin'=>$this->input->post['admin'],
                                    'active'=>'N',
                                    'expires'=>$expires,
                                    'startdate'=>$startdate,
                                    'adminemail'=>$this->input->post['adminemail'],
                                    'bounce_email'=>$this->input->post['bounce_email'],
                                    'anonymized'=>$this->input->post['anonymized'],
                                    'faxto'=>$this->input->post['faxto'],
                                    'format'=>$this->input->post['format'],
                                    'savetimings'=>$this->input->post['savetimings'],
                                    'template'=>$template,
                                    'language'=>$this->input->post['language'],
                                    'datestamp'=>$this->input->post['datestamp'],
                                    'ipaddr'=>$this->input->post['ipaddr'],
                                    'refurl'=>$this->input->post['refurl'],
                                    'usecookie'=>$this->input->post['usecookie'],
                                    'emailnotificationto'=>$this->input->post['emailnotificationto'],
                                    'allowregister'=>$this->input->post['allowregister'],
                                    'allowsave'=>$this->input->post['allowsave'],
                                    'navigationdelay'=>$this->input->post['navigationdelay'],
                                    'autoredirect'=>$this->input->post['autoredirect'],
                                    'showXquestions'=>$this->input->post['showXquestions'],
                                    'showgroupinfo'=>$this->input->post['showgroupinfo'],
                                    'showqnumcode'=>$this->input->post['showqnumcode'],
                                    'shownoanswer'=>$this->input->post['shownoanswer'],
                                    'showwelcome'=>$this->input->post['showwelcome'],
                                    'allowprev'=>$this->input->post['allowprev'],
                                    'allowjumps'=>$this->input->post['allowjumps'],
                                    'nokeyboard'=>$this->input->post['nokeyboard'],
                                    'showprogress'=>$this->input->post['showprogress'],
                                    'printanswers'=>$this->input->post['printanswers'],
                //                            'usetokens'=>$this->input->post['usetokens'],
                                    'datecreated'=>date("Y-m-d"),
                                    'listpublic'=>$this->input->post['public'],
                                    'htmlemail'=>$this->input->post['htmlemail'],
                                    'tokenanswerspersistence'=>$this->input->post['tokenanswerspersistence'],
                                    'alloweditaftercompletion'=>$this->input->post['alloweditaftercompletion'],
                                    'usecaptcha'=>$this->input->post['usecaptcha'],
                                    'publicstatistics'=>$this->input->post['publicstatistics'],
                                    'publicgraphs'=>$this->input->post['publicgraphs'],
                                    'assessments'=>$this->input->post['assessments'],
                                    'emailresponseto'=>$this->input->post['emailresponseto'],
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
                $aDefaultTexts=aTemplateDefaultTexts($bplang,'unescaped');     
                $is_html_email = false;
                if ($this->input->post('htmlemail') && $this->input->post('htmlemail') == "Y")
                {
                    $is_html_email = true;
                    $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$is_html_email,'unescaped');
                }
        
                $insertarray=array( 'surveyls_survey_id'=>$surveyid,
                                    'surveyls_language'=>$this->input->post['language'],
                                    'surveyls_title'=>$surveyls_title,
                                    'surveyls_description'=>$description,
                                    'surveyls_welcometext'=>$welcome,
                                    'surveyls_urldescription'=>$this->input->post['urldescrip'],
                                    'surveyls_endtext'=>$this->input->post['endtext'],
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
                GiveAllSurveyPermissions($this->session->userdata('loginID'),$surveyid);
        
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
        }
        
        if ($databaseoutput != '')
        {
            echo $databaseoutput;
        }
        else
        {
            redirect(site_url('admin/index/'.$surveyid));
        }
        
        
    }


}