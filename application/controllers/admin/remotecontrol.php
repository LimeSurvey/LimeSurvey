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

class remotecontrol extends Survey_Common_Action
{
    /**
    * @var Zend_XmlRpc_Server
    */
    protected $xmlrpc;

    /**
    * This is the XML-RPC server routine
    *
    * @access public
    * @return void
    */
    public function run()
    {
        $RPCType=Yii::app()->getConfig("RPCInterface");

        if ($RPCType=='xml')
        {
            $cur_path = get_include_path();

            set_include_path($cur_path . PATH_SEPARATOR . APPPATH . 'helpers');

            // Yii::import was causing problems for some odd reason
            require_once('Zend/XmlRpc/Server.php');
            require_once('Zend/XmlRpc/Server/Exception.php');
            require_once('Zend/XmlRpc/Value/Exception.php');
            $this->xmlrpc = new Zend_XmlRpc_Server();
            $this->xmlrpc->sendArgumentsToAllMethods(false);
            $this->xmlrpc->setClass('remotecontrol_handle', '', $this->controller);
            echo $this->xmlrpc->handle();
        }
        elseif($RPCType=='json')
        {
            Yii::app()->loadLibrary('jsonRPCServer');
            $oHandler=new remotecontrol_handle($this->controller);
            jsonRPCServer::handle($oHandler);
        }
        exit;
    }

    /**
    * Simple procedure to test most RPC functions
    *
    */
    public function test()
    {
        $sFileToImport=dirname(Yii::app()->basePath).DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'demosurveys'.DIRECTORY_SEPARATOR.'limesurvey2_sample_survey_english.lss';
//        $sFileToImport=dirname(Yii::app()->basePath).DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'demosurveys'.DIRECTORY_SEPARATOR.'survey_archive_example_feedback_survey.zip';
        Yii::app()->loadLibrary('jsonRPCClient');
        $myJSONRPCClient = new jsonRPCClient(Yii::app()->getBaseUrl(true).'/'.dirname(Yii::app()->request->getPathInfo()));
        $sSessionKey= $myJSONRPCClient->get_session_key('admin','password');
        if (is_array($sSessionKey)) {echo $sSessionKey['status']; die();}
        else
        {
            echo 'Retrieved session key'.'<br>';
        }

        $sLSSData=base64_encode(file_get_contents($sFileToImport));
        $iSurveyID=$myJSONRPCClient->import_survey($sSessionKey, $sLSSData, 'zip','Test import by JSON_RPC',1000);
        echo 'Created new survey SID:'.$iSurveyID.'<br>';

/*
        Very simple example to export responses as Excel file
        $aResult=$myJSONRPCClient->export_reponses($sSessionKey,$iSurveyID,'xls');
        file_put_contents('d:\test.xls',base64_decode(chunk_split($aResult)));
*/


        $aResult=$myJSONRPCClient->activate_survey($sSessionKey, $iSurveyID);
        if ($aResult['status']=='OK')
        {
            echo 'Survey '.$iSurveyID.' successfully activated.<br>';
        }
        $aResult=$myJSONRPCClient->activate_tokens($sSessionKey, $iSurveyID,array(1,2));
        if ($aResult['status']=='OK')
        {
            echo 'Tokens for Survey ID '.$iSurveyID.' successfully activated.<br>';
        }
        $aResult=$myJSONRPCClient->modify_survey_settings($sSessionKey, $iSurveyID,array('faxto'=>'0800-LIMESURVEY'));
        if ($aResult['status']=='OK')
        {
            echo 'Modified survey settings for survey '.$iSurveyID.'<br>';
        }
        $aResult=$myJSONRPCClient->add_survey_language($sSessionKey, $iSurveyID,'ar');
        if ($aResult['status']=='OK')
        {
            echo 'Added Arabian as additional language'.'<br>';
        }
        $aResult=$myJSONRPCClient->modify_survey_locale_settings($sSessionKey, $iSurveyID,array('surveyls_welcometext'=>'An Arabian welcome text!'),'ar');
        if ($aResult['status']=='OK')
        {
            echo 'Modified survey locale setting welcometext for Arabian in survey ID '.$iSurveyID.'<br>';
        }

        $aResult=$myJSONRPCClient->delete_survey_language($sSessionKey, $iSurveyID,'ar');
        if ($aResult['status']=='OK')
        {
            echo 'Removed Arabian as additional language'.'<br>';
        }
        die();
        $aResult=$myJSONRPCClient->delete_survey($sSessionKey, $iSurveyID);
        echo 'Deleted survey SID:'.$iSurveyID.'-'.$aResult['status'].'<br>';

        // Release the session key - close the session
        $Result= $myJSONRPCClient->release_session_key($sSessionKey);
        echo 'Closed the session'.'<br>';

    }


}
/**
* This class handles all methods of the RPCs
*/
class remotecontrol_handle
{
    /**
    * @var AdminController
    */
    protected $controller;

    /**
    * Constructor, stores the action instance into this handle class
    *
    * @access public
    * @param AdminController $controller
    * @return void
    */
    public function __construct(AdminController $controller)
    {
        $this->controller = $controller;
    }


    /**
    * RPC routine to create a session key
    *
    * @access public
    * @param string $username
    * @param string $password
    * @return string
    */
    public function get_session_key($username, $password)
    {
        if ($this->_doLogin($username, $password))
        {
            $this->_jumpStartSession($username);
            $sSessionKey = randomChars(32);

            $session = new Sessions;
            $session->id = $sSessionKey;
            $session->expire = time() + Yii::app()->getConfig('iSessionExpirationTime');
            $session->data = $username;
            $session->save();

            return $sSessionKey;
        }
        else
            return array('status' => 'Invalid user name or password');
    }

    /**
    * Closes the RPC session
    *
    * @access public
    * @param string $sSessionKey
    * @return string
    */
    public function release_session_key($sSessionKey)
    {
        Sessions::model()->deleteAllByAttributes(array('id' => $sSessionKey));
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        return 'OK';
    }

