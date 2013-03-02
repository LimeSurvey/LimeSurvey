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
        $oHandler=new remotecontrol_handle($this->controller);
        $RPCType=Yii::app()->getConfig("RPCInterface");

        if (Yii::app()->getRequest()->isPostRequest) {
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
                $this->xmlrpc->setClass($oHandler);
                echo $this->xmlrpc->handle();
            }
            elseif($RPCType=='json')
            {
                Yii::app()->loadLibrary('jsonRPCServer');

                jsonRPCServer::handle($oHandler);
            }
            exit;
        } else {
            // Disabled output of API methods for now
            if (1 == 2 && in_array($RPCType, array('xml', 'json'))) {
                $reflector = new ReflectionObject($oHandler);
                foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    /* @var $method ReflectionMethod */
                    if (substr($method->getName(),0,1) !== '_') {
                        $list[$method->getName()] = array(
                            'description' => str_replace(array("\r", "\r\n", "\n"), "<br/>", $method->getDocComment()),
                            'parameters'  => $method->getParameters()
                        );
                    }
                }
                ksort($list);
                $aData['method'] = $RPCType;
                $aData['list'] = $list;
                $aData['display']['menu_bars'] = false; // Hide normal menu bar
                $this->_renderWrappedTemplate('remotecontrol', array('index_view'), $aData);
            }
        }
    }

    /**
    * Simple procedure to test most RPC functions
    *
    */
    public function test()
    {
        $RPCType=Yii::app()->getConfig("RPCInterface");
        $serverUrl = Yii::app()->getBaseUrl(true).'/'.dirname(Yii::app()->request->getPathInfo());
        $sFileToImport=dirname(Yii::app()->basePath).DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'demosurveys'.DIRECTORY_SEPARATOR.'limesurvey2_sample_survey_english.lss';

        if ($RPCType == 'xml') {
            require_once('Zend/XmlRpc/Client.php');
            $client = new Zend_XmlRpc_Client($serverUrl);
        } elseif ($RPCType == 'json') {
            Yii::app()->loadLibrary('jsonRPCClient');
            $client = new jsonRPCClient($serverUrl);
        }

        $sSessionKey= $client->call('get_session_key', array('admin','password'));
        if (is_array($sSessionKey)) {echo $sSessionKey['status']; die();}
        else
        {
            echo 'Retrieved session key'.'<br>';
        }

        $sLSSData=base64_encode(file_get_contents($sFileToImport));
        $iSurveyID=$client->call('import_survey', array($sSessionKey, $sLSSData, 'lss','Test import by JSON_RPC',1000));
        echo 'Created new survey SID:'.$iSurveyID.'<br>';

        $aResult=$client->call('activate_survey', array($sSessionKey, $iSurveyID));
        if ($aResult['status']=='OK')
        {
            echo 'Survey '.$iSurveyID.' successfully activated.<br>';
        }
        $aResult=$client->call('activate_tokens', array($sSessionKey, $iSurveyID,array(1,2)));
        if ($aResult['status']=='OK')
        {
            echo 'Tokens for Survey ID '.$iSurveyID.' successfully activated.<br>';
        }
        $aResult=$client->call('set_survey_properties', array($sSessionKey, $iSurveyID,array('faxto'=>'0800-LIMESURVEY')));
        if (!array_key_exists('status', $aResult))
        {
            echo 'Modified survey settings for survey '.$iSurveyID.'<br>';
        }
        $aResult=$client->call('add_language', array($sSessionKey, $iSurveyID,'ar'));
        if ($aResult['status']=='OK')
        {
            echo 'Added Arabian as additional language'.'<br>';
        }
        $aResult=$client->call('set_language_properties', array($sSessionKey, $iSurveyID,array('surveyls_welcometext'=>'An Arabian welcome text!'),'ar'));
        if ($aResult['status']=='OK')
        {
            echo 'Modified survey locale setting welcometext for Arabian in survey ID '.$iSurveyID.'<br>';
        }

        $aResult=$client->call('delete_language', array($sSessionKey, $iSurveyID,'ar'));
        if ($aResult['status']=='OK')
        {
            echo 'Removed Arabian as additional language'.'<br>';
        }

        //Very simple example to export responses as Excel file
        //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'xls'));
        //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'pdf'));
        //$aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'doc'));
        $aResult=$client->call('export_responses', array($sSessionKey,$iSurveyID,'csv'));
        //file_put_contents('test.xls',base64_decode(chunk_split($aResult)));

        $aResult=$client->call('delete_survey', array($sSessionKey, $iSurveyID));
        echo 'Deleted survey SID:'.$iSurveyID.'-'.$aResult['status'].'<br>';

        // Release the session key - close the session
        $Result= $client->call('release_session_key', array($sSessionKey));
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
     * RPC routine to create a session key.
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
     * RPC Routine to get settings.
     *
     * @access public
     * @param string $sSessionKey Auth Credentials
     * @param string $sSetttingName Name of the setting to get
     * @return string The requested value
     */
   public function get_site_settings($sSessionKey,$sSetttingName)
    {
       if ($this->_checkSessionKey($sSessionKey))
       {
		   if( Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		   {
			   if (Yii::app()->getConfig($sSetttingName) !== false)
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


	/* Survey specific functions */

	/**
     * RPC Routine to add an empty survey with minimum details.
     * Used as a placeholder for importing groups and/or questions.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The wish id of the Survey to add
     * @param string $sSurveyTitle Title of the new Survey
     * @param string $sSurveyLanguage	Default language of the Survey
     * @param string $sformat Question appearance format
     * @return array|string|int
     */
	public function add_survey($sSessionKey, $iSurveyID, $sSurveyTitle, $sSurveyLanguage, $sformat = 'G')
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

				if (!is_null($iSurveyID))
					$aInsertData['wishSID'] = $iSurveyID;

				try
				{
					$iNewSurveyid = Survey::model()->insertNewSurvey($aInsertData);
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
						);

					$langsettings = new Surveys_languagesettings;
					$langsettings->insertNewSurvey($aInsertData);
					Survey_permissions::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'], $iNewSurveyid);

					return (int)$iNewSurveyid;
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
     * RPC Routine to delete a survey.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the Survey to be deleted
     * @return array Returns Status
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
     * RPC Routine to import a survey - imports lss,csv,xls or survey zip archive.
     *
     * @access public
     * @param string $sSessionKey Auth Credentials
     * @param string $sImportData String containing the BASE 64 encoded data of a lss,csv,xls or survey zip archive
     * @param string $sImportDataType  lss,csv,xls or zip
     * @param string $sNewSurveyName The optional new name of the survey
     * @param integer $DestSurveyID This is the new ID of the survey - if already used a random one will be taken instead
     * @return array|integer iSurveyID  - ID of the new survey
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
                    return (int)$aImportResults['newsid'];
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
    }

    /**
     * RPC Routine to get survey properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the Survey to be checked
     * @param array $aSurveySettings The properties to get
     * @return array
     */
    public function get_survey_properties($sSessionKey,$iSurveyID, $aSurveySettings)
     {
         Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey))
        {
             $oSurvey = Survey::model()->findByPk($iSurveyID);
             if (!isset($oSurvey))
             {
                 return array('status' => 'Error: Invalid survey ID');
             }
             if (hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
                 {
                     $aBasicDestinationFields=Survey::model()->tableSchema->columnNames;
                     $aSurveySettings=array_intersect($aSurveySettings,$aBasicDestinationFields);

                     if (empty($aSurveySettings))
                         return array('status' => 'No valid Data');
                     $aResult = array();
                     foreach($aSurveySettings as $sPropertyName)
                     {
                         $aResult[$sPropertyName]=$oSurvey->$sPropertyName;
                     }
                     return $aResult;
                 }
             else
                 return array('status' => 'No permission');
         }
         else
             return array('status' => 'Invalid Session key');
     }

    /**
     * RPC Routine to set survey properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID  - ID of the survey
     * @param array|struct $aSurveyData - An array with the particular fieldnames as keys and their values to set on that particular survey
     * @return array Of succeeded and failed nodifications according to internal validation.
     */
    public function set_survey_properties($sSessionKey, $iSurveyID, $aSurveyData)
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
                unset($aSurveyData['sid']);
                unset($aSurveyData['owner_id']);
                unset($aSurveyData['active']);
                unset($aSurveyData['language']);
                unset($aSurveyData['additional_languages']);
                // Remove invalid fields
                $aDestinationFields=array_flip(Survey::model()->tableSchema->columnNames);
                $aSurveyData=array_intersect_key($aSurveyData,$aDestinationFields);
                $oSurvey=Survey::model()->findByPk($iSurveyID);
                $aBasicAttributes = $oSurvey->getAttributes();
                $aResult = array();

                if ($oSurvey->active=='Y')
                {
                    // remove all fields that may not be changed when a survey is active
                    unset($aSurveyData['anonymized']);
                    unset($aSurveyData['datestamp']);
                    unset($aSurveyData['savetimings']);
                    unset($aSurveyData['ipaddr']);
                    unset($aSurveyData['refurl']);
                }

				if (empty($aSurveyData))
					return array('status' => 'No valid Data');

                foreach($aSurveyData as $sFieldName=>$sValue)
                {
					$oSurvey->$sFieldName=$sValue;
					try
					{
						$bSaveResult=$oSurvey->save(); // save the change to database
						//unset the value if it fails, so as to prevent future fails
						$aResult[$sFieldName]=$bSaveResult;
						if (!$bSaveResult)
							$oSurvey->$sFieldName=$aBasicAttributes[$sFieldName];
					}
					catch(Exception $e)
					{
						//unset the value that caused the exception
						$oSurvey->$sFieldName=$aBasicAttributes[$sFieldName];
					}
                }
                return $aResult;
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');
    }

    /**
     * RPC Routine to list the ids and info of surveys belonging to a user.
     * Returns array of ids and info.
     * If user is admin he can get surveys of every user (parameter sUser) or all surveys (sUser=null)
     * Else only the syrveys belonging to the user requesting will be shown.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param string $sUser Optional username to get list of surveys
     * @return array The list of surveys
     */
	public function list_surveys($sSessionKey, $sUser=NULL)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
		   $sCurrentUser =  Yii::app()->session['user'];

		   if( Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
		   {
				if ($sUser == null)
					$aUserSurveys = Survey::model()->findAll(); //list all surveys
				else
				{
				   $aUserData = User::model()->findByAttributes(array('users_name' => $sUser));
				   if (!isset($aUserData))
						return array('status' => 'Invalid user');
					else
						$aUserSurveys = Survey::model()->findAllByAttributes(array("owner_id"=>$aUserData->attributes['uid']));
				}
			}
			else
			{
				if (($sCurrentUser == $sUser) || ($sUser == null) )
				{
					$sUid =  User::model()->findByAttributes(array('users_name' => $sCurrentUser))->uid;
					$aUserSurveys = Survey::model()->findAllByAttributes(array("owner_id"=>$sUid));
				}
				else
					return array('status' => 'No permission');
			}

		   if(count($aUserSurveys)==0)
				return array('status' => 'No surveys found');

			foreach ($aUserSurveys as $oSurvey)
				{
				$oSurveyLanguageSettings = Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $oSurvey->primaryKey, 'surveyls_language' => $oSurvey->language));
				if (!isset($oSurveyLanguageSettings))
					$aSurveyTitle = '';
				else
					$aSurveyTitle = $oSurveyLanguageSettings->attributes['surveyls_title'];
				$aData[]= array('sid'=>$oSurvey->primaryKey,'surveyls_title'=>$aSurveyTitle,'startdate'=>$oSurvey->attributes['startdate'],'expires'=>$oSurvey->attributes['expires'],'active'=>$oSurvey->attributes['active']);
				}
			return $aData;
        }
        else
			return array('status' => 'Invalid session key');
	}



    /**
     * RPC Routine that launches a newly created survey.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the survey to be activated
     * @return array The result of the activation
     */
    public function activate_survey($sSessionKey, $iSurveyID)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oSurvey=Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey))
                return array('status' => 'Error: Invalid survey ID');

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
     * RPC routine to export statistics of a survey to a user.
     * Returns string - base64 encoding of the statistics.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey
     * @param string $docType Type of documents the exported statistics should be
     * @param string $sLanguage Optional language of the survey to use
     * @param string $graph Create graph option
     * @param int|array $groupIDs An OPTIONAL array (ot a single int) containing the groups we choose to generate statistics from
     * @return string Base64 encoded string with the statistics file
     */
    public function export_statistics($sSessionKey, $iSurveyID,  $docType='pdf', $sLanguage=null, $graph='0', $groupIDs=null)
    {
		Yii::app()->loadHelper('admin/statistics');

		$tempdir = Yii::app()->getConfig("tempdir");
		if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');

		$oSurvey = Survey::model()->findByPk($iSurveyID);
		if (!isset($oSurvey))
			return array('status' => 'Error: Invalid survey ID');;

		if(Survey::model()->findByPk($iSurveyID)->owner_id != $_SESSION['loginID'])
			return array('status' => 'Error: No Permission');
		$aAdditionalLanguages = array_filter(explode(" ", $oSurvey->additional_languages));

		if (is_null($sLanguage)|| !in_array($sLanguage,$aAdditionalLanguages))
			$sLanguage = $oSurvey->language;

		$oAllQuestions =Questions::model()->getQuestionList($iSurveyID, $sLanguage);
       	if (!isset($oAllQuestions))
				return array('status' => 'No available data');
				
        if($groupIDs!=null)
        {
            if(is_int($groupIDs))
                    $groupIDs = array($groupIDs);
                
            if(is_array($groupIDs)) 
            {   
                //check that every value of the array belongs to the survey defined
                $aGroups = Groups::model()->findAllByAttributes(array('sid' => $iSurveyID));

                foreach( $aGroups as $group)
                    $validGroups[] = $group['gid'];

                $groupIDs=array_intersect($groupIDs,$validGroups);
                
                if (empty($groupIDs))
                    return array('status' => 'Error: Invalid group ID');
                                     
               foreach($oAllQuestions as $key => $aQuestion)  
                 {
					 if(!in_array($aQuestion['gid'],$groupIDs))
						unset($oAllQuestions[$key]);	 
				 }      
            }
            else
                return array('status' => 'Error: Invalid group ID');
		}
			
       	if (!isset($oAllQuestions))
				return array('status' => 'No available data');
				
		usort($oAllQuestions, 'groupOrderThenQuestionOrder');     
        
        $aSummary = createCompleteSGQA($iSurveyID,$oAllQuestions,$sLanguage);

        $helper = new statistics_helper();
		switch ($docType)
		{
			case 'pdf':
				$sTempFile = $helper->generate_statistics($iSurveyID,$aSummary,$aSummary,$graph,$docType,'F',$sLanguage);
				$sResult = file_get_contents($sTempFile);
				unlink($sTempFile);
				break;
			case 'xls':
				$sTempFile = $helper->generate_statistics($iSurveyID,$aSummary,$aSummary,'0',$docType, 'F',$sLanguage);
				$sResult = file_get_contents($sTempFile);
				unlink($sTempFile);
				break;
			case 'html':
				$sResult = $helper->generate_statistics($iSurveyID,$aSummary,$aSummary,'0',$docType, 'DD',$sLanguage);
				break;
		}

		return base64_encode($sResult);

	}

    /**
     * RPC routine to get survey summary, regarding token usage and survey participation.
     * Returns the requested value as string.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey to get summary
     * @param string $sStatName Name of the sumamry option
     * @return string The requested value
     */
   public function get_summary($sSessionKey,$iSurveyID, $sStatName)
    {
       $aPermittedStats = array();
       if ($this->_checkSessionKey($sSessionKey))
       {
			$aPermittedTokenStats = array('token_count',
									'token_invalid',
									'token_sent',
									'token_opted_out',
									'token_completed'
									);
			$aPermittedSurveyStats  = array('completed_responses',
									'incomplete_responses',
									'full_responses'
									);
			$aPermittedStats = array_merge($aPermittedSurveyStats, $aPermittedTokenStats);
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Invalid surveyid');

			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{
				if(in_array($sStatName, $aPermittedTokenStats))
				{
					if (tableExists('{{tokens_' . $iSurveyID . '}}'))
						$summary = Tokens_dynamic::model($iSurveyID)->summary();
					else
						return array('status' => 'No available data');
				}

				if(in_array($sStatName, $aPermittedSurveyStats) && !tableExists('{{survey_' . $iSurveyID . '}}'))
					return array('status' => 'No available data');

				if (!in_array($sStatName, $aPermittedStats))
					return array('status' => 'No such property');


				switch($sStatName)
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

	/*Survey language specific functions */

    /**
     * RPC Routine to add a survey language.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the survey where a token table will be created for
     * @param string $sLanguage  A valid language shortcut to add to the current survey. If the language already exists no error will be given.
     * @return array Status=>OK when successfull, otherwise the error description
     */
    public function add_language($sSessionKey, $iSurveyID, $sLanguage)
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
     * RPC Routine to delete a survey language.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the survey where a token table will be created for
     * @param string $sLanguage  A valid language shortcut to delete from the current survey. If the language does not exist in that survey no error will be given.
     * @return array Status=>OK when successfull, otherwise the error description
     */
    public function delete_language($sSessionKey, $iSurveyID, $sLanguage)
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
     * RPC Routine to get survey language properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Dd of the Survey
     * @param array $aSurveyLocaleSettings Properties to get
     * @param string $sLang Language to use
     * @return array The requested values
     */
   public function get_language_properties($sSessionKey,$iSurveyID, $aSurveyLocaleSettings, $sLang=NULL)
    {
		Yii::app()->loadHelper("surveytranslator");
       if ($this->_checkSessionKey($sSessionKey))
       {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
			{
				return array('status' => 'Error: Invalid survey ID');
			}
			if (hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
				{
					$aBasicDestinationFields=Surveys_languagesettings::model()->tableSchema->columnNames;
					$aSurveyLocaleSettings=array_intersect($aSurveyLocaleSettings,$aBasicDestinationFields);

					if ($sLang == NULL || !array_key_exists($sLang,getLanguageDataRestricted()))
						$sLang = $oSurvey->language;


					$oSurveyLocale=Surveys_languagesettings::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLang));
					$aResult = array();

					if (empty($aSurveyLocaleSettings))
					return array('status' => 'No valid Data');

					foreach($aSurveyLocaleSettings as $sPropertyName)
					{
							$aResult[$sPropertyName]=$oSurveyLocale->$sPropertyName;
						//$aResult[$sPropertyName]=$aLangAttributes[$sPropertyName];
					}
					return $aResult;
				}
			else
				return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');
    }

    /**
     * RPC Routine to set survey language properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID  - ID of the survey
     * @param array|struct $aSurveyLocaleData - An array with the particular fieldnames as keys and their values to set on that particular survey
     * @param string $sLanguage - Optional - Language to update  - if not give the base language of the particular survey is used
     * @return array Status=>OK, when save successful otherwise error text.
     */
    public function set_language_properties($sSessionKey, $iSurveyID, $aSurveyLocaleData, $sLanguage=NULL)
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

                $aLangAttributes = $oSurveyLocale->getAttributes();
                $aResult = array();

                if (empty($aSurveyLocaleData))
					return array('status' => 'No valid Data');

                foreach($aSurveyLocaleData as $sFieldName=>$sValue)
                {
					$oSurveyLocale->$sFieldName=$sValue;
					try
					{
						// save the change to database - Every single change alone - to allow for validation to work
						$bSaveResult=$oSurveyLocale->save();
						$aResult[$sFieldName]=$bSaveResult;
						//unset failed values
						if (!$bSaveResult)
							$oSurveyLocale->$sFieldName=$aLangAttributes[$sFieldName];
					}
					catch(Exception $e)
					{
						$oSurveyLocale->$sFieldName=$aLangAttributes[$sFieldName];
					}
                }
                $aResult['status'] = 'OK';
                return $aResult;
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');
    }

	/* Group specific functions */

    /**
     * RPC Routine to add an empty group with minimum details.
     * Used as a placeholder for importing questions.
     * Returns the groupid of the created group.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Dd of the Survey to add the group
     * @param string $sGroupTitle Name of the group
     * @param string $sGroupDescription	 Optional description of the group
     * @return array|int The id of the new group - Or status
     */
  	public function add_group($sSessionKey, $iSurveyID, $sGroupTitle, $sGroupDescription='')
	{
		if ($this->_checkSessionKey($sSessionKey))
        {
			if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
				$oSurvey = Survey::model()->findByPk($iSurveyID);
				if (!isset($oSurvey))
					return array('status' => 'Error: Invalid survey ID');

				if($oSurvey['active']=='Y')
					return array('status' => 'Error:Survey is active and not editable');

				$oGroup = new Groups;
				$oGroup->sid = $iSurveyID;
				$oGroup->group_name =  $sGroupTitle;
                $oGroup->description = $sGroupDescription;
                $oGroup->group_order = getMaxGroupOrder($iSurveyID);
                $oGroup->language =  Survey::model()->findByPk($iSurveyID)->language;
				if($oGroup->save())
					return (int)$oGroup->gid;
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
     * RPC Routine to delete a group of a survey .
     * Returns the id of the deleted group.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the survey that the group belongs
     * @param int $iGroupID Id of the group to delete
     * @return array|int The id of the deleted group or status
     */
	public function delete_group($sSessionKey, $iSurveyID, $iGroupID)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {
			$iSurveyID = sanitize_int($iSurveyID);
			$iGroupID = sanitize_int($iGroupID);
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

            if (hasSurveyPermission($iSurveyID, 'surveycontent', 'delete'))
            {
				$oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
				if (!isset($oGroup))
					return array('status' => 'Error: Invalid group ID');

				if($oSurvey['active']=='Y')
					return array('status' => 'Error:Survey is active and not editable');

				$depented_on = getGroupDepsForConditions($oGroup->sid,"all",$iGroupID,"by-targgid");
				if(isset($depented_on))
					return array('status' => 'Group with depencdencies - deletion not allowed');

				$iGroupsDeleted = Groups::deleteWithDependency($iGroupID, $iSurveyID);

				if ($iGroupsDeleted === 1)
				{
					fixSortOrderGroups($iSurveyID);
					return (int)$iGroupID;
				}
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
     * RPC Routine to import a group - imports lsg,csv
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the survey that the group will belong
     * @param string $sImportData String containing the BASE 64 encoded data of a lsg,csv
     * @param string $sImportDataType  lsg,csv
     * @param string $sNewGroupName  Optional new name for the group
     * @param string $sNewGroupDescription  Optional new description for the group
     * @return array|integer iGroupID  - ID of the new group or status
     */
    public function import_group($sSessionKey, $iSurveyID, $sImportData, $sImportDataType, $sNewGroupName=NULL, $sNewGroupDescription=NULL)
    {

        if ($this->_checkSessionKey($sSessionKey))
        {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

            if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
				if($oSurvey->getAttribute('active') =='Y')
					return array('status' => 'Error:Survey is active and not editable');

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

					$oGroup = Groups::model()->findByAttributes(array('gid' => $iNewgid));
					$slang=$oGroup['language'];
					if($sNewGroupName!='')
					$oGroup->setAttribute('group_name',$sNewGroupName);
					if($sNewGroupDescription!='')
					$oGroup->setAttribute('description',$sNewGroupDescription);
					try
					{
						$oGroup->save();
					}
					catch(Exception $e)
					{
						// no need to throw exception
					}
                    return (int)$aImportResults['newgid'];
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
    }


    /**
     * RPC Routine to return properties of a group of a survey .
     * Returns array of properties
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iGroupID Id of the group to get properties
     * @param array  $aGroupSettings The properties to get
     * @return array The requested values
     */
	public function get_group_properties($sSessionKey, $iGroupID, $aGroupSettings)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
		   $oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
			if (!isset($oGroup))
				return array('status' => 'Error: Invalid group ID');

			if (hasSurveyPermission($oGroup->sid, 'survey', 'read'))
			{
				$aBasicDestinationFields=Groups::model()->tableSchema->columnNames;
				$aGroupSettings=array_intersect($aGroupSettings,$aBasicDestinationFields);

				if (empty($aGroupSettings))
					return array('status' => 'No valid Data');

                foreach($aGroupSettings as $sGroupSetting)
                {
					$aResult[$sGroupSetting] = $oGroup->$sGroupSetting;
				}
                return $aResult;
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}


    /**
     * RPC Routine to set group properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iGroupID  - ID of the survey
     * @param array|struct $aGroupData - An array with the particular fieldnames as keys and their values to set on that particular survey
     * @return array Of succeeded and failed modifications according to internal validation.
     */
    public function set_group_properties($sSessionKey, $iGroupID, $aGroupData)
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
                $aResult = array();
                // Remove fields that may not be modified
                unset($aGroupData['sid']);
                unset($aGroupData['gid']);
                // Remove invalid fields
                $aDestinationFields=array_flip(Groups::model()->tableSchema->columnNames);
                $aGroupData=array_intersect_key($aGroupData,$aDestinationFields);
				$aGroupAttributes = $oGroup->getAttributes();
				if (empty($aGroupData))
					return array('status' => 'No valid Data');

                foreach($aGroupData as $sFieldName=>$sValue)
                {
						//all dependencies this group has
						$has_dependencies=getGroupDepsForConditions($oGroup->sid,$iGroupID);
						//all dependencies on this group
						$depented_on = getGroupDepsForConditions($oGroup->sid,"all",$iGroupID,"by-targgid");
						//We do not allow groups with dependencies to change order - that would lead to broken dependencies

						if((isset($has_dependencies) || isset($depented_on))  && $sFieldName == 'group_order')
							$aFailed[$sFieldName]='Group with dependencies - Order cannot be changed';
						else
						{
							$oGroup->setAttribute($sFieldName,$sValue);
						}
						try
						{
							// save the change to database - one by one to allow for validation to work
							$bSaveResult=$oGroup->save();
							fixSortOrderGroups($oGroup->sid);
							$aResult[$sFieldName] = $bSaveResult;
							//unset failed values
							if (!$bSaveResult)
								$oGroup->$sFieldName=$aGroupAttributes[$sFieldName];
						}
						catch(Exception $e)
						{
							//unset values that cause exception
							$oGroup->$sFieldName=$aGroupAttributes[$sFieldName];
						}
                }
                return $aResult;
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');
    }

    /**
      * RPC Routine to return the ids and info of groups belonging to survey .
      * Returns array of ids and info.
      *
      * @access public
      * @param string $sSessionKey Auth credentials
      * @param int $iSurveyID Id of the Survey containing the groups
      * @return array The list of groups
      */
	public function list_groups($sSessionKey, $iSurveyID)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{
				$oGroupList = Groups::model()->findAllByAttributes(array("sid"=>$iSurveyID));
				if(count($oGroupList)==0)
					return array('status' => 'No groups found');

				foreach ($oGroupList as $oGroup)
				{
					$aData[]= array('id'=>$oGroup->primaryKey,'group_name'=>$oGroup->attributes['group_name']);
				}
				return $aData;
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}


	/* Question specific functions */


    /**
     * RPC Routine to delete a question of a survey .
     * Returns the id of the deleted question.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int iQuestionID Id of the question to delete
     * @return array|int Id of the deleted Question or status
     */
	public function delete_question($sSessionKey, $iQuestionID)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {
			$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID));
			if (!isset($oQuestion))
				return array('status' => 'Error: Invalid question ID');

			$iSurveyID = $oQuestion['sid'];

            if (hasSurveyPermission($iSurveyID, 'surveycontent', 'delete'))
            {
				$oSurvey = Survey::model()->findByPk($iSurveyID);

				if($oSurvey['active']=='Y')
					return array('status' => 'Survey is active and not editable');
				$iGroupID=$oQuestion['gid'];

				$oCondition = Conditions::model()->findAllByAttributes(array('cqid' => $iQuestionID));
				if(count($oCondition)>0)
					return array('status' => 'Cannot delete Question. Others rely on this question');

				LimeExpressionManager::RevertUpgradeConditionsToRelevance(NULL,$iQuestionID);

				try
				{
					Conditions::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Question_attributes::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Answers::model()->deleteAllByAttributes(array('qid' => $iQuestionID));

					$sCriteria = new CDbCriteria;
					$sCriteria->addCondition('qid = :qid or parent_qid = :qid');
					$sCriteria->params[':qid'] = $iQuestionID;
					Questions::model()->deleteAll($sCriteria);

					Defaultvalues::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Quota_members::model()->deleteAllByAttributes(array('qid' => $iQuestionID));
					Questions::updateSortOrder($iGroupID, $iSurveyID);

                return (int)$iQuestionID;
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
     * RPC Routine to import a question - imports lsq,csv.
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID The id of the survey that the question will belong
     * @param int $iGroupID The id of the group that the question will belong
     * @param string $sImportData String containing the BASE 64 encoded data of a lsg,csv
     * @param string $sImportDataType  lsq,csv
     * @param string $sMandatory Optional Mandatory question option (default to No)
     * @param string $sNewQuestionTitle  Optional new title for the question
     * @param string $sNewqQuestion An optional new question
     * @param string $sNewQuestionHelp An optional new question help text
     * @return array|integer iQuestionID  - ID of the new question - Or status
     */
    public function import_question($sSessionKey, $iSurveyID,$iGroupID, $sImportData, $sImportDataType, $sMandatory='N', $sNewQuestionTitle=NULL, $sNewqQuestion=NULL, $sNewQuestionHelp=NULL)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

            if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
				if($oSurvey->getAttribute('active') =='Y')
					return array('status' => 'Error:Survey is Active and not editable');

				$oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
				if (!isset($oGroup))
					return array('status' => 'Error: Invalid group ID');

				$sGroupSurveyID = $oGroup['sid'];
				if($sGroupSurveyID != $iSurveyID)
					return array('status' => 'Error: Missmatch in surveyid and groupid');

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
                    return (int)$aImportResults['newqid'];
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
    }


    /**
     * RPC Routine to return properties of a question of a survey.
     * Returns string
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iQuestionID Id of the question to get properties
     * @param array $aQuestionSettings The properties to get
     * @param string $sLanguage Optional parameter language for multilingual questions
     * @return array The requested values
     */
	public function get_question_properties($sSessionKey, $iQuestionID, $aQuestionSettings, $sLanguage=NULL)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
		    Yii::app()->loadHelper("surveytranslator");
			$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID));
			if (!isset($oQuestion))
				return array('status' => 'Error: Invalid questionid');

		    $iSurveyID = $oQuestion->sid;

			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{
				if (is_null($sLanguage))
					$sLanguage=Survey::model()->findByPk($iSurveyID)->language;

				if (!array_key_exists($sLanguage,getLanguageDataRestricted()))
					return array('status' => 'Error: Invalid language');

				$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID, 'language'=>$sLanguage));
				if (!isset($oQuestion))
					return array('status' => 'Error: Invalid questionid');

				$aBasicDestinationFields=Questions::model()->tableSchema->columnNames;
				array_push($aBasicDestinationFields,'available_answers')	;
				array_push($aBasicDestinationFields,'subquestions')	;
				array_push($aBasicDestinationFields,'attributes')	;
				array_push($aBasicDestinationFields,'attributes_lang')	;
				array_push($aBasicDestinationFields,'answeroptions')	;
				$aQuestionSettings=array_intersect($aQuestionSettings,$aBasicDestinationFields);

				if (empty($aQuestionSettings))
					return array('status' => 'No valid Data');

                $aResult=array();
                foreach ($aQuestionSettings as $sPropertyName )
                {
					if ($sPropertyName == 'available_answers' || $sPropertyName == 'subquestions')
					{
						$oSubQuestions =  Questions::model()->findAllByAttributes(array('parent_qid' => $iQuestionID,'language'=>$sLanguage ),array('order'=>'title') );
						if (count($oSubQuestions)>0)
						{
	    					$aData = array();
							foreach($oSubQuestions as $oSubQuestion)
							{
								if($sPropertyName == 'available_answers')
									$aData[$oSubQuestion['title']]= $oSubQuestion['question'];
								else
								{
									$aData[$oSubQuestion['qid']]['title']= $oSubQuestion['title'];
									$aData[$oSubQuestion['qid']]['question']= $oSubQuestion['question'];
									$aData[$oSubQuestion['qid']]['scale_id']= $oSubQuestion['scale_id'];
								}

							}

							$aResult[$sPropertyName]=$aData;
						}
						else
							$aResult[$sPropertyName]='No available answers';
					}
					else if ($sPropertyName == 'attributes')
					{
						$oAttributes =  Question_attributes::model()->findAllByAttributes(array('qid' => $iQuestionID, 'language'=> null ),array('order'=>'attribute') );
						if (count($oAttributes)>0)
						{
							$aData = array();
							foreach($oAttributes as $oAttribute)
								$aData[$oAttribute['attribute']]= $oAttribute['value'];

							$aResult['attributes']=$aData;
						}
						else
							$aResult['attributes']='No available attributes';
					}
					else if ($sPropertyName == 'attributes_lang')
					{
						$oAttributes =  Question_attributes::model()->findAllByAttributes(array('qid' => $iQuestionID, 'language'=> $sLanguage ),array('order'=>'attribute') );
						if (count($oAttributes)>0)
						{
							$aData = array();
							foreach($oAttributes as $oAttribute)
								$aData[$oAttribute['attribute']]= $oAttribute['value'];

							$aResult['attributes_lang']=$aData;
						}
						else
							$aResult['attributes_lang']='No available attributes';
					}
					else if ($sPropertyName == 'answeroptions')
					{
						$oAttributes = Answers::model()->findAllByAttributes(array('qid' => $iQuestionID, 'language'=> $sLanguage ),array('order'=>'sortorder') );
						if (count($oAttributes)>0)
						{
							$aData = array();
							foreach($oAttributes as $oAttribute) {
								$aData[$oAttribute['code']]['answer']=$oAttribute['answer'];
								$aData[$oAttribute['code']]['assessment_value']=$oAttribute['assessment_value'];
								$aData[$oAttribute['code']]['scale_id']=$oAttribute['scale_id'];
							}
							$aResult['answeroptions']=$aData;
						}
						else
							$aResult['answeroptions']='No available answer options';
					}
					else
					{
							$aResult[$sPropertyName]=$oQuestion->$sPropertyName;
					}
				}
                return $aResult;
			}
			else
				return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
	}

    /**
     * RPC Routine to set question properties.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iQuestionID  - ID of the question
     * @param array|struct $aQuestionData - An array with the particular fieldnames as keys and their values to set on that particular question
     * @param string $sLanguage Optional parameter language for multilingual questions
     * @return array Of succeeded and failed modifications according to internal validation.
     */
    public function set_question_properties($sSessionKey, $iQuestionID, $aQuestionData,$sLanguage=NULL)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            Yii::app()->loadHelper("surveytranslator");
            $oQuestion=Questions::model()->findByAttributes(array('qid' => $iQuestionID));
            if (is_null($oQuestion))
                return array('status' => 'Error: Invalid group ID');

			$iSurveyID = $oQuestion->sid;

            if (hasSurveyPermission($iSurveyID, 'survey', 'update'))
            {
				if (is_null($sLanguage))
					$sLanguage=Survey::model()->findByPk($iSurveyID)->language;

				if (!array_key_exists($sLanguage,getLanguageDataRestricted()))
					return array('status' => 'Error: Invalid language');

				$oQuestion = Questions::model()->findByAttributes(array('qid' => $iQuestionID, 'language'=>$sLanguage));
				if (!isset($oQuestion))
					return array('status' => 'Error: Invalid questionid');

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
                $aQuestionAttributes = $oQuestion->getAttributes();

				if (empty($aQuestionData))
					return array('status' => 'No valid Data');

                foreach($aQuestionData as $sFieldName=>$sValue)
                {
					//all the dependencies that this question has to other questions
					$dependencies=getQuestDepsForConditions($oQuestion->sid,$oQuestion->gid,$iQuestionID);
					//all dependencies by other questions to this question
					$is_criteria_question=getQuestDepsForConditions($oQuestion->sid,$oQuestion->gid,"all",$iQuestionID,"by-targqid");
					//We do not allow questions with dependencies in the same group to change order - that would lead to broken dependencies

					if((isset($dependencies) || isset($is_criteria_question))  && $sFieldName == 'question_order')
						$aFailed[$sFieldName]='Questions with dependencies - Order cannot be changed';
					else
					{
						$oQuestion->setAttribute($sFieldName,$sValue);
					}

					try
					{
						$bSaveResult=$oQuestion->save(); // save the change to database
						Questions::model()->updateQuestionOrder($oQuestion->gid, $oQuestion->sid);
						$aResult[$sFieldName]=$bSaveResult;
						//unset fields that failed
						if (!$bSaveResult)
							$oQuestion->$sFieldName=$aQuestionAttributes[$sFieldName];
					}
					catch(Exception $e)
					{
						//unset fields that caused exception
						$oQuestion->$sFieldName=$aQuestionAttributes[$sFieldName];
					}
                }
                return $aResult;
            }
            else
                return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid Session key');
    }


    /**
     * RPC Routine to return the ids and info of questions of a survey/group.
     * Returns array of ids and info.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the survey to list questions
     * @param int $iGroupID Optional id of the group to list questions
     * @param string $sLanguage Optional parameter language for multilingual questions
     * @return array The list of questions
     */
	public function list_questions($sSessionKey, $iSurveyID, $iGroupID=NULL, $sLanguage=NULL)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			Yii::app()->loadHelper("surveytranslator");
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'survey', 'read'))
			{
				if (is_null($sLanguage))
					$sLanguage=$oSurvey->language;

				if (!array_key_exists($sLanguage,getLanguageDataRestricted()))
					return array('status' => 'Error: Invalid language');

				if($iGroupID!=NULL)
				{
					$oGroup = Groups::model()->findByAttributes(array('gid' => $iGroupID));
					$sGroupSurveyID = $oGroup['sid'];

					if($sGroupSurveyID != $iSurveyID)
						return array('status' => 'Error: IMissmatch in surveyid and groupid');
					else
						$aQuestionList = Questions::model()->findAllByAttributes(array("sid"=>$iSurveyID, "gid"=>$iGroupID,"parent_qid"=>"0","language"=>$sLanguage));
				}
				else
					$aQuestionList = Questions::model()->findAllByAttributes(array("sid"=>$iSurveyID,"parent_qid"=>"0", "language"=>$sLanguage));

				if(count($aQuestionList)==0)
					return array('status' => 'No questions found');

				foreach ($aQuestionList as $oQuestion)
				{
					$aData[]= array('id'=>$oQuestion->primaryKey,'type'=>$oQuestion->attributes['type'], 'question'=>$oQuestion->attributes['question']);
				}
				return $aData;
			}
			else
				return array('status' => 'No permission');
        }
        else
			return array('status' => 'Invalid session key');
	}

	/* Participant-Token specific functions */



    /**
     * RPC Routine to add participants to the tokens collection of the survey.
     * Returns the inserted data including additional new information like the Token entry ID and the token string.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey
     * @param struct $aParticipantData Data of the participants to be added
     * @param bool Optional - Defaults to true and determins if the access token automatically created
     * @return array The values added
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
            $aDestinationFields = array_flip($aDestinationFields);

            foreach ($aParticipantData as &$aParticipant)
            {
                $aParticipant=array_intersect_key($aParticipant,$aDestinationFields);
                Tokens_dynamic::sid($iSurveyID);
                $token = new Tokens_dynamic;

                if ($new_token_id=$token->insertParticipant($aParticipant))
                {
                     if ($bCreateToken)
                        $token_string = Tokens_dynamic::model()->createToken($new_token_id);
                    else
                        $token_string = '';

                    $aParticipant = array_merge($aParticipant, array(
                    'tid' => $new_token_id,
                    'token' => $token_string,
                    ));
                }
                else
                {
					$aParticipant=false;
				}
            }
            return $aParticipantData;
        }
        else
            return array('status' => 'No permission');
    }

    /**
     * RPC Routine to delete multiple participants of a Survey.
     * Returns the id of the deleted token
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey that the participants belong to
     * @param array $aTokenIDs Id of the tokens/participants to delete
     * @return array Result of deletion
     */
	public function delete_participants($sSessionKey, $iSurveyID, $aTokenIDs)
	{
        if ($this->_checkSessionKey($sSessionKey))
        {
			$iSurveyID = sanitize_int($iSurveyID);

			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
			{
				if(!tableExists("{{tokens_$iSurveyID}}"))
					return array('status' => 'Error: No token table');

				$aResult=array();
				foreach($aTokenIDs as $iTokenID)
				{
					$tokenidExists = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
					if (!isset($tokenidExists))
						$aResult[$iTokenID]='Invalid token ID';
					else
					{
					Survey_links::deleteTokenLink(array($iTokenID), $iSurveyID);
					if(Tokens_dynamic::model($iSurveyID)->deleteRecords(array($iTokenID)))
						$aResult[$iTokenID]='Deleted';
					else
						$aResult[$iTokenID]='Deletion went wrong';
					}
				}
				return $aResult;
            }
            else
                return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}


    /**
      * RPC Routine to return settings of a token/participant of a survey .
      *
      * @access public
      * @param string $sSessionKey Auth credentials
      * @param int $iSurveyID Id of the Survey to get token properties
      * @param int $iTokenID Id of the participant to check
      * @param array $aTokenProperties The properties to get
      * @return array The requested values
      */
	public function get_participant_properties($sSessionKey, $iSurveyID, $iTokenID, $aTokenProperties)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$surveyidExists = Survey::model()->findByPk($iSurveyID);
			if (!isset($surveyidExists))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'tokens', 'read'))
			{
				if(!tableExists("{{tokens_$iSurveyID}}"))
					return array('status' => 'Error: No token table');

				$oToken = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
				if (!isset($oToken))
					return array('status' => 'Error: Invalid tokenid');

                $aResult=array();
                $aBasicDestinationFields=Tokens_dynamic::model()->tableSchema->columnNames;
                $aTokenProperties=array_intersect($aTokenProperties,$aBasicDestinationFields);

				if (empty($aTokenProperties))
					return array('status' => 'No valid Data');

                foreach($aTokenProperties as $sPropertyName )
                {
					$aResult[$sPropertyName]=$oToken->$sPropertyName;
				}
				return $aResult;
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}

    /**
     * RPC Routine to set properties of a survey participant/token.
     * Returns array
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the survey that participants belong
     * @param int $iTokenID Id of the participant to alter
     * @param array|struct $aTokenData Data to change
     * @return array Result of the change action
     */
	public function set_participant_properties($sSessionKey, $iSurveyID, $iTokenID, $aTokenData)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'tokens', 'update'))
			{
				if(!tableExists("{{tokens_$iSurveyID}}"))
					return array('status' => 'Error: No token table');

				$oToken = Tokens_dynamic::model($iSurveyID)->findByPk($iTokenID);
				if (!isset($oToken))
					return array('status' => 'Error: Invalid tokenid');

				$aResult = array();
				// Remove fields that may not be modified
				unset($aTokenData['tid']);

				$aBasicDestinationFields=array_flip(Tokens_dynamic::model()->tableSchema->columnNames);
				$aTokenData=array_intersect_key($aTokenData,$aBasicDestinationFields);
				$aTokenAttributes = $oToken->getAttributes();

				if (empty($aTokenData))
					return array('status' => 'No valid Data');

               foreach($aTokenData as $sFieldName=>$sValue)
               {
					$oToken->$sFieldName=$sValue;
				   try
				   {
						$bSaveResult=$oToken->save();
						$aResult[$sFieldName]=$bSaveResult;
						//unset fields that failed
						if (!$bSaveResult)
							$oToken->$sFieldName=$aTokenAttributes[$sFieldName];
				   }
				   catch(Exception $e)
				   {
						$oToken->$sFieldName=$aTokenAttributes[$sFieldName];
				   }
			   }
			   	return $aResult;
			}
			else
				return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
	}



   /**
    * RPC Routine to return the ids and info  of token/participants of a survey.
    * if $bUnused is true, user will get the list of not completed tokens (token_return functionality).
    * Parameters iStart and ilimit are used to limit the number of results of this call.
    *
    * @access public
    * @param string $sSessionKey Auth credentials
    * @param int $iSurveyID Id of the survey to list participants
    * @param int $iStart Start id of the token list
    * @param int  $iLimit Number of participants to return
    * @param bool $bUnused If you want unused tokensm, set true
    * @return array The list of tokens
    */
	public function list_participants($sSessionKey, $iSurveyID, $iStart=0, $iLimit=10, $bUnused=false)
	{
       if ($this->_checkSessionKey($sSessionKey))
       {
			$oSurvey = Survey::model()->findByPk($iSurveyID);
			if (!isset($oSurvey))
				return array('status' => 'Error: Invalid survey ID');

			if (hasSurveyPermission($iSurveyID, 'tokens', 'read'))
			{
				if(!tableExists("{{tokens_$iSurveyID}}"))
					return array('status' => 'Error: No token table');

				if($bUnused)
					$oTokens = Tokens_dynamic::model($iSurveyID)->findAll(array('condition'=>"completed = 'N'", 'limit' => $iLimit, 'offset' => $iStart));
				else
					$oTokens = Tokens_dynamic::model($iSurveyID)->findAll(array('limit' => $iLimit, 'offset' => $iStart));

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
     * RPC routine to to initialise the survey's collection of tokens where new participant tokens may be later added.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
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
     * RPC Routine to invite participants in a survey
     * Returns array of results of sending
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the survey that participants belong
     * @return array Result of the action
     */
	public function invite_participants($sSessionKey, $iSurveyID )
	{
		Yii::app()->loadHelper('admin/token');
		if (!$this->_checkSessionKey($sSessionKey))
			return array('status' => 'Invalid session key');

		$oSurvey = Survey::model()->findByPk($iSurveyID);
		if (!isset($oSurvey))
			return array('status' => 'Error: Invalid survey ID');

		if (hasSurveyPermission($iSurveyID, 'tokens', 'update'))
		{

			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');

			$iMaxEmails = (int)Yii::app()->getConfig("maxemails");
			$SQLemailstatuscondition = "emailstatus = 'OK'";

			$oTokens = Tokens_dynamic::model($iSurveyID);
			$aResultTokens = $oTokens->findUninvited(false, $iMaxEmails, true, $SQLemailstatuscondition);
			$aAllTokens = $oTokens->findUninvited(false, 0, true, $SQLemailstatuscondition);
			if (empty($aResultTokens))
				return array('status' => 'Error: No candidate tokens');

			$aResult = emailTokens($iSurveyID,$aResultTokens,'invite');
			$iLeft = count($aAllTokens) - count($aResultTokens);
			$aResult['status'] =$iLeft. " left to send";

			return $aResult;
		}
		else
			return array('status' => 'No permission');
	}


    /**
     * RPC Routine to send reminder for participants in a survey
     * Returns array of results of sending
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the survey that participants belong
     * @param int $iMinDaysBetween Optional parameter days from last reminder
     * @param int $iMaxReminders Optional parameter Maximum reminders count
     * @return array Result of the action
     */
	public function remind_participants($sSessionKey, $iSurveyID, $iMinDaysBetween=null, $iMaxReminders=null )
	{
		Yii::app()->loadHelper('admin/token');
		if (!$this->_checkSessionKey($sSessionKey))
			return array('status' => 'Invalid session key');

		$oSurvey = Survey::model()->findByPk($iSurveyID);
		if (!isset($oSurvey))
			return array('status' => 'Error: Invalid survey ID');

		if (hasSurveyPermission($iSurveyID, 'tokens', 'update'))
		{
			$timeadjust = Yii::app()->getConfig("timeadjust");

			if(!tableExists("{{tokens_$iSurveyID}}"))
				return array('status' => 'Error: No token table');

			if (getEmailFormat($iSurveyID) == 'html')
				$bHtml = true;
			else
				$bHtml = false;

			$SQLemailstatuscondition = "emailstatus = 'OK'";
			$SQLremindercountcondition = '';
            $SQLreminderdelaycondition = '';
			$iMaxEmails = (int)Yii::app()->getConfig("maxemails");

			if(!is_null($iMinDaysBetween))
			{
				$compareddate = dateShift(date("Y-m-d H:i:s", time() - 86400 * $iMinDaysBetween), "Y-m-d H:i", $timeadjust);
                $SQLreminderdelaycondition = " ((remindersent = 'N' AND sent < '" . $compareddate . "')  OR  (remindersent < '" . $compareddate . "'))";
			}

			if(!is_null($iMaxReminders))
				$SQLremindercountcondition = "remindercount < " . $iMaxReminders;

			$oTokens = Tokens_dynamic::model($iSurveyID);
			$aResultTokens = $oTokens->findUninvited(false, $iMaxEmails, false, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
			$aAllTokens = $oTokens->findUninvited(false, 0, false, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);

			if (empty($aResultTokens))
				return array('status' => 'Error: No candidate tokens');

			$aResult = emailTokens($iSurveyID, $aResultTokens, 'remind');

			$iLeft = count($aAllTokens) - count($aResultTokens);
			$aResult['status'] =$iLeft. " left to send";
			return $aResult;
		}
		else
			return array('status' => 'No permission');

	}


	/* Response specific functions */


    /**
     * RPC Routine to add a response to the survey responses collection.
     * Returns the id of the inserted survey response
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey to insert responses
     * @param struct $aResponseData The actual response
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
            $aBasicDestinationFields=$survey_dynamic->tableSchema->columnNames;
            $aResponseData=array_intersect_key($aResponseData, array_flip($aBasicDestinationFields));
            $result_id = $survey_dynamic->insertRecords($aResponseData);

            if ($result_id)
                return $result_id;
            else
                return array('status' => 'Unable to add response');
        }
        else
            return array('status' => 'No permission');

    }

    /**
     * RPC Routine to export responses.
     * Returns the requested file as base64 encoded string
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey
     * @param string $sDocumentType pdf,csv,xls,doc
     * @param string $sLanguageCode The language to be used
     * @param string $sCompletionStatus Optional 'complete','incomplete' or 'all' - defaults to 'all'
     * @param string $sHeadingType 'code','full' or 'abbreviated' Optional defaults to 'code'
     * @param string $sResponseType 'short' or 'long' Optional defaults to 'short'
     * @param integer $iFromResponseID Optional
     * @param integer $iToResponseID Optional
     * @param array $aFields Optional Selected fields
     * @return array|string On success: Requested file as base 64-encoded string. On failure array with error information
     * */
    public function export_responses($sSessionKey, $iSurveyID, $sDocumentType, $sLanguageCode=null, $sCompletionStatus='all', $sHeadingType='full', $sResponseType='short', $iFromResponseID=null, $iToResponseID=null, $aFields=null)
    {
        if (!$this->_checkSessionKey($sSessionKey)) return array('status' => 'Invalid session key');
        Yii::app()->loadHelper('admin/exportresults');
        if (!tableExists('{{survey_' . $iSurveyID . '}}')) return array('status' => 'No Data');
		if(!$count = Survey_dynamic::model($iSurveyID)->count()) return array('status' => 'No Data');

        if (!hasSurveyPermission($iSurveyID, 'responses', 'export')) return array('status' => 'No permission');
        if (is_null($sLanguageCode)) $sLanguageCode=getBaseLanguageFromSurveyID($iSurveyID);
        if (is_null($aFields)) $aFields=array_keys(createFieldMap($iSurveyID,'full',true,false,$sLanguageCode));
        if($sDocumentType=='xls'){
           // Cut down to the first 255 fields
           $aFields=array_slice($aFields,0,255);
        }
        $oFomattingOptions=new FormattingOptions();
        
        if($iFromResponseID !=null)   
			$oFomattingOptions->responseMinRecord=$iFromResponseID;
        else
			$oFomattingOptions->responseMinRecord=1;        
        
        if($iToResponseID !=null)   
            $oFomattingOptions->responseMaxRecord=$iToResponseID;
        else
            $oFomattingOptions->responseMaxRecord = $count;

        $oFomattingOptions->selectedColumns=$aFields;
        $oFomattingOptions->responseCompletionState=$sCompletionStatus;
        $oFomattingOptions->headingFormat=$sHeadingType;
        $oFomattingOptions->answerFormat=$sResponseType;
        $oFomattingOptions->output='file';
        $oExport=new ExportSurveyResultsService();
        $sTempFile=$oExport->exportSurvey($iSurveyID,$sLanguageCode, $sDocumentType,$oFomattingOptions, '');
        $sFileData = file_get_contents($sTempFile);
        unlink($sTempFile);

        return base64_encode($sFileData);
    }


    /**
     * Tries to login with username and password
     *
     * @access protected
     * @param string $sUsername The username
     * @param mixed $sPassword The Password
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
     * @param string $username The username
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
     * @param string $sSessionKey Auth credentials
     * @return bool
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
}