    /**
     * RPC routine to get settings
     *
     * @access public
     * @param string $sSessionKey
     * @param string $sSetttingName
     * @return string
     */
   public function get_site_settings($sSessionKey,$sSetttingName)
    {
       if ($this->_checkSessionKey($sSessionKey))
       {
		   if( Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		   {     
			   if (Yii::app()->getRegistry($sSetttingName) !== false)
					return Yii::app()->getRegistry($sSetttingName);
				elseif (Yii::app()->getConfig($sSetttingName) !== false)
					return Yii::app()->getConfig($sSetttingName);
				else
					return array('status' => 'Invalid setting');	
			}
			else
				return array('status' => 'Invalid setting'); 	
        }
        else
			return array('status' => 'Invalid session key');
    }

	/**
     * RPC routine to create an empty survey with minimum details
     * Used as a placeholder for importing groups and/or questions
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
	 * @param string $sSurveyTitle
	 * @param string $sSurveyLanguage	 
	 * @param string $sformat
     * @return string
     * @throws Zend_XmlRpc_Server_Exception
     */
	public function create_survey($sSessionKey, $iSurveyID, $sSurveyTitle, $sSurveyLanguage, $sformat = 'G')
	{
		Yii::app()->loadHelper("surveytranslator");
		if ($this->_checkSessionKey($sSessionKey))
        {
			if (Yii::app()->session['USER_RIGHT_CREATE_SURVEY'])
			{	
				if( $sSurveyTitle=='' || $sSurveyLanguage=='' || !array_key_exists($sSurveyLanguage,getLanguageDataRestricted()) || !in_array($sformat, array('A','G','S')))
					return array('status' => 'Faulty parameters');
					
				$aInsertData = array('template' => 'default',
									'owner_id' => Yii::app()->session['loginID'],
									'active' => 'N',
									'language'=>$sSurveyLanguage,
									'format' => $sformat
									);

				if(Yii::app()->getConfig('filterxsshtml') && Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
					$xssfilter = true;
				else
					$xssfilter = false;

				if (!is_null($iSurveyID))
					$aInsertData['wishSID'] = $iSurveyID;
					
				try 
				{
					$iNewSurveyid = Survey::model()->insertNewSurvey($aInsertData, $xssfilter);
					if (!$iNewSurveyid)
							return array('status' => 'Creation Failed');
				   
					$sTitle = html_entity_decode($sSurveyTitle, ENT_QUOTES, "UTF-8");

					// Load default email templates for the chosen language
					$oLanguage = new Limesurvey_lang($sSurveyLanguage);
					$aDefaultTexts = templateDefaultTexts($oLanguage, 'unescaped');
					unset($oLanguage);
					
					$bIsHTMLEmail = false;
					
					$aInsertData = array(
						'surveyls_survey_id' => $iNewSurveyid,
						'surveyls_title' => $sTitle,
						'surveyls_language' => $sSurveyLanguage,          
						'surveyls_email_invite_subj' => $aDefaultTexts['invitation_subject'],
						'surveyls_email_invite' => conditionalNewlineToBreak($aDefaultTexts['invitation'], $bIsHTMLEmail, 'unescaped'),
						'surveyls_email_remind_subj' => $aDefaultTexts['reminder_subject'],
						'surveyls_email_remind' => conditionalNewlineToBreak($aDefaultTexts['reminder'], $bIsHTMLEmail, 'unescaped'),
						'surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
						'surveyls_email_confirm' => conditionalNewlineToBreak($aDefaultTexts['confirmation'], $bIsHTMLEmail, 'unescaped'),
						'surveyls_email_register_subj' => $aDefaultTexts['registration_subject'],
						'surveyls_email_register' => conditionalNewlineToBreak($aDefaultTexts['registration'], $bIsHTMLEmail, 'unescaped'),
						'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
						'email_admin_notification' => conditionalNewlineToBreak($aDefaultTexts['admin_notification'], $bIsHTMLEmail, 'unescaped'),
						'email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
						'email_admin_responses' => $aDefaultTexts['admin_detailed_notification']
						);
					
					$langsettings = new Surveys_languagesettings;
					$langsettings->insertNewSurvey($aInsertData, $xssfilter);
					Survey_permissions::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'], $iNewSurveyid);

					return 	$iNewSurveyid;	
				}
				catch(Exception $e)
				{
					return array('status' => $e->getmessage());
				}			
			}
			else
				return array('status' => 'No permission');
		}
		else
			return array('status' => 'Invalid session key');			
	}

    /**
    * RPC routine to delete a survey
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID
    * @return array
    */
    public function delete_survey($sSessionKey, $iSurveyID)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasSurveyPermission($iSurveyID, 'survey', 'delete'))
            {
                Survey::model()->deleteSurvey($iSurveyID,true);
                return array('status' => 'OK');
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
    }

    /**
    * RPC routine to activate a survey
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID The id of the survey to be activated
    * @param string dStart - Optional parameter Startdate
    * @param string dEnd - Optional parameter Expires
    * @return array the result of the activation
    */
    public function activate_survey($sSessionKey, $iSurveyID, $dStart='', $dEnd='')
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
                return array('status' => 'Error: Invalid survey ID');

			$date_pattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
			try
			{
				if($dStart!='' && filter_var($dStart, FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>$date_pattern))))
						Survey::model()->updateByPk($iSurveyID, array('startdate'=> $dStart));
				
				if($dEnd!='' && filter_var($dEnd, FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>$date_pattern))))
						Survey::model()->updateByPk($iSurveyID, array('expires'=> $dEnd));
			}
			catch(Exception $e)
			{
				//return array('status' => $e->getmessage());
			}

            if (hasSurveyPermission($iSurveyID, 'surveyactivation', 'update'))
            {
                Yii::app()->loadHelper('admin/activate');
                $aActivateResults = activateSurvey($iSurveyID);

                if (isset($aActivateResults['error'])) return array('status' => 'Error: '.$aActivateResults['error']);
                else
                {
                    return $aActivateResults;
                }
            }
            else
                return array('status' => 'No permission');
        }
 		else
			return array('status' => 'Invalid session key');         
    }

    /**
    * RPC routine to import a survey - imports lss,csv,xls or survey zip archive
    *
    * @access public
    * @param string $sSessionKey
    * @param string $sImportData String containing the BASE 64 encoded data of a lss,csv,xls or survey zip archive
    * @param string $sImportDataType  lss,csv,xls or zip
    * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
    * @return integer iSurveyID  - ID of the new survey
    */
    public function import_survey($sSessionKey, $sImportData, $sImportDataType, $sNewSurveyName=NULL, $DestSurveyID=NULL)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            if (hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
            {
                if (!in_array($sImportDataType,array('zip','csv','xls','lss'))) return array('status' => 'Invalid extension');
                Yii::app()->loadHelper('admin/import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40).'.'.$sImportDataType;
                file_put_contents($sFullFilePath,base64_decode(chunk_split($sImportData)));
                $aImportResults = importSurveyFile($sFullFilePath, true, $sNewSurveyName, $DestSurveyID);
                unlink($sFullFilePath);
                if (isset($aImportResults['error'])) return array('status' => 'Error: '.$aImportResults['error']);
                else
                {
                    return $aImportResults['newsid'];
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');        
    }

  /**
     * RPC routine to return the ids and info of surveys belonging to a user
     * Returns array of ids and info
     * If user is admin he can get surveys of every user 
     * else only the syrveys belonging to the user requesting will be shown
     *
     * @access public
     * @param string $sSessionKey
     * @param string $suser
     * @return array
     * @throws Zend_XmlRpc_Server_Exception
     */
	public function get_survey_list($sSessionKey, $suser='')
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
		   $current_user =  Yii::app()->session['user'];
		   if( Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 and $suser !='')
				$current_user = $suser;

		   $aUserData = User::model()->findByAttributes(array('users_name' => $current_user));		   
		   if (!isset($aUserData))
				return array('status' => 'Invalid user');	   
	  	  		   
		   $user_surveys = Survey::model()->findAllByAttributes(array("owner_id"=>$aUserData->attributes['uid'])); 		   
		   if(count($user_surveys)==0)
				return array('status' => 'No surveys found');
			
			foreach ($user_surveys as $asurvey)
				{
				$asurvey_ls = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $asurvey->primaryKey, 'surveyls_language' => $asurvey->language));
				if (!isset($asurvey_ls))
					$asurvey_title = '';
				else
					$asurvey_title = $asurvey_ls->attributes['surveyls_title'];
				$aData[]= array('sid'=>$asurvey->primaryKey,'surveyls_title'=>$asurvey_title,'startdate'=>$asurvey->attributes['startdate'],'expires'=>$asurvey->attributes['expires'],'active'=>$asurvey->attributes['active']);
				}
			return $aData;
        }
        else
			return array('status' => 'Invalid session key');				
	}

     /**
     * RPC routine to get survey summary, regarding token usage and survey participation
     * Return integer with the requested value
     * @access public
     * @param string $sSessionKey
     * @param int $sid
     * @param string $stats_name
     * @return string
     */
   public function get_survey_summary($sSessionKey,$iSurveyID, $stat_name)
    {
       $permitted_stats = array();
       if ($this->_checkSessionKey($sSessionKey))
       { 	   	  
			$permitted_token_stats = array('token_count', 
									'token_invalid', 
									'token_sent', 
									'token_opted_out',
									'token_completed'
									);					
			$permitted_survey_stats  = array('completed_responses',  
									'incomplete_responses', 
									'full_responses' 
									);  
			$permitted_stats = array_merge($permitted_survey_stats, $permitted_token_stats);						
			$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
			if (!isset($surveyidExists))
				return array('status' => 'Invalid surveyid');
				
			if(in_array($stat_name, $permitted_token_stats))	
			{
				if (tableExists('{{tokens_' . $iSurveyID . '}}'))
					$summary = Tokens_dynamic::model($iSurveyID)->summary();
				else
					return array('status' => 'No available data');
			}
			
			if(in_array($stat_name, $permitted_survey_stats) && !tableExists('{{survey_' . $iSurveyID . '}}'))	
				return array('status' => 'No available data');
									
			if (!in_array($stat_name, $permitted_stats)) 
				return array('status' => 'No such property');
		
			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{	
				switch($stat_name) 
				{
					case 'token_count':
						if (isset($summary))
							return $summary['tkcount'];
						break;
					case 'token_invalid':
						if (isset($summary))
							return $summary['tkinvalid'];
						break;	
					case 'token_sent':
						if (isset($summary))
							return $summary['tksent'];
						break;
					case 'token_opted_out':
						if (isset($summary))
							return $summary['tkoptout'];
						break;
					case 'token_completed';
						if (isset($summary))
							return $summary['tkcompleted'];
						break;
					case 'completed_responses':
						return Survey_dynamic::model($iSurveyID)->count('submitdate IS NOT NULL');
						break;
					case 'incomplete_responses':
						return Survey_dynamic::model($iSurveyID)->countByAttributes(array('submitdate' => null));
						break;
					case 'full_responses';
						return Survey_dynamic::model($iSurveyID)->count();
						break;			
					default:
						return array('status' => 'Data is not available');
				}
			}
			else
			return array('status' => 'No permission'); 		
        }
        else
			return array('status' => 'Invalid session key');	        
    } 
    
    /**
    * RPC routine to modify survey settings
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID  - ID of the survey
    * @param array|struct $aSurveyData - An array with the particular fieldnames as keys and their values to set on that particular survey
    * @return array of succeeded and failed nodifications according to internal validation.
    */
    public function modify_survey_settings($sSessionKey, $iSurveyID, $aSurveyData)
    { 
        if ($this->_checkSessionKey($sSessionKey))
        {               
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
            {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
            {
                // Remove fields that may not be modified
                unset($aSurveyData['active']);
                unset($aSurveyData['language']);
                unset($aSurveyData['additional_languages']);
                // Remove invalid fields
                $aDestinationFields=array_flip(Survey::model()->tableSchema->columnNames);
                $aSurveyData=array_intersect_key($aSurveyData,$aDestinationFields);
                $oSurvey=Survey::model()->findByPk($iSurveyID);
                $succeded = array();
                $failed = array();
                if ($oSurvey->active=='Y')
                {
                    // remove all fields that may not be changed when a survey is active
                    unset($aSurveyData['anonymized']);
                    unset($aSurveyData['datestamp']);
                    unset($aSurveyData['savetimings']);
                    unset($aSurveyData['ipaddr']);
                    unset($aSurveyData['refurl']);

                }
                foreach($aSurveyData as $sFieldName=>$sValue)
                {
                    if($this->_internal_validate($sFieldName,$sValue))
                    {
						$oSurvey->$sFieldName=$sValue;
						$succeded[$sFieldName]=$sValue;						
					}
					else
						$failed[$sFieldName]=$sValue;
                }
                try
                {
                    $oSurvey->save(); // save the change to database
                    $result = array('succeded'=>$succeded,'failed'=>$failed);
                    return $result;
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');        
    }

    
    /**
    * RPC routine to modify survey locale settings
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID  - ID of the survey
    * @param array|struct $aSurveyData - An array with the particular fieldnames as keys and their values to set on that particular survey
    * @param string $aLanguage - Optional - Language to update  - if not give the base language of the particular survey is used
    * @return array status=>OK, when save successful otherwise error text.
    */
    public function modify_survey_locale_settings($sSessionKey, $iSurveyID, $aSurveyLocaleData, $sLanguage=NULL)
    {
        Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
            {
                return array('status' => 'Error: Invalid survey ID');
            }
            
            if (is_null($sLanguage))
            {
                $sLanguage=$oSurvey->language;
            }

			if (!array_key_exists($sLanguage,getLanguageDataRestricted()))
				return array('status' => 'Error: Invalid language');

            if (hasSurveyPermission($iSurveyID, 'surveylocale', 'update'))
            {
                // Remove fields that may not be modified
                unset($aSurveyLocaleData['surveyls_language']);
                unset($aSurveyLocaleData['surveyls_survey_id']);

                // Remove invalid fields
                $aDestinationFields=array_flip(Surveys_languagesettings::model()->tableSchema->columnNames);

                $aSurveyLocaleData=array_intersect_key($aSurveyLocaleData,$aDestinationFields);
                $oSurveyLocale = Surveys_languagesettings::model()->findByPk(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLanguage));
                $succeded = array();
                $failed = array();
                foreach($aSurveyLocaleData as $sFieldName=>$sValue)
                {
					if($this->_internal_validate($sFieldName,$sValue))
                    {
						$oSurveyLocale->$sFieldName=$sValue;
						$succeded[$sFieldName]=$sValue;						
					}
					else
						$failed[$sFieldName]=$sValue;
                }
                try
                {
                    $oSurveyLocale->save(); // save the change to database
                    $result = array('succeded'=>$succeded,'failed'=>$failed);
                    return $result;
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key'); 
    }

    /**
     * RPC routine to get survey settings
     * Properties are those defined in tables surveys and surveys_language_settings
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param array $aSurveySettings
	 * @param string $slang
     * @return array
     */
   public function get_survey_settings($sSessionKey,$iSurveyID, $aSurveySettings, $slang=NULL)
    {
		Yii::app()->loadHelper("surveytranslator");
       if ($this->_checkSessionKey($sSessionKey))
       { 
			$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
			if (!isset($surveyidExists))
			{
				return array('status' => 'Error: Invalid survey ID');
			}		   
			if (hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
				{
					$aBasicDestinationFields=Surveys_languagesettings::model()->tableSchema->columnNames;
					$aLanguageDestinationFields=Survey::model()->tableSchema->columnNames;
					$aSurveyFields = array_merge($aBasicDestinationFields,$aLanguageDestinationFields);

					$aSurveySettings=array_intersect($aSurveySettings,$aSurveyFields);
					
					$abasic_attrs = Survey::model()->findByPk($iSurveyID)->getAttributes();
					
					if ($slang == NULL || !array_key_exists($slang,getLanguageDataRestricted()))
						$slang = $abasic_attrs['language'];

					$alang_attrs = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $slang))->getAttributes();	

					$result = array();
					foreach($aSurveySettings as $sproperty_name)
					{
						if (isset($abasic_attrs[$sproperty_name]))
							$result[$sproperty_name]=$abasic_attrs[$sproperty_name];
						elseif (isset($alang_attrs[$sproperty_name]))
							$result[$sproperty_name]=$alang_attrs[$sproperty_name];
						else
							$result[$sproperty_name]='Not available';
					}
					return $result;
				}
			else
				return array('status' => 'No permission');  
        }
        else
			return array('status' => 'Invalid Session key');  
    }

    /**
    * RPC routine to export responses
    * Returns the requested file as base64 encoded string
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID
    * @param string $sDocumentType pdf,csv,xls,doc
    * @param string $sCompletionStatus Optional 'complete','incomplete' or 'all' - defaults to complete
    * @param string $sHeadingType 'code','full' or 'abbreviated' Optional defaults to 'code'
    * @param string $sResponseType 'short' or 'long' Optional defaults to 'short'
    * @param integer $iFromResponseID Optional
    * @param integer $iToResponseID Optional
    * @return On success: Requested file as base 64-encoded string. On failure array with error information
    **/
    function export_reponses($sSessionKey, $iSurveyID, $sDocumentType, $sLanguageCode=null, $sCompletionStatus='all', $sHeadingType='code', $sResponseType='short', $iFromResponseID=null, $iToResponseID=null, $aFields=null)
    {
        if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');
        Yii::app()->loadHelper('admin/exportresults');
        if (!hasSurveyPermission($iSurveyID, 'responses', 'export')) return array('status' => 'No permission');
        if (is_null($sLanguageCode)) $sLanguageCode=getBaseLanguageFromSurveyID($iSurveyID);
        if (is_null($aFields)) $aFields=array_keys(createFieldMap($iSurveyID,'full',true,false,$sLanguageCode));
        if($sDocumentType=='xls'){
           // Cut down to the first 255 fields
           $aFields=array_slice($aFields,0,255);
        }
        $oFomattingOptions=new FormattingOptions();
        $oFomattingOptions->format=$sDocumentType;
        $oFomattingOptions->responseMinRecord=$iFromResponseID;
        $oFomattingOptions->responseMaxRecord=$iToResponseID;
        $oFomattingOptions->selectedColumns=$aFields;
        $oFomattingOptions->responseCompletionState=$sCompletionStatus;
        $oFomattingOptions->headingFormat=$sHeadingType;
        $oFomattingOptions->answerFormat=$sResponseType;
        $oExport=new ExportSurveyResultsService();
        $sFileData=$oExport->exportSurvey($iSurveyID,$sLanguageCode,$oFomattingOptions,'return');
        return base64_encode($sFileData);
    }

   /**
     * RPC routine to export statistics of a survey to a user
     * Returns string - base64 encoding of the statistics
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param string $docType
     * @param string $graph
     * @return string
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function send_statistics($sSessionKey, $iSurveyID, $docType='pdf', $graph='0')
    {
		Yii::app()->loadHelper('admin/statistics');
		
		if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');

		$surveyidExists = Survey::model()->findByPk($iSurveyID);
		if (!isset($surveyidExists))
			return array('status' => 'Error: Invalid survey ID');;
				
		if(Survey::model()->findByPk($iSurveyID)->owner_id != $_SESSION['loginID'])
			return array('status' => 'Error: No Permission');

		$allqs = Questions::model()->findAll("sid = '".$iSurveyID."'");
		foreach($allqs as $field)
		{
				$myField = $iSurveyID."X".$field['gid']."X".$field['qid'];					 
				// Multiple choice get special treatment
				if ($field['type'] == "M" || $field['type'] == "P") {$myField = "M$myField";}
				//numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
				if ($field['type'] == "N") {$myField = "N$myField";}					 
				if ($field['type'] == "Q") {$myField = "Q$myField";}
				// textfields get special treatment
				if ($field['type'] == "S" || $field['type'] == "T" || $field['type'] == "U"){$myField = "T$myField";}
				//statistics for Date questions are not implemented yet.
				if ($field['type'] == "D") {$myField = "D$myField";}
				if ($field['type'] == "F" || $field['type'] == "H")
				{
					$result3 = Answers::model()->findAllByAttributes(array('qid' => $field['qid'],'language' => getBaseLanguageFromSurveyID($sid)), array('order' => 'sortorder, answer'));
					foreach ($result3 as $row)
					{
						$myField = "$myField{$row['code']}";
					}
				}
				$summary[]=$myField;
		}

		switch ($docType)
		{
			case 'pdf':
				$tempFile = generate_statistics($iSurveyID,$summary,'all',$graph,$docType,'F');
				break;
			case 'xls':
				$tempFile = generate_statistics($iSurveyID,$summary,'all',0,$docType, 'F');
				break;
			case 'html':
				$html = generate_statistics($iSurveyID,$summary,'all',0,$docType, 'F');
				break;
		}
		
		if(isset($html))
			return base64_encode($html);
		else 
		{
		$result = file_get_contents($tempfile);
		unlink($tempfile);
		return base64_encode($result);
		}		
	}

    /**
    * RPC routine to add a survey language
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID ID of the survey where a token table will be created for
    * @param string $sLanguage  A valid language shortcut to add to the current survey. If the language already exists no error will be given.
    * @return array Status=>OK when successfull, otherwise the error description
    */
    public function add_survey_language($sSessionKey, $iSurveyID, $sLanguage)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
            {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
            {
                Yii::app()->loadHelper('surveytranslator');
                $aLanguages=getLanguageData();

                if(!isset($aLanguages[$sLanguage]))
                {
                    return array('status' => 'Invalid language');
                }
                $oSurvey=Survey::model()->findByPk($iSurveyID);
                if ($sLanguage==$oSurvey->language)
                {
                    return array('status' => 'OK');
                }
                $aLanguages=$oSurvey->getAdditionalLanguages();
                $aLanguages[]=$sLanguage;
                $aLanguages=array_unique($aLanguages);
                $oSurvey->additional_languages=implode(' ',$aLanguages);
                try
                {
                    $oSurvey->save(); // save the change to database
                    $languagedetails=getLanguageDetails($sLanguage);

                    $insertdata = array(
                    'surveyls_survey_id' => $iSurveyID,
                    'surveyls_language' => $sLanguage,
                    'surveyls_title' => '',
                    'surveyls_dateformat' => $languagedetails['dateformat']
                    );
                    $setting= new Surveys_languagesettings;
                    foreach ($insertdata as $k => $v)
                        $setting->$k = $v;
                    $setting->save();
                    fixLanguageConsistency($iSurveyID,$sLanguage);
                    return array('status' => 'OK');
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }

            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to delete a survey language
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID ID of the survey where a token table will be created for
    * @param string $sLanguage  A valid language shortcut to delete from the current survey. If the language does not exist in that survey no error will be given.
    * @return array Status=>OK when successfull, otherwise the error description
    */
    public function delete_survey_language($sSessionKey, $iSurveyID, $sLanguage)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
            {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
            {

                Yii::app()->loadHelper('surveytranslator');
                $aLanguages=getLanguageData();

                if(!isset($aLanguages[$sLanguage]))
                {
                    return array('status' => 'Invalid language');
                }
                $oSurvey=Survey::model()->findByPk($iSurveyID);
                if ($sLanguage==$oSurvey->language)
                {
                    return array('status' => 'Cannot remove base language');
                }
                $aLanguages=$oSurvey->getAdditionalLanguages();
                unset($aLanguages[$sLanguage]);
                $oSurvey->additional_languages=implode(' ',$aLanguages);
                try
                {
                    $oSurvey->save(); // save the change to database
                    Surveys_languagesettings::model()->deleteByPk(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLanguage));
                    cleanLanguagesFromSurvey($iSurveyID,$oSurvey->additional_languages);
                    return array('status' => 'OK');
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }

            }
            else
                return array('status' => 'No permission');
        }
    }

    /**
    * RPC routine to activate tokens
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iSurveyID ID of the survey where a token table will be created for
    * @param array $aAttributeFields  An array of integer describing any additional attribute fields
    * @return array Status=>OK when successfull, otherwise the error description
    */
    public function activate_tokens($sSessionKey, $iSurveyID, $aAttributeFields=array())
    {
        if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');
        if (hasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
            {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (is_array($aAttributeFields) && count($aAttributeFields)>0)
            {
                foreach ($aAttributeFields as &$sField)
                {
                    $sField= intval($sField);
                    $sField='attribute_'.$sField;
                }
                $aAttributeFields=array_unique($aAttributeFields);
            }
            Yii::app()->loadHelper('admin/token');
            if (createTokenTable($iSurveyID, $aAttributeFields))
            {
                return array('status' => 'OK');
            }
            else
            {
                return array('status' => 'Token table could not be created');
            }
        }
        else
            return array('status' => 'No permission');
    }



    /**
    * RPC routine to add a response to the survey response table
    * Returns the id of the inserted survey response
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID
    * @param struct $aResponseData
    * @return int The response ID
    */
    public function add_response($sSessionKey, $iSurveyID, $aResponseData)
    {
        if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');
        $oSurvey=Survey::model()->findByPk($iSurveyID);
        if (is_null($oSurvey))
        {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (hasSurveyPermission($iSurveyID, 'responses', 'create'))
        {
            if (!Yii::app()->db->schema->getTable('{{survey_' . $iSurveyID . '}}'))
                return array('status' => 'No survey response table');

            //set required values if not set

            // @todo: Some of this is part of the validation and should be done in the model instead
            if (!isset($aResponseData['submitdate']))
                $aResponseData['submitdate'] = date("Y-m-d H:i:s");
            if (!isset($aResponseData['startlanguage']))
                $aResponseData['startlanguage'] = getBaseLanguageFromSurveyID($iSurveyID);

            if ($oSurvey->datestamp=='Y')
            {
                if (!isset($aResponseData['datestamp']))
                    $aResponseData['datestamp'] = date("Y-m-d H:i:s");
                if (!isset($aResponseData['startdate']))
                    $aResponseData['startdate'] = date("Y-m-d H:i:s");
            }

            Survey_dynamic::sid($iSurveyID);
            $survey_dynamic = new Survey_dynamic;
            $result = $survey_dynamic->insert($aResponseData);

            if ($result)
                return $survey_dynamic->primaryKey;
            else
                return array('status' => 'Unable to add response');
        }
        else
            return array('status' => 'No permission');

    }

    /**
    * RPC routine to add a participant to a token table
    * Returns the inserted data including additional new information like the Token entry ID and the token
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID
    * @param struct $aParticipantData
    * @param bool Optional - Defaults to true and determins if the access token automatically created
    * @return array
    * @throws Zend_XmlRpc_Server_Exception
    */
    public function add_participants($sSessionKey, $iSurveyID, $aParticipantData, $bCreateToken=true)
    {
        if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');
        $oSurvey=Survey::model()->findByPk($iSurveyID);
        if (is_null($oSurvey))
        {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (hasSurveyPermission($iSurveyID, 'tokens', 'create'))
        {
            if (!Yii::app()->db->schema->getTable('{{tokens_' . $iSurveyID . '}}'))
                return array('status' => 'No token table');

            $aDestinationFields = Yii::app()->db->schema->getTable('{{tokens_' . $iSurveyID . '}}')->getColumnNames();
            $aDestinationFields = array_flip($field_names);

            foreach ($aParticipantData as &$aParticipant)
            {
                $aParticipant=array_intersect_key($aParticipant,$aDestinationFields);

                Tokens_dynamic::sid($iSurveyID);

                $token = new Tokens_dynamic;

                if ($token->insert($aParticipant))
                {
                    $new_token_id = $token->primaryKey;

                    if ($bCreateToken)
                        $token_string = Tokens_dynamic::model()->createToken($new_token_id);
                    else
                        $token_string = '';

                    $aParticipant = array_merge($aParticipant, array(
                    'tid' => $new_token_id,
                    'token' => $token_string,
                    ));
                }
            }

            return $aParticipantData;
        }
        else
            return array('status' => 'No permission');
    }


    /**
     * RPC routing to to return unused Tokens.
     * Returns the unused tokens in an Array.
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @return array
     */
	public function token_return($sSessionKey, $iSurveyID)
	{	
        if ($this->_checkSessionKey($sSessionKey))
        {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');	
				
			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');

			if (hasSurveyPermission($iSurveyID, 'tokens', 'read'))
			{
				$oTokens = Tokens_dynamic::model($iSurveyID)->findAll("completed = 'N'");
				if(count($oTokens)==0)
					return array('status' => 'No unused Tokens found');
				
				foreach ($oTokens as $token)
					{
						$aData[] = $token->attributes['token'];
					}
				return $aData;
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');	        			
	}

   /**
     * RPC routine to return the ids and info  of tokens of a survey 
     * Returns array of ids and info
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @return array
     */
	public function get_token_list($sSessionKey, $iSurveyID)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');

			
			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');

			 
			if (hasSurveyPermission($iSurveyID, 'tokens', 'read'))
			{	
				$oTokens = Tokens_dynamic::model($iSurveyID)->findAll();
				if(count($oTokens)==0)
					return array('status' => 'No Tokens found');
				
				foreach ($oTokens as $token)
					{
						$aData[] = array(
									'tid'=>$token->primarykey,
									'token'=>$token->attributes['token'],
									'participant_info'=>array(
														'firstname'=>$token->attributes['firstname'],
														'lastname'=>$token->attributes['lastname'],														
														'email'=>$token->attributes['email'],
														    ));
					}
				return $aData;					
			}
			else
				return array('status' => 'No permission');  	   
        }
        else
            return array('status' => 'Invalid Session Key');        				
	}

  /**
     * RPC routine to delete multiple token records 
     * Returns the id of the deleted token
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param array $aTokenIDs
     * @return array
     */
	public function delete_tokens($sSessionKey, $iSurveyID, $aTokenIDs)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {
			$iSurveyID = sanitize_int($iSurveyID);

			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
			
			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');
				
			if (hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
			{
				$result=array();
				foreach($aTokenIDs as $iTokenID)
				{
					$tokenidExists = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
					if (!isset($tokenidExists))
						$result[$iTokenID]='Invalid token ID';
					else
					{
					Survey_links::deleteTokenLink(array($iTokenID), $iSurveyID);
					if(Tokens_dynamic::model($iSurveyID)->deleteRecords(array($iTokenID)))
						$result[$iTokenID]='Deleted';
					else
						$result[$iTokenID]='Deletion went wrong';
					}
				}
				return $result;	 
            }
            else
                return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');        		
	}


  /**
     * RPC routine to return settings of a token of a survey 
     * Returns string 
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
	 * @param int $iTokenID
     * @param array $aTokenProperties
     * @return array
     */
	public function get_token_settings($sSessionKey, $iSurveyID, $iTokenID, $aTokenProperties)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
			
			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');
			
			$oToken = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
			if (!isset($oToken))
				return array('status' => 'Error: Invalid tokenid');
							
			if (hasSurveyPermission($iSurveyID, 'tokens', 'read'))
			{		
                $result=array();
                $aBasicDestinationFields=Tokens_dynamic::model()->tableSchema->columnNames;	
                $aTokenProperties=array_intersect($aTokenProperties,$aBasicDestinationFields);     
				$abasic_attrs = $oToken->getAttributes();   
				  
                foreach($aTokenProperties as $sproperty_name )
                {       				
					if (isset($abasic_attrs[$sproperty_name]))
						$result[$sproperty_name]=$abasic_attrs[$sproperty_name];
					else
						$result[$sproperty_name]='Data not available';
				}
				return $result;				
			}
			else
				return array('status' => 'No permission');  	   
        }	
        else
            return array('status' => 'Invalid Session Key');        			
	}

  /**
     * RPC routine to modify settings of a token of a survey 
     * Returns array 
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
	 * @param int $iTokenID
     * @param array|struct $aTokenData
     * @return array
     */
	public function modify_token_settings($sSessionKey, $iSurveyID, $iTokenID, $aTokenData)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {

			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
			
			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');
			
			$oToken = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
			if (!isset($oToken))
				return array('status' => 'Error: Invalid tokenid');
							
			$succeded = array();
			$failed = array();      
			// Remove fields that may not be modified
			unset($aTokenData['tid']);
			
			$aBasicDestinationFields=array_flip(Tokens_dynamic::model()->tableSchema->columnNames);	
            $aTokenData=array_intersect_key($aTokenData,$aBasicDestinationFields);     

			if (hasSurveyPermission($iSurveyID, 'tokens', 'update'))
			{		
               foreach($aTokenData as $sFieldName=>$sValue)
               {
                    if($this->_internal_validate($sFieldName,$sValue))
                    {
						$oToken->$sFieldName=$sValue;
						$succeded[$sFieldName]=$sValue;						
					}
					else
						$failed[$sFieldName]=$sValue;				   				   
			   }
               try
               {
                    $oToken->save(); 
                    $result = array('succeded'=>$succeded,'failed'=>$failed);
                    return $result;
               }
               catch(Exception $e)
               {
                    return array('status' => 'Error');
               }			   		
			}
			else
				return array('status' => 'No permission');  	   
        }	
        else
            return array('status' => 'Invalid Session Key');        			
	}


    /**
     * RPC routine to create an empty group with minimum details
     * Used as a placeholder for importing questions
     * Returns the groupid of the created group
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
	 * @param string $sGroupTitle
	 * @param string $sGroupDescription	 
     * @return string
     * @throws Zend_XmlRpc_Server_Exception
     */
  	public function create_group($sSessionKey, $iSurveyID, $sGroupTitle, $sGroupDescription='')
	{   
		if ($this->_checkSessionKey($sSessionKey))
        {
			if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {		
				$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
				if (!isset($surveyidExists))
					return array('status' => 'Error: Invalid survey ID');
					
				if($surveyidExists['active']=='Y')
					return array('status' => 'Error:Survey is active and not editable');
					
				$group = new Groups;
				$group->sid = $iSurveyID;
				$group->group_name =  $sGroupTitle;
                $group->description = $sGroupDescription;
                $group->group_order = getMaxGroupOrder($iSurveyID);
                $group->language =  Survey::model()->findByPk($iSurveyID)->language;
				if($group->save())
					return $group->gid;
				else
					return array('status' => 'Creation Failed');
			}
			else
				return array('status' => 'No permission');		
		}
        else
            return array('status' => 'Invalid Session Key');		
	} 


    /**
    * RPC routine to import a group - imports lsg,csv
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID the id of the survey that the group will belong
    * @param string $sImportData String containing the BASE 64 encoded data of a lsg,csv
    * @param string $sImportDataType  lsg,csv
    * @param string $sNewGroupName  Optional new name for the group
    * @param string $sNewGroupDescription  Optional new description for the group
    * @return integer iGroupID  - ID of the new group
    */
    public function import_group($sSessionKey, $iSurveyID, $sImportData, $sImportDataType, $sNewGroupName=NULL, $sNewGroupDescription=NULL)
    {
		
        if ($this->_checkSessionKey($sSessionKey))
        { 
			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
				
			if($surveyidExists->getAttribute('active') =='Y')
				return array('status' => 'Error:Survey is aCctive and not editable');	

            if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
                if (!in_array($sImportDataType,array('csv','lsg'))) return array('status' => 'Invalid extension');
				libxml_use_internal_errors(true);
                Yii::app()->loadHelper('admin/import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40).'.'.$sImportDataType;
                file_put_contents($sFullFilePath,base64_decode(chunk_split($sImportData)));

				if (strtolower($sImportDataType)=='csv')
				{
					$aImportResults = CSVImportGroup($sFullFilePath, $iSurveyID);
				}
				elseif ( strtolower($sImportDataType)=='lsg')
				{
					
					$xml = simplexml_load_file($sFullFilePath);
					if(!$xml)
					{
						unlink($sFullFilePath);
						return array('status' => 'Error: Invalid LimeSurvey group structure XML ');
					}
					$aImportResults = XMLImportGroup($sFullFilePath, $iSurveyID);
				}
				else
					return array('status' => 'Invalid extension'); //just for symmetry!

				unlink($sFullFilePath);

				if (isset($aImportResults['fatalerror'])) return array('status' => 'Error: '.$aImportResults['fatalerror']);
                else
                {
					$iNewgid = $aImportResults['newgid'];	
				
					$group = Groups::model()->findByAttributes(array('gid' => $iNewgid));
					$slang=$group['language'];
					if($sNewGroupName!='')
					$group->setAttribute('group_name',$sNewGroupName);
					if($sNewGroupDescription!='')
					$group->setAttribute('description',$sNewGroupDescription);
					try
					{
						$group->save();
					}
					catch(Exception $e)	
					{
						// no need to throw exception
					}
                    return $aImportResults['newgid'];
                }                
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');       
    }


  /**
     * RPC routine to delete a group of a survey 
     * Returns the id of the deleted group
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param int $iGroupID
     * @return int
     * @throws Zend_XmlRpc_Server_Exception
     */
	public function delete_group($sSessionKey, $iSurveyID, $iGroupID)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {
			$iSurveyID = sanitize_int($iSurveyID);
			$iGroupID = sanitize_int($iGroupID);
			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
           		   
			$groupidExists = Groups::model()->findByAttributes(array('gid' => $iGroupID));
			if (!isset($groupidExists))
				return array('status' => 'Error: Invalid group ID');
		   
			if($surveyidExists['active']=='Y')
				return array('status' => 'Error:Survey is active and not editable');

            if (hasSurveyPermission($iSurveyID, 'surveycontent', 'delete'))
            {
				LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
				$iGroupsDeleted = Groups::deleteWithDependency($iGroupID, $iSurveyID);
				
				if ($iGroupsDeleted === 1)
					fixSortOrderGroups($iSurveyID);
					
				LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);

				if ($iGroupsDeleted === 1)
					return $iGroupID;
				else
					return array('status' => 'Group deletion failed');
            }
            else
                return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');		
	}

   /**
     * RPC routine to return the ids and info of groups belonging to survey 
     * Returns array of ids and info
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @return array
     * @throws Zend_XmlRpc_Server_Exception
     */
	public function get_group_list($sSessionKey, $iSurveyID)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
		   
			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{	
				$group_list = Groups::model()->findAllByAttributes(array("sid"=>$iSurveyID)); 		   
				if(count($group_list)==0)
					return array('status' => 'No groups found');
				
				foreach ($group_list as $group)
				{
					$aData[]= array('id'=>$group->primaryKey,'group_name'=>$group->attributes['group_name']);
				}
				return $aData;					
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}


  /**
     * RPC routine to return settings of a group of a survey 
     * Returns array of properties 
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iGroupID
     * @param array  $aGroupSettings
     * @return array
     * @throws Zend_XmlRpc_Server_Exception
     */
	public function get_group_settings($sSessionKey, $iGroupID, $aGroupSettings)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
		   $current_group = Groups::model()->findByAttributes(array('gid' => $iGroupID));
			if (!isset($current_group))
				return array('status' => 'Error: Invalid group ID');
		
			$aBasicDestinationFields=Groups::model()->tableSchema->columnNames;	
			$aGroupSettings=array_intersect($aGroupSettings,$aBasicDestinationFields);
				   
			if (hasSurveyPermission($current_group->sid, 'survey', 'read'))
			{		
                $abasic_attrs = $current_group ->getAttributes();
                foreach($aGroupSettings as $sGroupSetting)
                {
					if (isset($abasic_attrs[$sGroupSetting]))
						$result[$sGroupSetting]=$abasic_attrs[$sGroupSetting];
					else
						$result[$sGroupSetting]='Data not available';	
				}
                return $result;						
			}
			else
				return array('status' => 'No permission');	   
        }
        else
            return array('status' => 'Invalid Session Key');
	}


    /**
    * RPC routine to modify group settings
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iGroupID  - ID of the survey
    * @param array|struct $aGroupData - An array with the particular fieldnames as keys and their values to set on that particular survey
    * @return array of succeeded and failed modifications according to internal validation.
    */
    public function modify_group_settings($sSessionKey, $iGroupID, $aGroupData)
    { 
        if ($this->_checkSessionKey($sSessionKey))
        {               
            $oGroup=Groups::model()->findByAttributes(array('gid' => $iGroupID));
            if (is_null($oGroup))
            {
                return array('status' => 'Error: Invalid group ID');
            }
            if (hasSurveyPermission($oGroup->sid, 'survey', 'update'))
            {
                $succeded = array();
                $failed = array();      
                // Remove fields that may not be modified
                unset($aGroupData['sid']);
                unset($aGroupData['gid']);
                // Remove invalid fields
                $aDestinationFields=array_flip(Groups::model()->tableSchema->columnNames);
                $aGroupData=array_intersect_key($aGroupData,$aDestinationFields);

                foreach($aGroupData as $sFieldName=>$sValue)
                {
					$valid_value = $this->_internal_validate($sFieldName,$sValue);
					
					if ($valid_value === false)
						$failed[$sFieldName]=$sValue;
					else
					{
						//all dependencies this group has 
						$has_dependencies=getGroupDepsForConditions($oGroup->sid,$iGroupID);
						//all dependencies on this group 
						$depented_on = getGroupDepsForConditions($oGroup->sid,"all",$iGroupID,"by-targgid");
						//We do not allow groups with dependencies to change order - that would lead to broken dependencies
						
						if((isset($has_dependencies) || isset($depented_on))  && $sFieldName == 'group_order')
							$failed[$sFieldName]='Group with dependencies - Order cannot be changed';
						else
						{
							$oGroup->setAttribute($sFieldName,$valid_value);
							$succeded[$sFieldName]=$sValue;	
						}					
					}
                }
                try
                {
                    $oGroup->save(); // save the change to database
                    fixSortOrderGroups($oGroup->sid);
                    $result = array('succeded'=>$succeded,'failed'=>$failed);
                    return $result;
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');        
    }


    /**
    * RPC routine to import a question - imports lsq,csv
    *
    * @access public
    * @param string $sSessionKey
    * @param int $iSurveyID the id of the survey that the question will belong
    * @param int $iGroupID the id of the group that the question will belong
    * @param string $sImportData String containing the BASE 64 encoded data of a lsg,csv
    * @param string $sImportDataType  lsq,csv
    * @param string $sMandatory 
    * @param string $sNewQuestionTitle  Optional new title for the question
    * @param string $sNewqQuestion An optional new question
    * @param string $sNewQuestionHelp An optional new question help text
    * @return integer iQuestionID  - ID of the new question
    */
    public function import_question($sSessionKey, $iSurveyID,$iGroupID, $sImportData, $sImportDataType, $sMandatory='N', $sNewQuestionTitle=NULL, $sNewqQuestion=NULL, $sNewQuestionHelp=NULL)
    {
        if ($this->_checkSessionKey($sSessionKey))
        { 
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');
				
			if($oSurvey->getAttribute('active') =='Y')
				return array('status' => 'Error:Survey is Active and not editable');	
				
			$oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
			if (!isset($oGroup))
				return array('status' => 'Error: Invalid group ID');
			
			$gsid = $oGroup['sid'];	
			if($gsid != $iSurveyID)
				return array('status' => 'Error: IMissmatch in surveyid and groupid');
				
            if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
                if (!in_array($sImportDataType,array('csv','lsq'))) return array('status' => 'Invalid extension');
				libxml_use_internal_errors(true);
                Yii::app()->loadHelper('admin/import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40).'.'.$sImportDataType;
                file_put_contents($sFullFilePath,base64_decode(chunk_split($sImportData)));

				if (strtolower($sImportDataType)=='csv')
				{
					$aImportResults = CSVImportQuestion($sFullFilePath, $iSurveyID, $iGroupID);
				}
				elseif ( strtolower($sImportDataType)=='lsq')
				{
				
					$xml = simplexml_load_file($sFullFilePath);
					if(!$xml)
					{
						unlink($sFullFilePath);
						return array('status' => 'Error: Invalid LimeSurvey question structure XML ');
					}
					$aImportResults =  XMLImportQuestion($sFullFilePath, $iSurveyID, $iGroupID);
				}
				else
					return array('status' => 'Really Invalid extension'); //just for symmetry!

				unlink($sFullFilePath);

				if (isset($aImportResults['fatalerror'])) return array('status' => 'Error: '.$aImportResults['fatalerror']);
                else
                {
					fixLanguageConsistency($iSurveyID);
					$iNewqid = $aImportResults['newqid'];	
				
					$oQuestion = Questions::model()->findByAttributes(array('sid' => $iSurveyID, 'gid' => $iGroupID, 'qid' => $iNewqid));
					if($sNewQuestionTitle!=NULL)
						$oQuestion->setAttribute('title',$sNewQuestionTitle);
					if($sNewqQuestion!='')
						$oQuestion->setAttribute('question',$sNewqQuestion);					
					if($sNewQuestionHelp!='')
						$oQuestion->setAttribute('help',$sNewQuestionHelp);					
					if(in_array($sMandatory, array('Y','N')))
						$oQuestion->setAttribute('mandatory',$sMandatory);
					else
						$oQuestion->setAttribute('mandatory','N');	
					
					try
					{
						$oQuestion->save();
					}
					catch(Exception $e)	
					{
						// no need to throw exception
					}
                    return $aImportResults['newqid'];
                }                           
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');       
    }


  /**
     * RPC routine to delete a question of a survey 
     * Returns the id of the deleted question
     *
     * @access public
     * @param string $sSessionKey
     * @param int iQuestionID
     * @return string
     */
	public function delete_question($sSessionKey, $iQuestionID)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {

			$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID));
			if (!isset($oQuestion))
				return array('status' => 'Error: Invalid question ID');
		
			$iSurveyID = $oQuestion['sid'];
			$oSurvey = Survey::model()->findByPk($iSurveyID);

			if($oSurvey['active']=='Y')
				return array('status' => 'Survey is active and not editable');
			$iGroupID=$oQuestion['gid'];	
			
            if (hasSurveyPermission($iSurveyID, 'surveycontent', 'delete'))
            {
				$oCondition = Conditions::model()->findAllByAttributes(array('cqid' => $iQuestionID));
				if(count($oCondition)>0)
					return array('status' => 'Cannot delete Question. Others rely on this question');
				
				LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$iQuestionID);
				
				try
				{
					Conditions::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Question_attributes::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Answers::model()->deleteAllByAttributes(array('qid' => $iQuestionID));

					$criteria = new CDbCriteria;
					$criteria->addCondition('qid = :qid or parent_qid = :qid');
					$criteria->params[':qid'] = $iQuestionID;
					Questions::model()->deleteAll($criteria);

					Defaultvalues::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Quota_members::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Questions::updateSortOrder($iGroupID, $iSurveyID);
				
                return $iQuestionID;
				}
				catch(Exception $e)
                {
                    return array('status' => 'Error');
                }
	
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');         		
	}



   /**
     * RPC routine to return the ids and info  of questions of a survey/group 
     * Returns array of ids and info
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param int $iGroupID
     * @return array
     */
	public function get_question_list($sSessionKey, $iSurveyID, $iGroupID=NULL)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);		   
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');
  
			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{	
				if($iGroupID!=NULL)
				{
					$oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
					$gsid = $oGroup['sid'];
					
					if($gsid != $iSurveyID)
						return array('status' => 'Error: IMissmatch in surveyid and groupid');	
					else
						$aQuestionList = Questions::model()->findAllByAttributes(array("sid"=>$iSurveyID, "gid"=>$iGroupID,"parent_qid"=>"0"));
				}
				else
					$aQuestionList = Questions::model()->findAllByAttributes(array("sid"=>$iSurveyID,"parent_qid"=>"0"));
	   
				if(count($aQuestionList)==0)
					return array('status' => 'No questions found');
				
				foreach ($aQuestionList as $question)
				{
					$aData[]= array('id'=>$question->primaryKey,'type'=>$question->attributes['type'], 'question'=>$question->attributes['question']);
				}
				return $aData;					
			}
			else
				return array('status' => 'No permission'); 	   
        }
        else
			return array('status' => 'Invalid session key');          				
	}


  /**
     * RPC routine to return  properties of a question of a survey 
     * Returns string 
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iQuestionID
     * @param array $aQuestionSettings
     * @return array
     */
	public function get_question_settings($sSessionKey, $iQuestionID, $aQuestionSettings)
	{
	
       if ($this->_checkSessionKey($sSessionKey))
       {
			$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID));
			if (!isset($oQuestion))
				return array('status' => 'Error: Invalid questionid', 22);
				
			$aBasicDestinationFields=Questions::model()->tableSchema->columnNames;
			array_push($aBasicDestinationFields,'available_answers')	;
			$aQuestionSettings=array_intersect($aQuestionSettings,$aBasicDestinationFields);

			if (empty($aQuestionSettings))	
			   	return array('status' => 'No valid Data');   
			   	
			if (hasSurveyPermission($oQuestion->sid, 'survey', 'read'))
			{		
                $abasic_attrs = $oQuestion->getAttributes();  
                $result=array();
                foreach ($aQuestionSettings as $sproperty_name )
                {
					if ($sproperty_name == 'available_answers')
					{
						$subgroups =  Questions::model()->findAllByAttributes(array('parent_qid' => $iQuestionID),array('order'=>'title') );
						if (count($subgroups)>0)
						{
							foreach($subgroups as $subgroup)
								$aData[$subgroup['title']]= $subgroup['question'];
							
							$result['available_answers']=$aData;
						}
						else
							$result['available_answers']='No available answers';
					}
					else
					{					
					if (isset($abasic_attrs[$sproperty_name]))
						$result[$sproperty_name]=$abasic_attrs[$sproperty_name];
					else
						$result[$sproperty_name]='Data not available';
					
					}
				}
                return $result;				
			}
			else
				return array('status' => 'No permission');  	    
        }
        else
			return array('status' => 'Invalid session key');       				
	}

    /**
    * RPC routine to modify group settings
    *
    * @access public
    * @param string $sSessionKey
    * @param integer $iQuestionID  - ID of the question
    * @param array|struct $aQuestionData - An array with the particular fieldnames as keys and their values to set on that particular question
    * @return array of succeeded and failed modifications according to internal validation.
    */
    public function modify_question_settings($sSessionKey, $iQuestionID, $aQuestionData)
    { 
        if ($this->_checkSessionKey($sSessionKey))
        {               
            $oQuestion=Questions::model()->findByAttributes(array('qid' => $iQuestionID));
            if (is_null($oQuestion))
                return array('status' => 'Error: Invalid group ID');

            if (hasSurveyPermission($oQuestion->sid, 'survey', 'update'))
            {
                $succeded = array();
                $failed = array();      
                // Remove fields that may not be modified
                unset($aQuestionData['qid']);
                unset($aQuestionData['gid']);
                unset($aQuestionData['sid']);                
                unset($aQuestionData['parent_qid']);
                unset($aQuestionData['language']);
                unset($aQuestionData['type']);                
                // Remove invalid fields
                $aDestinationFields=array_flip(Questions::model()->tableSchema->columnNames);
                $aQuestionData=array_intersect_key($aQuestionData,$aDestinationFields);
				
				if (empty($aQuestionData))	
					return array('status' => 'No valid Data');   

                foreach($aQuestionData as $sFieldName=>$sValue)
                {
					$valid_value = $this->_internal_validate($sFieldName,$sValue);
					
					if ($valid_value === false)
						$failed[$sFieldName]=$sValue;
					else
					{
						//all the dependencies that this question has to other questions
						$dependencies=getQuestDepsForConditions($oQuestion->sid,$oQuestion->gid,$iQuestionID);
						//all dependencies by other questions to this question
						$is_criteria_question=getQuestDepsForConditions($oQuestion->sid,$oQuestion->gid,"all",$iQuestionID,"by-targqid");
						//We do not allow questions with dependencies in the same group to change order - that would lead to broken dependencies						
						
						if((isset($dependencies) || isset($is_criteria_question))  && $sFieldName == 'question_order')
							$failed[$sFieldName]='Questions with dependencies - Order cannot be changed';
						else
						{
							$oQuestion->setAttribute($sFieldName,$valid_value);
							$succeded[$sFieldName]=$sValue;	
						}					
					}
                }
                try
                {
                    $oQuestion->save(); // save the change to database
                    fixSortOrderQuestions($oQuestion->gid, $oQuestion->sid);
                    $result = array('succeded'=>$succeded,'failed'=>$failed);
                    return $result;
                }
                catch(Exception $e)
                {
                    return array('status' => 'Error');
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');        
    }



    /**
    * Tries to login with username and password
    *
    * @access protected
    * @param string $sUsername
    * @param mixed $sPassword
    * @return bool
    */
    protected function _doLogin($sUsername, $sPassword)
    {
        if (Failed_login_attempts::model()->isLockedOut())
            return false;

        $identity = new UserIdentity(sanitize_user($sUsername), $sPassword);

        if (!$identity->authenticate())
        {
            Failed_login_attempts::model()->addAttempt();
            return false;
        }
        else
            return true;
    }

    /**
    * Fills the session with necessary user info on the fly
    *
    * @access protected
    * @param string $sUsername
    * @return bool
    */
    protected function _jumpStartSession($username)
    {
        $aUserData = User::model()->findByAttributes(array('users_name' => $username))->attributes;

        $session = array(
        'loginID' => intval($aUserData['uid']),
        'user' => $aUserData['users_name'],
        'full_name' => $aUserData['full_name'],
        'htmleditormode' => $aUserData['htmleditormode'],
        'templateeditormode' => $aUserData['templateeditormode'],
        'questionselectormode' => $aUserData['questionselectormode'],
        'dateformat' => $aUserData['dateformat'],
        'adminlang' => 'en'
        );
        foreach ($session as $k => $v)
            Yii::app()->session[$k] = $v;
        Yii::app()->user->setId($aUserData['uid']);

        $this->controller->_GetSessionUserRights($aUserData['uid']);
        return true;
    }

    /**
    * This function checks if the XML-RPC session key is valid. If yes returns true, otherwise false and sends an error message with error code 1
    *
    * @access protected
    * @param string $sSessionKey
    * @return bool
    * @throws Zend_XmlRpc_Server_Exception
    */
    protected function _checkSessionKey($sSessionKey)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'expire < ' . time();
        Sessions::model()->deleteAll($criteria);
        $oResult = Sessions::model()->findByPk($sSessionKey);

        if (is_null($oResult))
            return false;
        else
        {
            $this->_jumpStartSession($oResult->data);
            return true;
        }
    }
    
    /**
     * This function validates parameters to be inserted in survey model
     * 
     * @access protected
     * @param string $sparam_name
     * @param string $sparam_value
     * @return bool|string
     * @throws Zend_XmlRpc_Server_Exception
     */
    protected function _internal_validate($sparam_name, $sparam_value)
    {   	
		$date_pattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
		$validation_categories = array(
								'active'=>'char',
								'anonymized'=>'char',
								'savetimings'=>'char',
								'datestamp'=>'char',
								'ipaddr'=>'char',
								'refurl'=>'char',
								'usecookie'=>'char',
								'allowregister'=>'char',
								'allowsave'=>'char',
								'autoredirect'=>'char',
								'allowprev'=>'char',
								'printanswers'=>'char',
								'publicstatistics'=>'char',
								'publicgraphs'=>'char',
								'listpublic'=>'char',
								'htmlemail'=>'char',
								'sendconfirmation'=>'char',
								'tokenanswerspersistence'=>'char',
								'assessments'=>'char',
								'usecaptcha'=>'captcha_format',
								'usetokens'=>'char',
								'showxquestions'=>'char',
								'showgroupinfo'=>'group_format',
								'shownoanswer'=>'char',
								'showqnumcode'=>'gnum_format',
								'showwelcome'=>'char',
								'showprogress'=>'char',
								'allowjumps'=>'char',
								'nokeyboard'=>'char',
								'alloweditaftercompletion'=>'char',
								'googleanalyticsstyle'=>'ga_format',
								'bounceprocessing'=>'char',
								'autonumber_start'=>'int',
								'tokenlength'=>'int',
								'bouncetime'=>'int',
								'navigationdelay'=>'int',
								'expires'=>'date',
								'startdate'=>'date',
								'datecreated'=>'date',
								'adminemail'=>'email',
								'bounce_email'=>'email',
								'surveyls_dateformat'=>'dateformat',
								'surveyls_numberformat'=>'numberformat',
								'template'=>'tmpl',
								'format'=>'gsa_format',
								//group  parameters
								'group_order'=>'int',
								//question_parameters
								'other'=>'char',
								'mandatory'=>'char',
								'question_order'=>'int',
								'scale_id'=>'int',
								'same_default'=>'int',
								//token parameters
								'email'=>'email',
								'remindercount'=>'int',
								'remindersent'=>'int',
								'usesleft'=>'int',
								'validfrom'=>'date',
								'validuntil'=>'date',
								'mpid'=>'int',
								'blacklisted'=>'char',
								'sent'=>'char',
								'completed'=>'char'
								);
		
		if (array_key_exists($sparam_name, $validation_categories))
		{
			switch($validation_categories[$sparam_name])
			{
			case 'char':
				if(in_array($sparam_value, array('Y','N')))
					return $sparam_value;
				else
					return false;
				break;
			
			case 'int':
				return filter_var($sparam_value, FILTER_VALIDATE_INT, array("options" => array("min_range"=>1, "max_range"=>999999999)));
				break;
			
			case 'date':
				return filter_var($sparam_value, FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>$date_pattern)));
				break;

			case 'email':
				return filter_var($sparam_value, FILTER_VALIDATE_EMAIL);
				break;
							
			case 'dateformat':
				return filter_var($sparam_value, FILTER_VALIDATE_INT, array("options" => array("min_range"=>1, "max_range"=>12)));
				break;
				
			case 'numberformat':
				return filter_var($sparam_value, FILTER_VALIDATE_INT, array("options" => array("min_range"=>0, "max_range"=>1)));
				break;
			case 'tmpl':
				if(array_key_exists($sparam_value,getTemplateList()))
					return $sparam_value;
				else
					return false;
				break;
			case 'gsa_format':
				if(in_array($sparam_value, array('G','S','A')))
					return $sparam_value;
				else
					return false;
				break;	
			case 'captcha_format':
				if(in_array($sparam_value, array('A','B','C','D','X','R','S','N')))
					return $sparam_value;
				else
					return false;
				break;
			case 'group_format':
				if(in_array($sparam_value, array('B','N','D','X')))
					return $sparam_value;
				else
					return false;
				break;	
			case 'gnum_format':
				if(in_array($sparam_value, array('B','N','C','X')))
					return $sparam_value;
				else
					return false;
				break;	
			case 'ga_format':
				return filter_var($sparam_value, FILTER_VALIDATE_INT, array("options" => array("min_range"=>0, "max_range"=>2)));
				break;																
			default:
				return $sparam_value;
	
			}
		}
		else
			return $sparam_value;
	}     
    
}
