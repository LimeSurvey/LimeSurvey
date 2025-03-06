<?php

/**
* This class handles all methods of the RemoteControl 2 API
*/

use LimeSurvey\PluginManager\PluginEvent;

class remotecontrol_handle
{
    const INVALID_SESSION_KEY = 'Invalid session key';

    /**
     * @var AdminController
     */
    protected $controller;

    /**
     * Constructor, stores the action instance into this handle class
     *
     * @access public
     * @param AdminController $controller
     */
    public function __construct(AdminController $controller)
    {
        $this->controller = $controller;
    }


    /**
     * Create and return a session key.
     *
     * Using this function you can create a new XML-RPC/JSON-RPC session key.
     * This is mandatory for all following LSRC2 function calls.
     *
     * * In case of success : Return the session key in string
     * * In case of error:
     *     * for protocol-level errors (invalid format etc), an error message.
     *     * For invalid username and password, returns a null error and the result body contains a 'status' name-value pair with the error message.
     *
     * @access public
     * @param string $username
     * @param string $password
     * @param string $plugin to be used
     * @return string|array
     */
    public function get_session_key($username, $password, $plugin = 'Authdb')
    {
        $username = (string) $username;
        $password = (string) $password;
        $loginResult = $this->_doLogin($username, $password, $plugin);
        if ($loginResult === true) {
            $this->_jumpStartSession($username);
            $sSessionKey = Yii::app()->securityManager->generateRandomString(32);
            $session = new Session();
            $session->id = $sSessionKey;
            $session->expire = time() + (int) Yii::app()->getConfig('iSessionExpirationTime', ini_get('session.gc_maxlifetime'));
            $session->data = $username;
            $session->save();
            return $sSessionKey;
        }
        if (is_string($loginResult)) {
            return array('status' => $loginResult);
        }
        return array('status' => 'Invalid user name or password');
    }

    /**
     * Close the RPC session
     *
     * Using this function you can close a previously opened XML-RPC/JSON-RPC session.
     *
     * @access public
     * @param string $sSessionKey the session key
     * @return string OK
     */
    public function release_session_key($sSessionKey)
    {
        $sSessionKey = (string) $sSessionKey;
        Session::model()->deleteAllByAttributes(array('id' => $sSessionKey));
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()->deleteAll($criteria);
        return 'OK';
    }

    /**
     * Get the available site settings
     *
     * Using this function you can get the available site settings.
     *
     * @access public
     * @param string $sSessionKey the session key
     * @return array
     */
    public function get_available_site_settings($sSessionKey)
    {
        $sSessionKey = (string) $sSessionKey;
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            return array('status' => 'User is not allowed to read the available site settings');
        }

        return Yii::app()->getAvailableConfigs();
    }

    /**
     * Get a global setting
     *
     * Function to query site settings. Can only be used by super administrators.
     *
     * @access public
     * @param string $sSessionKey Auth Credentials
     * @param string $sSetttingName Name of the setting to get
     * @return string|array The requested value or an array with the error in case of error
     */
    public function get_site_settings($sSessionKey, $sSetttingName)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $sSetttingName = (string) $sSetttingName;
                if (Yii::app()->getConfig($sSetttingName) !== false) {
                    return Yii::app()->getConfig($sSetttingName);
                } else {
                    return array('status' => 'Invalid setting');
                }
            } else {
                return array('status' => 'Invalid setting');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /* Survey specific functions */

    /**
     * Add an empty survey with minimum details
     *
     * This just tries to create an empty survey with the minimal settings.
     *
     * Failure status: Invalid session key, No permission, Faulty parameters, Creation Failed result
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The desired ID of the Survey to add
     * @param string $sSurveyTitle Title of the new Survey
     * @param string $sSurveyLanguage Default language of the Survey
     * @param string $sformat (optional) Question appearance format (A, G or S) for "All on one page", "Group by Group", "Single questions", default to group by group (G)
     * @return int|array The survey ID in case of success
     */
    public function add_survey($sSessionKey, $iSurveyID, $sSurveyTitle, $sSurveyLanguage, $sformat = 'G')
    {
        $iSurveyID = (int) $iSurveyID;
        $sSurveyTitle = (string) $sSurveyTitle;
        $sSurveyLanguage = (string) $sSurveyLanguage;
        Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
                if ($sSurveyTitle == '' || $sSurveyLanguage == '' || !array_key_exists($sSurveyLanguage, getLanguageDataRestricted()) || !in_array($sformat, array('A', 'G', 'S'))) {
                                    return array('status' => 'Faulty parameters');
                }

                $aInsertData = array(
                    'template' => App()->getConfig('defaulttheme'),
                    'owner_id' => Yii::app()->session['loginID'],
                    'active' => 'N',
                    'language' => $sSurveyLanguage,
                    'format' => $sformat
                );

                if (!is_null($iSurveyID)) {
                                $aInsertData['wishSID'] = $iSurveyID;
                }

                try {
                    $newSurvey = Survey::model()->insertNewSurvey($aInsertData);
                    if (!$newSurvey->sid) {
                        return array('status' => 'Creation Failed'); // status are a string, another way to send errors ?
                    }
                    $iNewSurveyid = $newSurvey->sid;

                    $sTitle = html_entity_decode($sSurveyTitle, ENT_QUOTES, "UTF-8");

                    $aInsertData = array(
                        'surveyls_survey_id' => $iNewSurveyid,
                        'surveyls_title' => $sTitle,
                        'surveyls_language' => $sSurveyLanguage,
                    );

                    $langsettings = new SurveyLanguageSetting();
                    $langsettings->insertNewSurvey($aInsertData);
                    Permission::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'], $iNewSurveyid);

                    return (int) $iNewSurveyid;
                } catch (Exception $e) {
                    return array('status' => $e->getmessage());
                }
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Delete a survey.
     *
     * Failure status: Invalid session key, No permission
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The ID of the Survey to be deleted
     * @return array Returns status : status are OK in case of success
     */
    public function delete_survey($sSessionKey, $iSurveyID)
    {
        $iSurveyID = (int) $iSurveyID;
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete')) {
                Survey::model()->deleteSurvey($iSurveyID, true);
                return array('status' => 'OK');
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Import survey in a known format (RPC function)
     *
     * Allow importing lss, csv, xls or survey zip archive in BASE 64 encoded.
     *
     * Failure status: Invalid session key, No permission, The import error
     *
     * @access public
     * @param string $sSessionKey Auth Credentials
     * @param string $sImportData String containing the BASE 64 encoded data of a lss, csv, txt or survey lsa archive
     * @param string $sImportDataType lss, csv, txt or lsa
     * @param string $sNewSurveyName (optional) The optional new name of the survey
     * @param integer $DestSurveyID  (optional) This is the new ID of the survey - if already used a random one will be taken instead
     * @return int|array The ID of the new survey in case of success
     */
    public function import_survey($sSessionKey, $sImportData, $sImportDataType, $sNewSurveyName = null, $DestSurveyID = null)
    {
        $sImportData = (string) $sImportData;
        $sNewSurveyName = (string) $sNewSurveyName;
        if (!is_null($DestSurveyID)) {
            $DestSurveyID = (int) $DestSurveyID;
        }
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
                if (!in_array($sImportDataType, array('lsa', 'csv', 'txt', 'lss'))) {
                    return array('status' => 'Invalid extension');
                }
                Yii::app()->loadHelper('admin/import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40) . '.' . $sImportDataType;
                file_put_contents($sFullFilePath, base64_decode(chunk_split($sImportData)));
                $aImportResults = importSurveyFile($sFullFilePath, true, $sNewSurveyName, $DestSurveyID);
                unlink($sFullFilePath);
                if (isset($aImportResults['error']) && $aImportResults['error']) {
                    return array('status' => 'Error: ' . $aImportResults['error']);
                } else {
                    return (int) $aImportResults['newsid'];
                }
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Copy survey (RPC function)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID_org Id of the source survey
     * @param string $sNewname name of the new survey
     * @param integer $DestSurveyID  (optional) This is the new ID of the survey - if already used a random one will be taken instead
     * @return array On success: new $iSurveyID in array['newsid']. On failure array with error information
     * */
    public function copy_survey($sSessionKey, $iSurveyID_org, $sNewname, $DestSurveyID = null)
    {
        $iSurveyID = (int) $iSurveyID_org;
        if (!is_null($DestSurveyID)) {
            $DestSurveyID = (int) $DestSurveyID;
        }
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        if (!Permission::model()->hasGlobalPermission('surveys', 'create')) {
            return array(
                'status' => 'Copy failed',
                'error' => "You don't have sufficient permissions."
            );
        }
        if (!$iSurveyID) {
            return array(
                'status' => 'Copy failed',
                'error' => "No survey ID has been provided. Cannot copy survey"
            );
        }
        if (!Survey::model()->findByPk($iSurveyID)) {
            return array(
                'status' => 'Copy failed',
                'error' => "Invalid survey ID"
            );
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export')) {
            return array(
                'status' => 'Copy failed',
                'error' => "You don't have sufficient permissions"
            );
        }
        /* All check : try to copy */
        $aExcludes = array();
        $sNewSurveyName = $sNewname;
        $aExcludes['dates'] = true;
        $btranslinksfields = true;
        Yii::app()->loadHelper('export');
        $copysurveydata = surveyGetXMLData($iSurveyID, $aExcludes);
        if (empty($copysurveydata)) {
            return array(
                'status' => 'Copy failed',
                'error' => "No data from survey"
            );
        }
        Yii::app()->loadHelper('admin/import');
        $aImportResults = XMLImportSurvey('', $copysurveydata, $sNewSurveyName, $DestSurveyID, $btranslinksfields);
        if (empty($aImportResults['newsid'])) {
            return array(
                'status' => 'Copy failed',
                'error' => $aImportResults['error']
            );
        }
        if (isset($aExcludes['conditions'])) {
            Question::model()->updateAll(array('relevance' => '1'), 'sid=' . $aImportResults['newsid']);
            QuestionGroup::model()->updateAll(array('grelevance' => '1'), 'sid=' . $aImportResults['newsid']);
        }
        if (!isset($aExcludes['permissions'])) {
            Permission::model()->copySurveyPermissions($iSurveyID, $aImportResults['newsid']);
        }
        return array(
            'status' => 'OK',
            'newsid' => $aImportResults['newsid']
        );


    }

    /**
     * Get survey properties (RPC function)
     *
     * Get properties of a survey
     * All internal properties of a survey are available.
     * @see \Survey for the list of available properties
     *
     * Failure status : Invalid survey ID, Invalid session key, No permission, No valid Data
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The id of the Survey to be checked
     * @param array|null $aSurveySettings (optional) The properties to get
     * @return array
     */
    public function get_survey_properties($sSessionKey, $iSurveyID, $aSurveySettings = null)
    {
        Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read')) {
                $aBasicDestinationFields = Survey::model()->tableSchema->columnNames;
                if (!empty($aSurveySettings)) {
                    $aSurveySettings = array_intersect($aSurveySettings, $aBasicDestinationFields);
                } else {
                    $aSurveySettings = $aBasicDestinationFields;
                }
                if (empty($aSurveySettings)) {
                    return array('status' => 'No valid Data');
                }
                $aResult = array();
                foreach ($aSurveySettings as $sPropertyName) {
                    $aResult[$sPropertyName] = $oSurvey->$sPropertyName;
                }
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Set survey properties (RPC function)
     *
     * @see \Survey for the list of available properties
     * Properties available are restricted
     * * Always
     *     * sid
     *     * active
     *     * language
     *     * additional_languages
     * * If survey is active
     *     * anonymized
     *     * datestamp
     *     * savetimings
     *     * ipaddr
     *     * refurl
     *
     * In case of partial success : return an array with key as properties and value as boolean , true if saved with success.
     *
     * Failure status : Invalid survey ID, Invalid session key, No permission, No valid Data
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID - ID of the Survey
     * @param struct $aSurveyData - An array with the particular fieldnames as keys and their values to set on that particular Survey
     * @return array Of succeeded and failed nodifications according to internal validation
     */
    public function set_survey_properties($sSessionKey, $iSurveyID, $aSurveyData)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
                // Remove fields that may not be modified
                unset($aSurveyData['sid']);
                //unset($aSurveyData['owner_id']);
                unset($aSurveyData['active']);
                unset($aSurveyData['language']);
                unset($aSurveyData['additional_languages']);
                // Remove invalid fields
                $aDestinationFields = array_flip(Survey::model()->tableSchema->columnNames);
                $aSurveyData = array_intersect_key($aSurveyData, $aDestinationFields);
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                $aBasicAttributes = $oSurvey->getAttributes();
                $aResult = array();

                if ($oSurvey->isActive) {
                    // remove all fields that may not be changed when a survey is active
                    unset($aSurveyData['anonymized']);
                    unset($aSurveyData['datestamp']);
                    unset($aSurveyData['savetimings']);
                    unset($aSurveyData['ipaddr']);
                    unset($aSurveyData['refurl']);
                }

                if (empty($aSurveyData)) {
                                    return array('status' => 'No valid Data');
                }

                foreach ($aSurveyData as $sFieldName => $sValue) {
                    $oSurvey->$sFieldName = $sValue;
                    try {
                        $bSaveResult = $oSurvey->save(); // save the change to database
                        //unset the value if it fails, so as to prevent future fails
                        $aResult[$sFieldName] = $bSaveResult;
                        if (!$bSaveResult) {
                                                $oSurvey->$sFieldName = $aBasicAttributes[$sFieldName];
                        }
                    } catch (Exception $e) {
                        //unset the value that caused the exception
                        $oSurvey->$sFieldName = $aBasicAttributes[$sFieldName];
                    }
                }
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /**
     * Activate survey (RPC function)
     *
     * Return the result of the activation
     * Failure status : Invalid Survey ID, Survey already active, Consistency check error, Activation Error, Invalid session key, No permission
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to be activated
     * @param array $activationSettings (optional) survey activation settings to change prior to activation, format is array of settingName => settingValue pairs (default: [])
     * @return array in case of success result of the activation
     */
    public function activate_survey($sSessionKey, $iSurveyID, $userActivationSettings = [])
    {
        // check provided session key and setup user session
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        // check valid survey ID and current user has permission
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update')) {
            return array('status' => 'No permission');
        }
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        // check if survey is already activated
        if ($oSurvey->isActive) {
            return array('status' => 'Error: Survey already active');
        }
        // Check consistency for groups and questions
        Yii::app()->loadHelper('admin/activate');
        $checkHasGroup = checkHasGroup($iSurveyID);
        $checkGroup = checkGroup($iSurveyID);
        if ($checkHasGroup !== false || $checkGroup !== false) {
            return array('status' => 'Error: Survey does not pass consistency check');
        }
        $surveyActivator = new SurveyActivator($oSurvey);
        // list of activation related survey settings and allowed values
        $activationSettingNames = [
            'anonymized',
            'datestamp',
            'ipaddr',
            'ipanonymize',
            'refurl',
            'savetimings',
        ];
        // update survey activation settings
        foreach ($activationSettingNames as $activationSettingName) {
            // use inherited value
            $oSurvey->{$activationSettingName} = $oSurvey->oOptions->{$activationSettingName};
            // override inherited value with user input
            if (isset($userActivationSettings[$activationSettingName]) && in_array($userActivationSettings[$activationSettingName], ['Y', 'N'])) {
                $oSurvey->{$activationSettingName} = $userActivationSettings[$activationSettingName];
            }
        }
        // persist changes
        $oSurvey->save();
        // attempt to activate the survey
        $aActivateResults = $surveyActivator->activate();
        if (isset($aActivateResults['error'])) {
            return array('status' => 'Error: ' . $aActivateResults['error']);
        }
        return $aActivateResults;
    }

    /**
     * Export survey statistics (RPC function)
     *
     * Allow to export statistics available Returns string - base64 encoding of the statistics.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param string $docType (optional) Type of documents the exported statistics should be (pdf|xls|html)
     * @param string $sLanguage (optional) language of the survey to use (default from Survey)
     * @param string $graph (optional) Create graph option (default : no)
     * @param int|array $groupIDs (optional) array or integer containing the groups we choose to generate statistics from
     * @return string|array in case of success : Base64 encoded string with the statistics file
     */
    public function export_statistics($sSessionKey, $iSurveyID, $docType = 'pdf', $sLanguage = null, $graph = '0', $groupIDs = null)
    {
        Yii::app()->loadHelper('admin/statistics');

        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
                    return array('status' => 'Error: Invalid survey ID');
        }
        ;

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'statistics', 'read')) {
                    return array('status' => 'Error: No Permission');
        }

        $aAdditionalLanguages = array_filter(explode(" ", (string) $oSurvey->additional_languages));

        if (is_null($sLanguage) || !in_array($sLanguage, $aAdditionalLanguages)) {
                    $sLanguage = $oSurvey->language;
        }

        $oAllQuestions = Question::model()->getQuestionList($iSurveyID);
        if (!isset($oAllQuestions)) {
                    return array('status' => 'No available data');
        }

        if ($groupIDs != null) {
            if (is_int($groupIDs)) {
                            $groupIDs = array($groupIDs);
            }

            if (is_array($groupIDs)) {
                //check that every value of the array belongs to the survey defined
                $aGroups = QuestionGroup::model()->findAllByAttributes(array('sid' => $iSurveyID));

                foreach ($aGroups as $group) {
                                    $validGroups[] = $group['gid'];
                }

                $groupIDs = array_intersect($groupIDs, $validGroups);

                if (empty($groupIDs)) {
                                    return array('status' => 'Error: Invalid group ID');
                }

                foreach ($oAllQuestions as $key => $aQuestion) {
                    if (!in_array($aQuestion['gid'], $groupIDs)) {
                                            unset($oAllQuestions[$key]);
                    }
                }
            } else {
                            return array('status' => 'Error: Invalid group ID');
            }
        }

        if (!isset($oAllQuestions)) {
                return array('status' => 'No available data');
        }

        usort($oAllQuestions, 'groupOrderThenQuestionOrder');

        $aSummary = createCompleteSGQA($iSurveyID, $oAllQuestions, $sLanguage);

        $helper = new statistics_helper();
        switch ($docType) {
            case 'pdf':
                $sTempFile = $helper->generate_statistics($iSurveyID, $aSummary, $aSummary, $graph, $docType, 'F', $sLanguage);
                $sResult = file_get_contents($sTempFile);
                unlink($sTempFile);
                break;
            case 'xls':
                $sTempFile = $helper->generate_statistics($iSurveyID, $aSummary, $aSummary, '0', $docType, 'F', $sLanguage);
                $sResult = file_get_contents($sTempFile);
                unlink($sTempFile);
                break;
            case 'html':
                $sResult = $helper->generate_statistics($iSurveyID, $aSummary, $aSummary, '0', $docType, 'DD', $sLanguage);
                break;
        }

        return base64_encode((string) $sResult);
    }

    /**
     * Export submission timeline (RPC function)
     *
     * Returns an array of values (count and period)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param string $sType (day|hour)
     * @param string $dStart
     * @param string $dEnd
     * @return array On success: The timeline. On failure array with error information
     * */
    public function export_timeline($sSessionKey, $iSurveyID, $sType, $dStart, $dEnd)
    {
        $iSurveyID = (int) $iSurveyID;
        $survey = Survey::model()->findByPk($iSurveyID);

        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        if (is_null($survey)) {
            return array('status' => 'Error: Invalid survey ID');
        }
        if (!in_array($sType, array('day', 'hour'))) {
            return array('status' => 'Invalid Period');
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
            return array('status' => 'No permission');
        }
        if (is_null($survey)) {
            return array('status' => 'Error: Invalid survey ID');
        }
        if (!tableExists($survey->responsesTableName)) {
            return array('status' => 'No available data');
        }

        $oResponses = SurveyDynamic::model($iSurveyID)->timeline($sType, $dStart, $dEnd);
        if (empty($oResponses)) {
            return array('status' => 'No valid Data');
        }

        return $oResponses;
    }

    /**
     * Get survey summary, regarding token usage and survey participation (RPC function)
     *
     * Returns the requested value as string, or all status in an array
     *
     * Available status are
     * * For Survey stats
     *     * completed_responses
     *     * incomplete_responses
     *     * full_responses
     * * For token part
     *     * token_count
     *     * token_invalid
     *     * token_sent
     *     * token_opted_out
     *     * token_completed
     *     * token_screenout
     * All available status can be sent using `all`
     *
     * Failure status : No available data, No such property, Invalid session key, No permission
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to get summary
     * @param string $sStatName (optional) Name of the summary option, or all to send all in an array (all by default)
     * @return string|array in case of success the requested value or an array of all values
     */
    public function get_summary($sSessionKey, $iSurveyID, $sStatName = 'all')
    {

        if ($this->_checkSessionKey($sSessionKey)) {
            $aPermittedTokenStats = array(
                'token_count',
                'token_invalid',
                'token_sent',
                'token_opted_out',
                'token_completed',
                'token_screenout'
            );
            $aPermittedSurveyStats = array(
                'completed_responses',
                'incomplete_responses',
                'full_responses'
            );
            $aPermittedStats = array_merge($aPermittedSurveyStats, $aPermittedTokenStats, array('all'));
            // Check if survey exists
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                            return array('status' => 'Invalid surveyid');
            }

            if (!in_array($sStatName, $aPermittedStats)) {
                            return array('status' => 'Invalid summary key');
            }

            //Check permissions to access this survey
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'read')) {
                $aSummary = array();

                if (in_array($sStatName, $aPermittedTokenStats) || $sStatName == 'all') {
                    if ($oSurvey->hasTokensTable) {
                        $aTokenSummary = Token::model($iSurveyID)->summary();
                        if ($aTokenSummary) {
                            $aSummary['token_count'] = $aTokenSummary['count'];
                            $aSummary['token_invalid'] = $aTokenSummary['invalid'];
                            $aSummary['token_sent'] = $aTokenSummary['sent'];
                            $aSummary['token_opted_out'] = $aTokenSummary['optout'];
                            $aSummary['token_completed'] = $aTokenSummary['completed'];
                            $aSummary['token_screenout'] = $aTokenSummary['screenout'];
                        }
                    } elseif ($sStatName != 'all') {
                        return array('status' => 'No available data');
                    }
                }

                if (in_array($sStatName, $aPermittedSurveyStats) || $sStatName == 'all') {
                    if (tableExists($oSurvey->responsesTableName)) {
                        $aSummary['completed_responses'] = SurveyDynamic::model($iSurveyID)->count('submitdate is NOT NULL');
                        $aSummary['incomplete_responses'] = SurveyDynamic::model($iSurveyID)->countByAttributes(array('submitdate' => null));
                        $aSummary['full_responses'] = SurveyDynamic::model($iSurveyID)->count();
                    } elseif ($sStatName != 'all') {
                        return array('status' => 'No available data');
                    }
                }

                if ($sStatName == 'all') {
                    return $aSummary;
                } else {
                    return $aSummary[$sStatName];
                }
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /*Survey language specific functions */

    /**
     * Add a survey language (RPC function)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the Survey for which a survey participants table will be created
     * @param string $sLanguage  A valid language shortcut to add to the current Survey. If the language already exists no error will be given.
     * @return array Status=>OK when successful, otherwise the error description
     */
    public function add_language($sSessionKey, $iSurveyID, $sLanguage)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
                Yii::app()->loadHelper('surveytranslator');
                $aLanguages = getLanguageData();

                if (!isset($aLanguages[$sLanguage])) {
                    return array('status' => 'Invalid language');
                }
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                if ($sLanguage == $oSurvey->language) {
                    return array('status' => 'OK');
                }
                $aLanguages = $oSurvey->getAdditionalLanguages();
                $aLanguages[] = $sLanguage;
                $aLanguages = array_unique($aLanguages);
                $oSurvey->additional_languages = implode(' ', $aLanguages);
                try {
                    $oSurvey->save(); // save the change to database
                    $languagedetails = getLanguageDetails($sLanguage);

                    $insertdata = array(
                        'surveyls_survey_id' => $iSurveyID,
                        'surveyls_language' => $sLanguage,
                        'surveyls_title' => '',
                        'surveyls_dateformat' => $languagedetails['dateformat']
                    );
                    $setting = new SurveyLanguageSetting();
                    foreach ($insertdata as $k => $v) {
                                        $setting->$k = $v;
                    }
                    $setting->save();
                    fixLanguageConsistency($iSurveyID, $sLanguage);
                    return array('status' => 'OK');
                } catch (Exception $e) {
                    return array('status' => 'Error');
                }
            } else {
                            return array('status' => 'No permission');
            }
        }
        return null;
    }

    /**
     * Delete a language from a survey (RPC function)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the Survey for which a survey participants table will be created
     * @param string $sLanguage A valid language shortcut to delete from the current Survey. If the language does not exist in that Survey no error will be given.
     * @return array Status=>OK when successful, otherwise the error description
     */
    public function delete_language($sSessionKey, $iSurveyID, $sLanguage)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
                Yii::app()->loadHelper('surveytranslator');
                $aLanguages = getLanguageData();

                if (!isset($aLanguages[$sLanguage])) {
                    return array('status' => 'Invalid language');
                }
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                if ($sLanguage == $oSurvey->language) {
                    return array('status' => 'Cannot remove base language');
                }
                $aLanguages = $oSurvey->getAdditionalLanguages();
                $iLanguageKey = array_search($sLanguage, $aLanguages, true);
                unset($aLanguages[$iLanguageKey]);
                $oSurvey->additional_languages = implode(' ', $aLanguages);
                try {
                    $oSurvey->save(); // save the change to database
                    SurveyLanguageSetting::model()->deleteByPk(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLanguage));
                    cleanLanguagesFromSurvey($iSurveyID, $oSurvey->additional_languages);
                    return array('status' => 'OK');
                } catch (Exception $e) {
                    return array('status' => 'Error');
                }
            } else {
                            return array('status' => 'No permission');
            }
        }
        return null;
    }


    /**
     * Get survey language properties (RPC function)
     *
     * @see \SurveyLanguageSetting for available properties
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param array|null $aSurveyLocaleSettings (optional) Properties to get, default to all attributes
     * @param string|null $sLang (optional) Language to use, default to Survey->language
     * @return array in case of success The requested values
     */
    public function get_language_properties($sSessionKey, $iSurveyID, $aSurveyLocaleSettings = null, $sLang = null)
    {
        Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read')) {
                $aBasicDestinationFields = SurveyLanguageSetting::model()->tableSchema->columnNames;
                if (!empty($aSurveyLocaleSettings)) {
                    $aSurveyLocaleSettings = array_intersect($aSurveyLocaleSettings, $aBasicDestinationFields);
                } else {
                    $aSurveyLocaleSettings = $aBasicDestinationFields;
                }

                if ($sLang == null || !array_key_exists($sLang, getLanguageDataRestricted())) {
                    $sLang = $oSurvey->language;
                }


                $oSurveyLocale = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLang));
                $aResult = array();

                if (empty($aSurveyLocaleSettings)) {
                    return array('status' => 'No valid Data');
                }

                foreach ($aSurveyLocaleSettings as $sPropertyName) {
                    $aResult[$sPropertyName] = $oSurveyLocale->$sPropertyName;
                    //$aResult[$sPropertyName]=$aLangAttributes[$sPropertyName];
                }
                return $aResult;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Set survey language properties (RPC function)
     *
     * @see \SurveyLanguageSetting for available properties.
     *
     * Some properties can not be set
     * * surveyls_language
     * * surveyls_survey_id
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID  - ID of the Survey
     * @param struct $aSurveyLocaleData - An array with the particular fieldnames as keys and their values to set on that particular survey
     * @param string $sLanguage - Optional - Language to update  - if not given the base language of the particular survey is used
     * @return array in case of success 'status'=>'OK', when save successful otherwise error text.
     */
    public function set_language_properties($sSessionKey, $iSurveyID, $aSurveyLocaleData, $sLanguage = null)
    {
        Yii::app()->loadHelper("surveytranslator");
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (is_null($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (is_null($sLanguage)) {
                $sLanguage = $oSurvey->language;
            }

            if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                            return array('status' => 'Error: Invalid language');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'update')) {
                // Remove fields that may not be modified
                unset($aSurveyLocaleData['surveyls_language']);
                unset($aSurveyLocaleData['surveyls_survey_id']);

                // Remove invalid fields
                $aDestinationFields = array_flip(SurveyLanguageSetting::model()->tableSchema->columnNames);

                $aSurveyLocaleData = array_intersect_key($aSurveyLocaleData, $aDestinationFields);
                $oSurveyLocale = SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $iSurveyID, 'surveyls_language' => $sLanguage));

                $aLangAttributes = $oSurveyLocale->getAttributes();
                $aResult = array();

                if (empty($aSurveyLocaleData)) {
                                    return array('status' => 'No valid Data');
                }

                foreach ($aSurveyLocaleData as $sFieldName => $sValue) {
                    $oSurveyLocale->$sFieldName = $sValue;
                    try {
                        // save the change to database - Every single change alone - to allow for validation to work
                        $bSaveResult = $oSurveyLocale->save();
                        $aResult[$sFieldName] = $bSaveResult;
                        //unset failed values
                        if (!$bSaveResult) {
                                                $oSurveyLocale->$sFieldName = $aLangAttributes[$sFieldName];
                        }
                    } catch (Exception $e) {
                        $oSurveyLocale->$sFieldName = $aLangAttributes[$sFieldName];
                    }
                }
                $aResult['status'] = 'OK';
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /* Group specific functions */

    /**
     * Add empty group with minimum details (RPC function)
     *
     * Used as a placeholder for importing questions.
     * Returns the groupid of the created group.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to add the group
     * @param string $sGroupTitle Name of the group
     * @param string $sGroupDescription     Optional description of the group
     * @return array|int The id of the new group - Or status
     */
    public function add_group($sSessionKey, $iSurveyID, $sGroupTitle, $sGroupDescription = '')
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'update')) {
                $iSurveyID = (int) $iSurveyID;
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                if (!isset($oSurvey)) {
                    return array('status' => 'Error: Invalid survey ID');
                }

                if ($oSurvey->isActive) {
                    return array('status' => 'Error:Survey is active and not editable');
                }

                $oGroup = new QuestionGroup();
                $oGroup->sid = $iSurveyID;
                $oGroup->group_order = getMaxGroupOrder($iSurveyID);
                if (!$oGroup->save()) {
                    return array('status' => 'Creation Failed');
                }

                $oQuestionGroupL10n = new QuestionGroupL10n();
                $oQuestionGroupL10n->group_name = $sGroupTitle;
                $oQuestionGroupL10n->description = $sGroupDescription;
                $oQuestionGroupL10n->language = Survey::model()->findByPk($iSurveyID)->language;
                $oQuestionGroupL10n->gid = $oGroup->gid;

                if ($oQuestionGroupL10n->save()) {
                                    return (int) $oGroup->gid;
                } else {
                                    return array('status' => 'Creation Failed');
                }
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Delete a group from a chosen survey (RPC function)
     *
     * Returns the id of the deleted group.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey that the group belongs
     * @param int $iGroupID ID of the group to delete
     * @return array|int The ID of the deleted group or status
     */
    public function delete_group($sSessionKey, $iSurveyID, $iGroupID)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $iGroupID = (int) $iGroupID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }
        /* Find with surveyid to avoid bad parameters and don't send information on group existence
         * @see https://bugs.limesurvey.org/view.php?id=19869 */
        $oGroup = QuestionGroup::model()->findByAttributes(array('gid' => $iGroupID, 'sid' => $iSurveyID));
        if (!isset($oGroup)) {
            return array('status' => 'Error: Invalid group ID');
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'delete')) {
            return array('status' => 'No permission');
        }
        if ($oSurvey->isActive) {
            return array('status' => 'Error:Survey is active and not editable');
        }
        $depented_on = getGroupDepsForConditions($oGroup->sid, "all", $iGroupID, "by-targgid");
        if (isset($depented_on)) {
            return array('status' => 'Group with depencdencies - deletion not allowed');
        }
        $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupID);
        if ($iGroupsDeleted === 1) {
            QuestionGroup::model()->updateGroupOrder($iSurveyID);
            return (int) $iGroupID;
        } else {
            return array('status' => 'Group deletion failed');
        }

    }

    /**
     * Import a group and add to a survey (RPC function)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID The ID of the Survey that the group will belong
     * @param string $sImportData String containing the BASE 64 encoded data of a lsg,csv
     * @param string $sImportDataType  lsg,csv
     * @param string $sNewGroupName  Optional new name for the group in the survey's base language
     * @param string $sNewGroupDescription  Optional new description for the group in the survey's base language
     * @return array|integer iGroupID  - ID of the new group or status
     */
    public function import_group($sSessionKey, $iSurveyID, $sImportData, $sImportDataType, $sNewGroupName = null, $sNewGroupDescription = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $sImportData = (string) $sImportData;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'import')) {
                if ($oSurvey->isActive) {
                    return array('status' => 'Error:Survey is active and not editable');
                }

                if (!in_array($sImportDataType, array('csv', 'lsg'))) {
                    return array('status' => 'Invalid extension');
                }
                libxml_use_internal_errors(true);
                Yii::app()->loadHelper('admin.import');
                // First save the data to a temporary file
                $sFullFilePath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40) . '.' . $sImportDataType;
                file_put_contents($sFullFilePath, base64_decode(chunk_split($sImportData)));

                if (strtolower($sImportDataType) == 'lsg') {
                    if (\PHP_VERSION_ID < 80000) {
                        $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
                    }
                    $sXMLdata = file_get_contents($sFullFilePath);
                    $xml = @simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
                    if (!$xml) {
                        unlink($sFullFilePath);
                        if (\PHP_VERSION_ID < 80000) {
                            libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
                        }
                        return array('status' => 'Error: Invalid LimeSurvey group structure XML ');
                    }
                    $aImportResults = XMLImportGroup($sFullFilePath, $iSurveyID, true);
                } else {
                                    return array('status' => 'Invalid extension');
                }
                //just for symmetry!

                unlink($sFullFilePath);

                if (isset($aImportResults['fatalerror'])) {
                    if (\PHP_VERSION_ID < 80000) {
                        libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
                    }
                    return array('status' => 'Error: ' . $aImportResults['fatalerror']);
                } else {
                    $iNewgid = $aImportResults['newgid'];

                    $oGroup = QuestionGroup::model()->findByAttributes(array('gid' => $iNewgid));
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                    $oGroupL10n = $oGroup->questiongroupl10ns[$sLanguage];

                    if ($sNewGroupName != '') {
                                            $oGroupL10n->setAttribute('group_name', (string) $sNewGroupName);
                    }
                    if ($sNewGroupDescription != '') {
                                        $oGroupL10n->setAttribute('description', (string) $sNewGroupDescription);
                    }
                    try {
                        $oGroupL10n->save();
                    } catch (Exception $e) {
                        // no need to throw exception
                    }
                    if (\PHP_VERSION_ID < 80000) {
                        libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
                    }
                    return (int) $aImportResults['newgid'];
                }
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Find response IDs given a survey ID and a token (RPC function)
     *
     * @param string $sSessionKey
     * @param int $iSurveyID
     * @param string $sToken
     * @return array
     */
    public function get_response_ids($sSessionKey, $iSurveyID, $sToken)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            if (!Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
                return array('status' => 'No permission');
            }
            $iSurveyID = (int) $iSurveyID;
            $responses = SurveyDynamic::model($iSurveyID)->findAllByAttributes(array('token' => $sToken));
            $result = array();
            foreach ($responses as $response) {
                $result[] = (int) $response->id;
            }
            return $result;
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Get the properties of a group of a survey .
     *
     * Returns array of properties needed or all properties
     * @see \QuestionGroup for available properties
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iGroupID Id of the group to get properties of
     * @param array  $aGroupSettings The properties to get
     * @param string $sLanguage Optional parameter language for multilingual groups
     * @return array in case of success the requested values in array
     */
    public function get_group_properties($sSessionKey, $iGroupID, $aGroupSettings = null, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iGroupID = (int) $iGroupID;
            $oGroup = QuestionGroup::model()->with('questiongroupl10ns')->findByAttributes(array('gid' => $iGroupID));
            if (!isset($oGroup)) {
                return array('status' => 'Error: Invalid group ID');
            }

            if (Permission::model()->hasSurveyPermission($oGroup->sid, 'survey', 'read')) {
                $iSurveyID = $oGroup->sid;
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return array('status' => 'Error: Invalid language');
                }

                $aBasicDestinationFields = QuestionGroup::model()->tableSchema->columnNames;
                array_push($aBasicDestinationFields, 'group_name');
                array_push($aBasicDestinationFields, 'description');
                if (!empty($aGroupSettings)) {
                    $aGroupSettings = array_intersect($aGroupSettings, $aBasicDestinationFields);
                } else {
                    $aGroupSettings = $aBasicDestinationFields;
                }

                if (empty($aGroupSettings)) {
                    return array('status' => 'No valid Data');
                }

                foreach ($aGroupSettings as $sGroupSetting) {
                    if (isset($oGroup->$sGroupSetting)) {
                        $aResult[$sGroupSetting] = $oGroup->$sGroupSetting;
                    } elseif (
                        isset($oGroup->questiongroupl10ns[$sLanguage])
                        && isset($oGroup->questiongroupl10ns[$sLanguage]->$sGroupSetting)
                    ) {
                        $aResult[$sGroupSetting] = $oGroup->questiongroupl10ns[$sLanguage]->$sGroupSetting;
                    }
                }
                return $aResult;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /**
     * Set group properties (RPC function)
     *
     * @see \QuestionGroup for available properties and restriction
     *
     * Some attribute can not be set
     * * sid
     * * gid
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iGroupID  - ID of the Group
     * @param array $aGroupData - An array with the particular fieldnames as keys and their values to set on that particular survey
     * @return array Of succeeded and failed modifications according to internal validation.
     */
    public function set_group_properties($sSessionKey, $iGroupID, $aGroupData)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iGroupID = (int) $iGroupID;
            $oGroup = QuestionGroup::model()->with('questiongroupl10ns')->findByAttributes(array('gid' => $iGroupID));
            if (is_null($oGroup)) {
                return array('status' => 'Error: Invalid group ID');
            }
            if (Permission::model()->hasSurveyPermission($oGroup->sid, 'surveycontent', 'update')) {
                $aResult = array();
                // Remove fields that may not be modified
                unset($aGroupData['sid']);
                unset($aGroupData['gid']);

                // Backwards compatibility for L10n data
                if (!empty($aGroupData['language'])) {
                    $language = $aGroupData['language'];
                    $aGroupData['questiongroupl10ns'][$language] = array(
                        'language' => $language,
                        'group_name' => !empty($aGroupData['group_name']) ? $aGroupData['group_name'] : '',
                        'description' => !empty($aGroupData['description']) ? $aGroupData['description'] : '',
                    );
                }

                // Process L10n data
                if (!empty($aGroupData['questiongroupl10ns']) && is_array($aGroupData['questiongroupl10ns'])) {
                    $aL10nDestinationFields = array_flip(QuestionGroupL10n::model()->tableSchema->columnNames);
                    foreach ($aGroupData['questiongroupl10ns'] as $language => $aLanguageData) {
                        // Get existing L10n data or create new
                        if (isset($oGroup->questiongroupl10ns[$language])) {
                            $oQuestionGroupL10n = $oGroup->questiongroupl10ns[$language];
                        } else {
                            $oQuestionGroupL10n = new QuestionGroupL10n();
                            $oQuestionGroupL10n->gid = $iGroupID;
                            $oQuestionGroupL10n->setAttribute('language', $language);
                            $oQuestionGroupL10n->setAttribute('group_name', '');
                            $oQuestionGroupL10n->setAttribute('description', '');
                            if (!$oQuestionGroupL10n->save()) {
                                $aResult['questiongroupl10ns'][$language] = false;
                                continue;
                            }
                        }

                        // Remove invalid fields
                        $aGroupL10nData = array_intersect_key($aLanguageData, $aL10nDestinationFields);
                        if (empty($aGroupL10nData)) {
                            $aResult['questiongroupl10ns'][$language] = 'Empty group L10n data';
                            continue;
                        }

                        $aGroupL10nAttributes = $oQuestionGroupL10n->getAttributes();
                        foreach ($aGroupL10nData as $sFieldName => $sValue) {
                            $oQuestionGroupL10n->setAttribute($sFieldName, $sValue);
                            try {
                                // save the change to database - one by one to allow for validation to work
                                $bSaveResult = $oQuestionGroupL10n->save();
                                $aResult['questiongroupl10ns'][$language][$sFieldName] = $bSaveResult;
                                //unset failed values
                                if (!$bSaveResult) {
                                    $oQuestionGroupL10n->$sFieldName = $aGroupL10nAttributes[$sFieldName];
                                }
                            } catch (Exception $e) {
                                //unset values that cause exception
                                $oQuestionGroupL10n->$sFieldName = $aGroupL10nAttributes[$sFieldName];
                            }
                        }
                    }
                }

                // Remove invalid fields
                $aDestinationFields = array_flip(QuestionGroup::model()->tableSchema->columnNames);
                $aGroupData = array_intersect_key($aGroupData, $aDestinationFields);
                $aGroupAttributes = $oGroup->getAttributes();
                if (empty($aGroupData)) {
                    if (empty($aResult)) {
                        return array('status' => 'No valid Data');
                    } else {
                        return $aResult;
                    }
                }

                foreach ($aGroupData as $sFieldName => $sValue) {
                    //all dependencies this group has
                    $has_dependencies = getGroupDepsForConditions($oGroup->sid, $iGroupID);
                    //all dependencies on this group
                    $depented_on = getGroupDepsForConditions($oGroup->sid, "all", $iGroupID, "by-targgid");
                    //We do not allow groups with dependencies to change order - that would lead to broken dependencies

                    if ((isset($has_dependencies) || isset($depented_on)) && $sFieldName == 'group_order') {
                        $aResult[$sFieldName] = 'Group with dependencies - Order cannot be changed';
                        continue;
                    }
                    $oGroup->setAttribute($sFieldName, $sValue);

                    try {
                        // save the change to database - one by one to allow for validation to work
                        $bSaveResult = $oGroup->save();
                        QuestionGroup::model()->updateGroupOrder($oGroup->sid);
                        $aResult[$sFieldName] = $bSaveResult;
                        //unset failed values
                        if (!$bSaveResult) {
                                                $oGroup->$sFieldName = $aGroupAttributes[$sFieldName];
                        }
                    } catch (Exception $e) {
                        //unset values that cause exception
                        $oGroup->$sFieldName = $aGroupAttributes[$sFieldName];
                    }
                }
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /* Question specific functions */


    /**
     * Delete question from a survey (RPC function)
     *
     * Returns the id of the deleted question.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iQuestionID ID of the Question to delete
     * @return array|int ID of the deleted Question or status
     */
    public function delete_question($sSessionKey, $iQuestionID)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = Question::model()->findByPk($iQuestionID);
            if (!isset($oQuestion)) {
                return array('status' => 'Error: Invalid question ID');
            }

            $iSurveyID = $oQuestion['sid'];

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'delete')) {
                $oSurvey = Survey::model()->findByPk($iSurveyID);

                if ($oSurvey->isActive) {
                    return array('status' => 'Survey is active and not editable');
                }

                $oCondition = Condition::model()->findAllByAttributes(array('cqid' => $iQuestionID));
                if (count($oCondition) > 0) {
                    return array('status' => 'Cannot delete Question. Others rely on this question');
                }

                LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $iQuestionID);

                try {
                    $oQuestion->delete();
                    return (int) $iQuestionID;
                } catch (Exception $e) {
                    return array('status' => $e->getMessage());
                }
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /**
     * Import question (RPC function)
     *
     * Import a question from lsq file
     *
     * @access public
     * @param string $sSessionKey
     * @param int $iSurveyID The ID of the Survey that the question will belong to
     * @param int $iGroupID The ID of the Group that the question will belong to
     * @param string $sImportData String containing the BASE 64 encoded data of a lsq
     * @param string $sImportDataType  lsq
     * @param string $sMandatory (optional) Mandatory question option (default to No)
     * @param string $sNewQuestionTitle  (optional) new title for the question
     * @param string $sNewqQuestion (optional) new question text
     * @param string $sNewQuestionHelp (optional) new question help text
     * @return array|integer The id of the new question in case of success. Array if errors
     */
    public function import_question($sSessionKey, $iSurveyID, $iGroupID, $sImportData, $sImportDataType, $sMandatory = 'N', $sNewQuestionTitle = null, $sNewqQuestion = null, $sNewQuestionHelp = null)
    {
        $bOldEntityLoaderState = null;
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $iGroupID = (int) $iGroupID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update') && !Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'import')) {
            return array('status' => 'No permission');
        }
        if ($oSurvey->isActive) {
            return array('status' => 'Error:Survey is Active and not editable');
        }

        $oGroup = QuestionGroup::model()->findByAttributes(array('gid' => $iGroupID));
        if (!isset($oGroup)) {
            return array('status' => 'Error: Invalid group ID');
        }

        $sGroupSurveyID = $oGroup['sid'];
        if ($sGroupSurveyID != $iSurveyID) {
            return array('status' => 'Error: Missmatch in surveyid and groupid');
        }
        /* Check unicity of title, and set autorename to true if it's set */
        $importOptions = ['autorename' => false];
        if (!empty($sNewQuestionTitle)) {
            $countQuestionTitle = intval(Question::model()->count(
                "sid = :sid and parent_qid = 0 and title = :title",
                array(
                    ":sid" => $iSurveyID,
                    ":title" => $sNewQuestionTitle
                )
            ));
            if ($countQuestionTitle > 0) {
                return array('status' => 'Error: Question title already exist in this survey.');
            }
            /* This allow import with existing title */
            $importOptions = ['autorename' => true];
        }
        if (!strtolower($sImportDataType) == 'lsq') {
            return array('status' => 'Invalid extension');
        }

        libxml_use_internal_errors(true);
        Yii::app()->loadHelper('admin.import');

        // First save the data to a temporary file
        $sFullFilePath = App()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(40) . '.' . $sImportDataType;
        file_put_contents($sFullFilePath, base64_decode(chunk_split($sImportData)));

        if (strtolower($sImportDataType) == 'lsq') {
            if (\PHP_VERSION_ID < 80000) {
                $bOldEntityLoaderState = libxml_disable_entity_loader(true); // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
            }
            $sXMLdata = file_get_contents($sFullFilePath);
            $xml = @simplexml_load_string($sXMLdata, 'SimpleXMLElement', LIBXML_NONET);
            if (!$xml) {
                unlink($sFullFilePath);
                if (\PHP_VERSION_ID < 80000) {
                    libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
                }
                return array('status' => 'Error: Invalid LimeSurvey question structure XML ');
            }
            $aImportResults = XMLImportQuestion($sFullFilePath, $iSurveyID, $iGroupID, $importOptions);
        } else {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
            }
            return array('status' => 'Really Invalid extension'); //just for symmetry!
        }
        unlink($sFullFilePath);
        $iNewqid = 0;
        if (isset($aImportResults['fatalerror'])) {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
            }
            return array('status' => 'Error: ' . $aImportResults['fatalerror']);
        } else {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader($bOldEntityLoaderState); // Put back entity loader to its original state, to avoid contagion to other applications on the server
            }
            fixLanguageConsistency($iSurveyID);

            $iNewqid = $aImportResults['newqid'];
            /* @var array[] validation errors */
            $errors = [];
            $oQuestion = Question::model()->findByAttributes(array('sid' => $iSurveyID, 'gid' => $iGroupID, 'qid' => $iNewqid));
            if (in_array($sMandatory, array('Y', 'S', 'N'))) {
                $oQuestion->setAttribute('mandatory', $sMandatory);
            } else {
                $oQuestion->setAttribute('mandatory', 'N');
            }
            if (!empty($sNewQuestionTitle)) {
                $oQuestion->setAttribute('title', $sNewQuestionTitle);
            }

            if (!$oQuestion->save()) {
                return array(
                    'status' => 'Error when update question',
                    'errors' => $oQuestion->getErrors()
                );
            }

            $oQuestionL10ns = QuestionL10n::model()->findAll(
                "qid = :qid",
                array(':qid' => $iNewqid)
            );

            foreach ($oQuestionL10ns as $oQuestionL10n) {
                if (!empty($sNewqQuestion)) {
                    $oQuestionL10n->setAttribute('question', $sNewqQuestion);
                }
                if (!empty($sNewQuestionHelp)) {
                    $oQuestionL10n->setAttribute('help', $sNewQuestionHelp);
                }
                if (!$oQuestionL10n->save()) {
                    $errors[] = $oQuestionL10n->getErrors();
                }
            }

            if (!empty($errors)) {
                return array(
                    'status' => 'Error when update question',
                    'errors' => $errors
                );
            }

            return intval($iNewqid);
        }
    }


    /**
     * Get properties of a question in a survey.
     *
     * @see \Question for available properties.
     * Some more properties are available_answers, subquestions, attributes, attributes_lang, answeroptions, answeroptions_multiscale, defaultvalue
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iQuestionID ID of the question to get properties
     * @param array $aQuestionSettings (optional) properties to get, default to all
     * @param string $sLanguage (optional) parameter language for multilingual questions, default are \Survey->language
     * @return array The requested values
     */
    public function get_question_properties($sSessionKey, $iQuestionID, $aQuestionSettings = null, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iQuestionID = (int) $iQuestionID;
            Yii::app()->loadHelper("surveytranslator");
            $oQuestion = Question::model()->findByAttributes(array('qid' => $iQuestionID));
            if (!isset($oQuestion)) {
                return array('status' => 'Error: Invalid questionid');
            }
            $iSurveyID = $oQuestion->sid;
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
                if (empty($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return array('status' => 'Error: Invalid language');
                }

                $oQuestion = Question::model()->with('questionl10ns')
                    ->find(
                        't.qid = :qid and questionl10ns.language = :language',
                        array(':qid' => $iQuestionID, ':language' => $sLanguage)
                    );
                if (!isset($oQuestion)) {
                    return array('status' => 'Error: Invalid questionid');
                }

                $aBasicDestinationFields = Question::model()->tableSchema->columnNames;
                /* Push questionl10ns data, like 3.X for less update of external scripts */
                array_push($aBasicDestinationFields, 'question');
                array_push($aBasicDestinationFields, 'help');
                array_push($aBasicDestinationFields, 'script');
                /* Push questionl10ns data but complete */
                array_push($aBasicDestinationFields, 'questionl10ns');
                /* Other fileds */
                array_push($aBasicDestinationFields, 'available_answers');
                array_push($aBasicDestinationFields, 'subquestions');
                array_push($aBasicDestinationFields, 'attributes');
                array_push($aBasicDestinationFields, 'attributes_lang');

                if ($oQuestion->getIsDualScale()) {
                    array_push($aBasicDestinationFields, 'answeroptions_multiscale');
                } else {
                    array_push($aBasicDestinationFields, 'answeroptions');
                }

                array_push($aBasicDestinationFields, 'defaultvalue');
                if (!empty($aQuestionSettings)) {
                    $aQuestionSettings = array_intersect($aQuestionSettings, $aBasicDestinationFields);
                } else {
                    $aQuestionSettings = $aBasicDestinationFields;
                }

                if (empty($aQuestionSettings)) {
                    return array('status' => 'No valid Data');
                }

                $aResult = array();
                foreach ($aQuestionSettings as $sPropertyName) {
                    if ($sPropertyName == 'available_answers' || $sPropertyName == 'subquestions') {
                        $oSubQuestions = Question::model()->with('questionl10ns')
                            ->findAll(
                                't.parent_qid = :parent_qid and questionl10ns.language = :language',
                                array(':parent_qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'title')
                            );

                        if (count($oSubQuestions) > 0) {
                            $aData = array();
                            foreach ($oSubQuestions as $oSubQuestion) {
                                if ($sPropertyName == 'available_answers') {
                                    $aData[$oSubQuestion['title']] = array_key_exists($sLanguage, $oSubQuestion->questionl10ns) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                } else {
                                    $aData[$oSubQuestion['qid']]['title'] = $oSubQuestion['title'];
                                    $aData[$oSubQuestion['qid']]['question'] = array_key_exists($sLanguage, $oSubQuestion->questionl10ns) ? $oSubQuestion->questionl10ns[$sLanguage]->question : '';
                                    $aData[$oSubQuestion['qid']]['scale_id'] = $oSubQuestion['scale_id'];
                                }
                            }

                            $aResult[$sPropertyName] = $aData;
                        } else {
                            $aResult[$sPropertyName] = 'No available answers';
                        }
                    } elseif ($sPropertyName == 'attributes') {
                        $questionAttributeHelper = new LimeSurvey\Models\Services\QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, null, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes'] = $data;
                        } else {
                            $aResult['attributes'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'attributes_lang') {
                        $questionAttributeHelper = new LimeSurvey\Models\Services\QuestionAttributeHelper();
                        $questionAttributes = $questionAttributeHelper->getQuestionAttributesWithValues($oQuestion, $sLanguage, null, true);
                        $data = [];
                        foreach ($questionAttributes as $attributeName => $attributeData) {
                            if (!empty($attributeData['i18n'])) {
                                $data[$attributeName] = $attributeData[$sLanguage]['value'];
                            }
                        }
                        if (count($data) > 0) {
                            ksort($data, SORT_NATURAL | SORT_FLAG_CASE);
                            $aResult['attributes_lang'] = $data;
                        } else {
                            $aResult['attributes_lang'] = 'No available attributes';
                        }
                    } elseif ($sPropertyName == 'answeroptions') {
                        $oAttributes = Answer::model()->with('answerl10ns')
                            ->findAll(
                                't.qid = :qid and answerl10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage),
                                array('order' => 'sortorder')
                            );
                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['code']]['answer'] = array_key_exists($sLanguage, $oAttribute->answerl10ns) ? $oAttribute->answerl10ns[$sLanguage]->answer : '';
                                $aData[$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'answeroptions_multiscale') {
                        $oAttributes = \Answer::model()->with('answerl10ns')
                        ->findAll(
                            't.qid = :qid and answerl10ns.language = :language',
                            array(':qid' => $iQuestionID, ':language' => $sLanguage),
                            array('order' => 'sortorder')
                        );

                        if (count($oAttributes) > 0) {
                            $aData = array();
                            foreach ($oAttributes as $oAttribute) {
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['code'] = $oAttribute['code'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['answer'] = array_key_exists($sLanguage, $oAttribute->answerl10ns) ? $oAttribute->answerl10ns[$sLanguage]->answer : '';
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['assessment_value'] = $oAttribute['assessment_value'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['scale_id'] = $oAttribute['scale_id'];
                                $aData[$oAttribute['scale_id']][$oAttribute['code']]['order'] = $oAttribute['sortorder'];
                            }
                            $aResult['answeroptions'] = $aData;
                        } else {
                            $aResult['answeroptions'] = 'No available answer options';
                        }
                    } elseif ($sPropertyName == 'defaultvalue') {
                        $aResult['defaultvalue'] = DefaultValue::model()->with('defaultvaluel10ns')
                            ->find(
                                'qid = :qid AND defaultvaluel10ns.language = :language',
                                array(':qid' => $iQuestionID, ':language' => $sLanguage)
                            )
                            ->defaultvalue;
                    } elseif ($sPropertyName == 'question' || $sPropertyName == 'help' || $sPropertyName == 'script') {
                        $aResult[$sPropertyName] = $oQuestion->questionl10ns[$sLanguage]->$sPropertyName;
                    } elseif ($sPropertyName == 'questionl10ns') {
                        $aResult[$sPropertyName] = $oQuestion->questionl10ns[$sLanguage]->attributes;
                    } else {
                        $aResult[$sPropertyName] = $oQuestion->$sPropertyName;
                    }
                }
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Set question properties.
     *
     * @see \Question for available properties.
     *
     * Restricted properties:
     * * qid
     * * gid
     * * sid
     * * parent_qid
     * * language
     * * type
     * * question_order in some condition (with dependecies)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iQuestionID  - ID of the question
     * @param array $aQuestionData - An array with the particular fieldnames as keys and their values to set on that particular question
     * @param string $sLanguage Optional parameter language for multilingual questions
     * @return array Of succeeded and failed modifications according to internal validation.
     */
    public function set_question_properties($sSessionKey, $iQuestionID, $aQuestionData, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            Yii::app()->loadHelper("surveytranslator");
            $iQuestionID = (int) $iQuestionID;
            $oQuestion = Question::model()->with('questionl10ns')->findByAttributes(array('qid' => $iQuestionID));
            if (is_null($oQuestion)) {
                return array('status' => 'Error: Invalid group ID');
            }

            $iSurveyID = $oQuestion->sid;

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')) {
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted())) {
                    return array('status' => 'Error: Invalid language');
                }

                $oQuestion = Question::model()->findByAttributes(array('qid' => $iQuestionID));
                if (!isset($oQuestion)) {
                    return array('status' => 'Error: Invalid questionid');
                }

                // Backwards compatibility for L10n data
                if (!empty($aQuestionData['language'])) {
                    $language = $aQuestionData['language'];
                    $aQuestionData['questionl10ns'][$language] = array(
                        'language' => $language,
                        'question' => $aQuestionData['question'] ?? '',
                        'help' => $aQuestionData['help'] ?? '',
                    );
                }

                // Process L10n data
                if (!empty($aQuestionData['questionl10ns']) && is_array($aQuestionData['questionl10ns'])) {
                    $aL10nDestinationFields = array_flip(QuestionL10n::model()->tableSchema->columnNames);
                    foreach ($aQuestionData['questionl10ns'] as $language => $aLanguageData) {
                        // Get existing L10n data or create new
                        if (isset($oQuestion->questionl10ns[$language])) {
                            $oQuestionL10n = $oQuestion->questionl10ns[$language];
                        } else {
                            $oQuestionL10n = new QuestionL10n();
                            $oQuestionL10n->qid = $iQuestionID;
                            $oQuestionL10n->setAttribute('language', $language);
                            $oQuestionL10n->setAttribute('question', '');
                            $oQuestionL10n->setAttribute('help', '');
                            if (!$oQuestionL10n->save()) {
                                $aResult['questionl10ns'][$language] = false;
                                continue;
                            }
                        }

                        // Remove invalid fields
                        $aQuestionL10nData = array_intersect_key($aLanguageData, $aL10nDestinationFields);
                        if (empty($aQuestionL10nData)) {
                            $aResult['questionl10ns'][$language] = 'Empty question L10n data';
                            continue;
                        }

                        $aQuestionL10nAttributes = $oQuestionL10n->getAttributes();
                        foreach ($aQuestionL10nData as $sFieldName => $sValue) {
                            $oQuestionL10n->setAttribute($sFieldName, $sValue);
                            try {
                                // save the change to database - one by one to allow for validation to work
                                $bSaveResult = $oQuestionL10n->save();
                                $aResult['questionl10ns'][$language][$sFieldName] = $bSaveResult;
                                //unset failed values
                                if (!$bSaveResult) {
                                    $oQuestionL10n->$sFieldName = $aQuestionL10nAttributes[$sFieldName];
                                }
                            } catch (Exception $e) {
                                //unset values that cause exception
                                $oQuestionL10n->$sFieldName = $aQuestionL10nAttributes[$sFieldName];
                            }
                        }
                    }
                }

                // Remove fields that may not be modified
                unset($aQuestionData['qid']);
                unset($aQuestionData['gid']);
                unset($aQuestionData['sid']);
                unset($aQuestionData['parent_qid']);
                unset($aQuestionData['language']);
                unset($aQuestionData['type']);
                // Remove invalid fields
                $aDestinationFields = array_flip(Question::model()->tableSchema->columnNames);
                $aQuestionData = array_intersect_key($aQuestionData, $aDestinationFields);
                $aQuestionAttributes = $oQuestion->getAttributes();

                if (empty($aQuestionData)) {
                    if (empty($aResult)) {
                        return array('status' => 'No valid Data');
                    } else {
                        return $aResult;
                    }
                }

                foreach ($aQuestionData as $sFieldName => $sValue) {
                    //all the dependencies that this question has to other questions
                    $dependencies = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, $iQuestionID);
                    //all dependencies by other questions to this question
                    $is_criteria_question = getQuestDepsForConditions($oQuestion->sid, $oQuestion->gid, "all", $iQuestionID, "by-targqid");
                    //We do not allow questions with dependencies in the same group to change order - that would lead to broken dependencies

                    if ((isset($dependencies) || isset($is_criteria_question)) && $sFieldName == 'question_order') {
                        $aResult[$sFieldName] = 'Questions with dependencies - Order cannot be changed';
                        continue;
                    }
                    $oQuestion->setAttribute($sFieldName, $sValue);

                    try {
                        $bSaveResult = $oQuestion->save(); // save the change to database
                        Question::model()->updateQuestionOrder($oQuestion->gid);
                        $aResult[$sFieldName] = $bSaveResult;
                        //unset fields that failed
                        if (!$bSaveResult) {
                                                $oQuestion->$sFieldName = $aQuestionAttributes[$sFieldName];
                        }
                    } catch (Exception $e) {
                        //unset fields that caused exception
                        $oQuestion->$sFieldName = $aQuestionAttributes[$sFieldName];
                    }
                }
                return $aResult;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }




    /* Participant-Token specific functions */



    /**
     * Add participants to the survey.
     *
     * The parameters $aParticipantData is a 2 dimensionnal array containing needed participant data.
     * @see \Token for all available attribute,
     * @example : `[ {"email":"me@example.com","lastname":"Bond","firstname":"James"},{"email":"me2@example.com","attribute_1":"example"} ]`
     *
     * Returns the inserted data including additional new information like the Token entry ID and the token string. In case of errors in some data, return it in errors.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param array $aParticipantData Data of the participants to be added
     * @param bool $bCreateToken Optional - Defaults to true and determins if the access token automatically created
     * @return array The values added
     */
    public function add_participants($sSessionKey, $iSurveyID, $aParticipantData, $bCreateToken = true)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (is_null($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create')) {
            if (!Yii::app()->db->schema->getTable($oSurvey->tokensTableName)) {
                            return array('status' => 'No survey participants table');
            }
            $aDestinationFields = array_flip(Token::model($iSurveyID)->getMetaData()->tableSchema->columnNames);
            foreach ($aParticipantData as &$aParticipant) {
                $token = Token::create($iSurveyID);
                $token->setAttributes(array_intersect_key($aParticipant, $aDestinationFields));
                if ($bCreateToken) {
                    $token->generateToken();
                }
                if ($token->encryptSave(true)) {
                    $aParticipant = $token->getAttributes();
                } else {
                    $aParticipant["errors"] = $token->errors;
                }
            }
            return $aParticipantData;
        } else {
                    return array('status' => 'No permission');
        }
    }

    /**
     * Delete multiple participants from the survey participants table (RPC function)
     *
     * Returns the id of the deleted token
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey that the participants belong to
     * @param array $aTokenIDs ID of the tokens/participants to delete
     * @return array Result of deletion
     */
    public function delete_participants($sSessionKey, $iSurveyID, $aTokenIDs)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                            return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete')) {
                if (!tableExists("{{tokens_$iSurveyID}}")) {
                                    return array('status' => 'Error: No survey participants table');
                }

                $aResult = array();
                foreach ($aTokenIDs as $iTokenID) {
                    $iTokenID = (int) $iTokenID;
                    $token = Token::model($iSurveyID)->findByPk($iTokenID);
                    if (!isset($token)) {
                                            $aResult[$iTokenID] = 'Invalid token ID';
                    } else {
                                            $iDel = Token::model($iSurveyID)->deleteByPk($iTokenID);
                        if ($iDel > 0) {
                            $aResult[$iTokenID] = 'Deleted';
                        } else {
                            $aResult[$iTokenID] = 'Deletion went wrong';
                        }
                    }
                }
                return $aResult;
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /**
     * Get settings of a survey participant (RPC function)
     *
     * Allow to request for a specific participant. If more than one participant is returned with specified attribute(s) an error is returned.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to get token properties
     * @param array|int $aTokenQueryProperties of participant properties used to query the participant, or the token id as an integer
     * @param array $aTokenProperties The properties to get
     * @return array The requested values
     */
    public function get_participant_properties($sSessionKey, $iSurveyID, $aTokenQueryProperties, $aTokenProperties = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $surveyidExists = Survey::model()->findByPk($iSurveyID);
            if (!isset($surveyidExists)) {
                            return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
                if (!tableExists("{{tokens_$iSurveyID}}")) {
                                    return array('status' => 'Error: No survey participants table');
                }

                if (is_array($aTokenQueryProperties)) {
                    $tokenCount = Token::model($iSurveyID)->countByAttributes($aTokenQueryProperties);
                    if ($tokenCount == 0) {
                        return array('status' => 'Error: No results were found based on your attributes.');
                    } elseif ($tokenCount > 1) {
                        return array('status' => 'Error: More than 1 result was found based on your attributes.');
                    }
                    $token = Token::model($iSurveyID)->findByAttributes($aTokenQueryProperties);
                } else {
                    // If aTokenQueryProperties is not an array, but an integer
                    $iTokenID = $aTokenQueryProperties;
                    $token = Token::model($iSurveyID)->findByPk($iTokenID);
                }
                if (!isset($token)) {
                    return array('status' => 'Error: Invalid tokenid');
                }
                $token->decrypt();
                if (!empty($aTokenProperties)) {
                    $result = array_intersect_key($token->attributes, array_flip($aTokenProperties));
                } else {
                    $result = $token->attributes;
                }
                if (empty($result)) {
                    return array('status' => 'No valid Data');
                } else {
                    return $result;
                }
            } else {
                            return array('status' => 'No permission');
            }
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Set properties of a survey participant (RPC function)
     *
     * Allow to set properties about a specific participant, only one particpant can be updated.
     * @see \Token for available properties
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey that participants belong
     * @param array|int $aTokenQueryProperties of participant properties used to query the participant, or the token id as an integer
     * @param array $aTokenData Data to change
     * @return array Result of the change action
     */
    public function set_participant_properties($sSessionKey, $iSurveyID, $aTokenQueryProperties, $aTokenData)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                            return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update')) {
                if (!tableExists("{{tokens_$iSurveyID}}")) {
                                    return array('status' => 'Error: No survey participants table');
                }

                if (is_array($aTokenQueryProperties)) {
                    $oTokens = Token::model($iSurveyID)->findAllByAttributes($aTokenQueryProperties);
                    $tokenCount = count($oTokens);
                    if ($tokenCount == 0) {
                        return array('status' => 'Error: No results were found based on your attributes.');
                    } elseif ($tokenCount > 1) {
                        return array('status' => 'Error: More than 1 result was found based on your attributes.');
                    }
                    $oToken = $oTokens[0];
                } else {
                    // If aTokenQueryProperties is not an array but an integer
                    $iTokenID = $aTokenQueryProperties;
                    $oToken = Token::model($iSurveyID)->findByPk($iTokenID);
                }
                if (!isset($oToken)) {
                    return array('status' => 'Error: Invalid tokenid');
                }
                $oToken->decrypt();

                // Remove fields that may not be modified
                unset($aTokenData['tid']);

                $aBasicDestinationFields = array_flip($oToken->getTableSchema()->columnNames);
                $aTokenData = array_intersect_key($aTokenData, $aBasicDestinationFields);

                if (empty($aTokenData)) {
                    return array('status' => 'No valid Data');
                }

                $oToken->setAttributes($aTokenData, false);
                if ($oToken->encryptSave(true)) {
                    return $oToken->attributes;
                } else {
                    return array('status' => $oToken->getErrors());
                }
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }


    /**
     * Get survey groups (RPC function)
     *
     * Returns the ids and all attributes of all survey groups
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey containing the groups
     * @param string $sLanguage Optional parameter language for multilingual groups
     * @return array in case of success the list of groups
     */
    public function list_groups($sSessionKey, $iSurveyID, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
                $oGroupList = QuestionGroup::model()->with('questiongroupl10ns')->findAllByAttributes(array("sid" => $iSurveyID));
                if (count($oGroupList) == 0) {
                    return array('status' => 'No groups found');
                }

                if (is_null($sLanguage)) {
                    $sLanguage = $oSurvey->language;
                }

                foreach ($oGroupList as $oGroup) {
                    $L10ns = $oGroup->questiongroupl10ns[$sLanguage];
                    $tmp = array('id' => $oGroup->primaryKey) + $oGroup->attributes;
                    $tmp['group_name'] = $L10ns['group_name'];
                    $tmp['description'] = $L10ns['description'];
                    $tmp['language'] = $sLanguage;
                    $aData[] = $tmp;
                }
                return $aData;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Return the IDs and properties of survey participants (RPC function)
     *
     * If $bUnused is true, user will get the list of uncompleted tokens (token_return functionality).
     * Parameters iStart and iLimit are used to limit the number of results of this call.
     * Starting with version 4.3.0 it is not possible anymore to query for several IDs just using
     * an array of values - instead you have use the 'IN' operator.
     * Examples of conditions:
     *     array ('tid => 'IN','1','3','26')
     *     array('email' => 'info@example.com')
     *     array('validuntil' => array('>', '2019-01-01 00:00:00'))
     *
     * By default return each participant with basic information
     * * tid : the token id
     * * token : the token for this participant
     * * participant_info : an array with firstname, lastname and email
     * Parameter $aAttributes can be used to add more attribute in participant_info array
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to list participants
     * @param int $iStart Start id of the token list
     * @param int  $iLimit Number of participants to return
     * @param bool $bUnused If you want unused tokens, set true
     * @param bool|array $aAttributes The extented attributes that we want
     * @param array $aConditions Optional conditions to limit the list, either as a key=>value array for simple comparisons
     *              or as key=>array(operator,value[,value[...]]) using an operator.
     *              Valid operators are  ['<', '>', '>=', '<=', '=', '<>', 'LIKE', 'IN']
     *              Only the IN operator allows for several values. The same key can be used several times.
     *              All conditions are connected by AND.
     * @return array The list of tokens
     */
    public function list_participants($sSessionKey, $iSurveyID, $iStart = 0, $iLimit = 10, $bUnused = false, $aAttributes = false, $aConditions = array())
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $iStart = (int) $iStart;
            $iLimit = (int) $iLimit;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
                if (!tableExists("{{tokens_$iSurveyID}}")) {
                    return array('status' => 'Error: No survey participants table');
                }

                /** @var CDbCriteria mixed> Criteria used in final query below. */
                $oCriteria = new CDbCriteria();
                $oCriteria->order = 'tid';
                $oCriteria->limit = $iLimit;
                $oCriteria->compare('tid', '>=' . $iStart);

                $aAttributeValues = array();
                if (count($aConditions) > 0) {
                    $aConditionFields = array_flip(Token::model($iSurveyID)->getMetaData()->tableSchema->columnNames);
                    // NB: $valueOrTuple is either a value or tuple like [$operator, $value].
                    foreach ($aConditions as $columnName => $valueOrTuple) {
                        if (is_array($valueOrTuple)) {
                            /** @var string[] List of operators allowed in query. */
                            $allowedOperators = ['<', '>', '>=', '<=', '=', '<>', 'LIKE', 'IN'];
                            /** @var string */
                            $operator = $valueOrTuple[0];
                            if (!in_array($operator, $allowedOperators)) {
                                return array('status' => 'Illegal operator: ' . $operator);
                            } elseif ($operator === 'LIKE') {
                                /** @var mixed */
                                $value = $valueOrTuple[1];
                                $oCriteria->addSearchCondition($columnName, $value);
                            } elseif ($operator === 'IN') {
                                /** @var mixed */
                                $values = array_slice($valueOrTuple, 1);
                                $oCriteria->addInCondition($columnName, $values);
                            } else {
                                /** @var mixed */
                                $value = $valueOrTuple[1];
                                $oCriteria->compare($columnName, $operator . $value);
                            }
                        } elseif (is_string($valueOrTuple) || is_null($valueOrTuple)) {
                            if (array_key_exists($columnName, $aConditionFields)) {
                                $aAttributeValues[$columnName] = $valueOrTuple;
                            }
                        } else {
                            // Silent ignore?
                        }
                    }
                }
                if ($bUnused) {
                    $oTokens = Token::model($iSurveyID)->incomplete()->findAllByAttributes($aAttributeValues, $oCriteria);
                } else {
                    $oTokens = Token::model($iSurveyID)->findAllByAttributes($aAttributeValues, $oCriteria);
                }

                if (count($oTokens) == 0) {
                    return array('status' => 'No survey participants found.');
                }

                $extendedAttributes = array();
                if ($aAttributes) {
                    $aBasicDestinationFields = Token::model($iSurveyID)->tableSchema->columnNames;
                    $aTokenProperties = array_intersect($aAttributes, $aBasicDestinationFields);
                    $currentAttributes = array('tid', 'token', 'firstname', 'lastname', 'email');
                    $extendedAttributes = array_diff($aTokenProperties, $currentAttributes);
                }

                foreach ($oTokens as $token) {
                    $token->decrypt();
                    $aTempData = array(
                        'tid' => $token->primarykey,
                        'token' => $token->attributes['token'],
                        'participant_info' => array(
                            'firstname' => $token->attributes['firstname'],
                            'lastname' => $token->attributes['lastname'],
                            'email' => $token->attributes['email'],
                        )
                    );
                    foreach ($extendedAttributes as $sAttribute) {
                        $aTempData[$sAttribute] = $token->attributes[$sAttribute];
                    }
                    $aData[] = $aTempData;
                }
                return $aData;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Return the ids and info of (sub-)questions of a survey/group (RPC function)
     *
     * Returns array of ids and info.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to list questions
     * @param int $iGroupID Optional id of the group to list questions
     * @param string $sLanguage Optional parameter language for multilingual questions
     * @return array The list of questions
     */
    public function list_questions($sSessionKey, $iSurveyID, $iGroupID = null, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            Yii::app()->loadHelper("surveytranslator");
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);

            if (empty($oSurvey)) {
                return ['status' => 'Error: Invalid survey ID'];
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
                if (is_null($sLanguage)) {
                    $sLanguage = $oSurvey->language;
                }

                if (!array_key_exists($sLanguage, getLanguageDataRestricted()) or !in_array($sLanguage, $oSurvey->allLanguages)) {
                    return ['status' => 'Error: Invalid language'];
                }

                if ($iGroupID != null) {
                    $iGroupID = (int) $iGroupID;
                    $oGroup = QuestionGroup::model()->findByPk($iGroupID);

                    if (empty($oGroup)) {
                        return ['status' => 'Error: group not found'];
                    }

                    if ($oGroup->sid != $oSurvey->sid) {
                        return ['status' => 'Error: Mismatch in surveyid and groupid'];
                    } else {
                        $aQuestionList = $oGroup->allQuestions;
                    }
                } else {
                    $aQuestionList = $oSurvey->allQuestions;
                }

                if (count($aQuestionList) == 0) {
                    return ['status' => 'No questions found'];
                }

                foreach ($aQuestionList as $oQuestion) {
                    $L10ns = $oQuestion->questionl10ns[$sLanguage];
                    $aData[] = array_merge(
                        [
                        'id' => $oQuestion->primaryKey,
                        'question' => $L10ns->question,
                        'help' => $L10ns->help,
                        'language' => $sLanguage,
                        ],
                        $oQuestion->attributes
                    );
                }
                return $aData;
            } else {
                return ['status' => 'No permission'];
            }
        } else {
            return ['status' => self::INVALID_SESSION_KEY];
        }
    }

    /* Quota specific functions */

    /**
     * Add a new quota with minimum details
     *
     * This just tries to create an empty quota with the minimal settings.
     *
     * Failure status: Invalid session key, No permission, Faulty parameters, Creation Failed result
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID the Quota will belong to
     * @param string $sQuotaName The name of the new Quota
     * @param int $iLimit Quota limit
     * @param bool $bActive Whether quota is active
     * @param string $sAction ('terminate', 'confirm_terminate')
     * @param bool $bAutoloadURL Whether URL is automatically redirected if quota is triggered
     * @param string $sMessage Message to be presented to the user
     * @param string $sURL URL to be redirected to after finishing the quota
     * @param string $sURLDescription Description of the URL
     * @return array|int The id of the new quota - Or status
     */
    public function add_quota($sSessionKey, $iSurveyID, $sQuotaName, $iLimit, $bActive = true, $sAction = 'terminate', $bAutoloadURL = false, $sMessage = '', $sURL = '', $sURLDescription = '')
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $iLimit = (int) $iLimit;
            $bActive = (bool) $bActive;
            $sAction = (string) $sAction;
            $bAutoloadURL = (int) $bAutoloadURL;
            $sMessage = (string) $sMessage;
            $sURL = (string) $sURL;
            $sURLDescription = (string) $sURLDescription;

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'quotas', 'create')) {
                $oSurvey = Survey::model()->findByPk($iSurveyID);
                if (!isset($oSurvey)) {
                    return array('status' => 'Error: Invalid survey ID');
                }

                if ($iLimit < 0) {
                    return array('status' => 'Error: Invalid limit');
                }

                switch ($sAction) {
                    case 'terminate':
                        $iAction = Quota::TERMINATE_VISIBLE_QUOTA_QUESTIONS;
                        break;
                    case 'terminate_visible_hidden':
                        $iAction = Quota::TERMINATE_VISIBLE_AND_HIDDEN_QUOTA_QUESTIONS;
                        break;
                    case 'terminate_pages':
                        $iAction = Quota::TERMINATE_ALL_PAGES;
                        break;
                    case 'confirm_terminate':
                        $iAction = Quota::SOFT_TERMINATE_VISIBLE_QUOTA_QUESTIONS;
                        break;
                    default:
                        return array('status' => 'Error: Invalid quota action');
                }

                if ($sMessage == '' && ($sURL != '' || $sURLDescription != '')) {
                    return array('status' => 'Language-specific URL/description is set but no message is given');
                }

                $oDB = Yii::app()->db;
                $oTransaction = $oDB->beginTransaction();

                $oQuota = new Quota();
                $oQuota->sid = $iSurveyID;
                $oQuota->name = $sQuotaName;
                $oQuota->qlimit = $iLimit;
                $oQuota->action = $iAction;
                $oQuota->active = (int) $bActive;
                $oQuota->autoload_url = (int) $bAutoloadURL;

                if (!$oQuota->save()) {
                    return array('status' => 'Creation Failed');
                }

                if (!$sMessage == '') {
                    $oQuotaLanguageSetting = new QuotaLanguageSetting();
                    $oQuotaLanguageSetting->quotals_quota_id = $oQuota->id;
                    $oQuotaLanguageSetting->quotals_language = $oSurvey->language;
                    $oQuotaLanguageSetting->quotals_name = $sQuotaName;
                    $oQuotaLanguageSetting->quotals_message = $sMessage;
                    $oQuotaLanguageSetting->quotals_url = $sURL;
                    $oQuotaLanguageSetting->quotals_urldescrip = $sURLDescription;

                    if (!$oQuotaLanguageSetting->save()) {
                        $oTransaction->rollback();
                        return array('status' => 'Creation Failed');
                    }
                }

                $oTransaction->commit();
                return (int) $oQuota->id;

            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => 'Invalid session key');
        }
    }

    /**
     * List the quotas in a survey
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey containing the quotas
     * @return array The list of quotas
     */
    public function list_quotas($sSessionKey, $iSurveyID)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iSurveyID = (int) $iSurveyID;
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'quotas', 'read')) {
                $aQuotas = Quota::model()->findAllByAttributes(array('sid' => $iSurveyID));
                if (count($aQuotas) == 0) {
                    return array('status' => 'No quotas found');
                }

                $aData = array();
                foreach ($aQuotas as $oRow) {
                    $aData[] = array(
                        'id' => $oRow->id,
                        'name' => $oRow->name,
                        'action' => $oRow->action,
                        'limit' => $oRow->qlimit,
                        'active' => $oRow->active,
                        'autoload_url' => $oRow->autoload_url,
                    );
                }
                return $aData;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => 'Invalid session key');
        }
    }

    /**
     * Delete a quota
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iQuotaID The ID of the quota to be deleted
     * @return array|int The ID of the deleted quota or status
     */
    public function delete_quota($sSessionKey, $iQuotaID)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iQuotaID = (int) $iQuotaID;
            $oQuota = Quota::model()->findByPk($iQuotaID);
            if (!isset($oQuota)) {
                return array('status' => 'Error: Invalid quota ID');
            }

            if (Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', 'delete')) {
                $oQuota->deleteQuota(array('id' => $iQuotaID));
                return array('status' => 'OK');
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => 'Invalid session key');
        }
    }

    /**
     * Get quota attributes (RPC function)
     *
     * Get properties of a quota
     * All internal properties and completeCount of a quota are available.
     * @see \Quota for the list of available properties
     *
     * Failure status : Invalid quota ID, Invalid session key, No permission, No valid Data
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iQuotaId Quota ID
     * @param array|null $aQuotaSettings (optional) The properties to get
     * @param string $sLanguage Optional parameter language for multilingual quotas
     * @return array
     */
    public function get_quota_properties($sSessionKey, $iQuotaId, $aQuotaSettings = null, $sLanguage = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $iQuotaId = (int) $iQuotaId;
            $oQuota = Quota::model()->findByPk($iQuotaId);

            if (!isset($oQuota)) {
                return array('status' => 'Error: Invalid quota ID');
            }

            if (Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', 'read')) {
                $iSurveyID = $oQuota->sid;
                if (is_null($sLanguage)) {
                    $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
                }

                $aBasicDestinationFields = Quota::model()->tableSchema->columnNames;

                # Quota Language settings
                array_push($aBasicDestinationFields, 'quotals_message');
                array_push($aBasicDestinationFields, 'quotals_url');
                array_push($aBasicDestinationFields, 'quotals_urldescrip');

                # completeCount
                array_push($aBasicDestinationFields, 'completeCount');

                if (!empty($aQuotaSettings)) {
                    $aQuotaSettings = array_intersect($aQuotaSettings, $aBasicDestinationFields);
                } else {
                    $aQuotaSettings = $aBasicDestinationFields;
                }

                if (empty($aQuotaSettings)) {
                    return array('status' => 'No valid Data');
                }

                $aResult = array();
                foreach ($aQuotaSettings as $sPropertyName) {
                    if (isset($oQuota->$sPropertyName)) {
                        $aResult[$sPropertyName] = $oQuota->$sPropertyName;
                    } elseif (
                        isset($oQuota->languagesettings[$sLanguage])
                        && isset($oQuota->languagesettings[$sLanguage]->$sPropertyName)
                    ) {
                        $aResult[$sPropertyName] = $oQuota->languagesettings[$sLanguage]->$sPropertyName;
                    }
                }
                return $aResult;
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Set quota attributes (RPC function)
     *
     * Retuns an array containing the boolean 'success' and 'message' with either errors or Quota attributes (on success)
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iQuotaId Quota ID
     * @param array $aQuotaData Quota attributes as array eg ['active'=>1,'limit'=>100]
     * @return array ['success'=>bool, 'message'=>string]
     */
    public function set_quota_properties($sSessionKey, $iQuotaId, $aQuotaData)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            /** @var Quota $oQuota */
            $iQuotaId = (int) $iQuotaId;
            $oQuota = Quota::model()->findByPk($iQuotaId);
            if (!$oQuota) {
                return [
                    'success' => false,
                    'message' => 'Error: Invalid quota ID'
                ];
            }
            $oSurvey = $oQuota->survey;
            if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'quotas', 'update')) {
                // don't accept id & sid
                if (isset($aQuotaData['id'])) {
                    unset($aQuotaData['id']);
                };
                if (isset($aQuotaData['sid'])) {
                    unset($aQuotaData['sid']);
                };

                // accept boolean input also
                isset($aQuotaData['active']) ? $aQuotaData['active'] = (int) $aQuotaData['active'] : null;
                isset($aQuotaData['autoload_url']) ? $aQuotaData['autoload_url'] = (int) $aQuotaData['autoload_url'] : null;

                $oQuota->attributes = $aQuotaData;
                if (!$oQuota->save()) {
                    return ['success' => false, 'message' => $oQuota->errors];
                } else {
                    return ['success' => true, 'message' => $oQuota->attributes];
                }
            } else {
                return ['success' => false, 'message' => 'Denied!'];
            }
        } else {
            return ['success' => false, 'message' => self::INVALID_SESSION_KEY];
        }
    }

    /**
     * List the survey belonging to a user (RPC function)
     *
     * If user is admin he can get surveys of every user (parameter sUser) or all surveys (sUser=null)
     * Else only the surveys belonging to the user requesting will be shown.
     *
     * Returns array with
     * * `sid` the ids of survey
     * * `gsid` the surey group ids of survey
     * * `surveyls_title` the title of the survey
     * * `startdate` start date
     * * `expires` expiration date
     * * `active` if survey is active (Y) or not (!Y)
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param string|null $sUsername (optional) username to get list of surveys
     * @param integer|null $gsid  (optional) the surveys group id to get list of surveys
     * @return array In case of success the list of surveys
     */
    public function list_surveys($sSessionKey, $sUsername = null, $gsid = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $oSurvey = new Survey();
            $oSurvey->permission(Yii::app()->user->getId());
            if ($sUsername != null) {
                $aUserData = User::model()->findByAttributes(array('users_name' => (string) $sUsername));
                if (!isset($aUserData)) {
                    return array('status' => 'Invalid user');
                } else {
                    $sUid = $aUserData->attributes['uid'];
                }
                $oSurvey->permission($sUid);
            }
            if (!empty($gsid) && intval($gsid)) {
                $aUserSurveys = $oSurvey->with(
                    array(
                        'languagesettings' => array('condition' => 'surveyls_language=language'),
                        'owner'
                    )
                )->findAll("t.gsid = :gsid", [':gsid' => intval($gsid)]);
            } else {
                $aUserSurveys = $oSurvey->with(array('languagesettings' => array('condition' => 'surveyls_language=language'), 'owner'))->findAll();
            }
            if (count($aUserSurveys) == 0) {
                return array('status' => 'No surveys found');
            }

            foreach ($aUserSurveys as $oSurvey) {
                $oSurveyLanguageSettings = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $oSurvey->primaryKey, 'surveyls_language' => $oSurvey->language));
                if (!isset($oSurveyLanguageSettings)) {
                    $aSurveyTitle = '';
                } else {
                    $aSurveyTitle = $oSurveyLanguageSettings->attributes['surveyls_title'];
                }
                $aData[] = array(
                    'sid' => $oSurvey->primaryKey,
                    'gsid' => $oSurvey->gsid,
                    'surveyls_title' => $aSurveyTitle,
                    'startdate' => $oSurvey->attributes['startdate'],
                    'expires' => $oSurvey->attributes['expires'],
                    'active' => $oSurvey->attributes['active']
                );
            }
            return $aData;
        } else {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * List the survey groups belonging to a user
     *
     * If user is admin he can get survey groups of every user (parameter sUser) or all survey groups (sUser=null)
     * Else only the survey groups belonging to the user requesting will be shown.
     *
     * Returns array with survey group attributes
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param string|null $sUsername (optional) username to get list of survey groups
     * @return array In case of success the list of survey groups
     */
    public function list_survey_groups($sSessionKey, $sUsername = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            $oSurveyGroup = new SurveysGroups();
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $sOwner = Yii::app()->user->getId();
            } elseif ($sUsername != null) {
                $aUserData = User::model()->findByAttributes(array('users_name' => (string) $sUsername));
                if (!isset($aUserData)) {
                    return array('status' => 'Invalid user');
                } else {
                    $sOwner = $aUserData->attributes['uid'];
                }
            }

            if (empty($sOwner)) {
                $aUserSurveyGroups = $oSurveyGroup->findAll();
            } else {
                $aUserSurveyGroups = $oSurveyGroup->findAllByAttributes(array('owner_id' => $sOwner));
            }
            if (count($aUserSurveyGroups) == 0) {
                return array('status' => 'No survey groups found');
            }

            foreach ($aUserSurveyGroups as $oSurveyGroup) {
                $aData[] = $oSurveyGroup->attributes;
            }
            return $aData;
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

/**
 * Get list the ids and info of administration user(s) (RPC function)
 *
 * Returns array of ids and info.
 *
 * Failure status : Invalid user id, Invalid username, No users found, Invalid session key, Permission denied (super admin is required)
 *
 * @param string $sSessionKey Auth credentials
 * @param int $uid Optional; ID of the user
 * @param string $username Optional; name of the user
 * @return array The list of users in case of success
 */
    public function list_users($sSessionKey = null, $uid = null, $username = null)
    {
        if ($this->_checkSessionKey($sSessionKey)) {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $users = null;
                if ($uid) {
                    $uid = (int) $uid;
                    $user = User::model()->findByPk($uid);
                    if (!$user) {
                        return array('status' => 'Invalid user ID');
                    }
                    $users = array($user);
                } elseif ($username) {
                    $user = User::model()->findByUsername($username);
                    if (!$user) {
                        return array('status' => 'Invalid username');
                    }
                    $users = array($user);
                } else {
                    $users = User::model()->findAll();
                }

                if (count($users) == 0) {
                    return array('status' => 'No users found');
                }

                foreach ($users as $user) {
                    $attributes = $user->attributes;
                    $attributes['permissions'] = array();
                    foreach ($user->permissions as $permission) {
                        $attributes['permissions'][] = $permission->attributes;
                    }
                    unset($attributes['password']);
                    $data[] = $attributes;
                }
                return $data;
            } else {
                return array('status' => 'Permission denied.');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Activate survey participants (RPC function)
     *
     * Initialise the survey participant table of a survey where new participant tokens may be later added.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param integer $iSurveyID ID of the Survey where a survey participants table will be created for
     * @param array $aAttributeFields  An array of integer describing any additional attribute fields
     * @return array Status=>OK when successful, otherwise the error description
     */
    public function activate_tokens($sSessionKey, $iSurveyID, $aAttributeFields = array())
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (is_null($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }
        if (
            /* Same test Tokens->newtokentable */
            !Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update') &&
            !Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create')
        ) {
            return array('status' => 'No permission');
        }
        if (is_array($aAttributeFields) && count($aAttributeFields) > 0) {
            foreach ($aAttributeFields as &$sField) {
                $sField = intval($sField);
                $sField = 'attribute_' . $sField;
            }
            $aAttributeFields = array_unique($aAttributeFields);
        }
        Yii::app()->loadHelper('admin/token');
        if (Token::createTable($iSurveyID, $aAttributeFields)) {
            return array('status' => 'OK');
        } else {
            return array('status' => 'Survey participants table could not be created');
        }

    }

    /**
     * Send e-mails to registered participants in a survey (RPC function)
     *
     * Returns array of results of sending
     *
     * Default behaviour is to send register emails to not invited, not reminded, not completed and in valid frame date participant.
     *
     * $overrideAllConditions replaces this default conditions for selecting the participants. A typical use case is to select only one participant
     * ````
     * $overrideAllConditions = Array();
     * $overrideAllConditions[] = 'tid = 2';
     * $response = $myJSONRPCClient->mail_registered_participants( $sessionKey, $survey_id, $overrideAllConditions );
     * ````
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey that participants belong
     * @param array $overrideAllConditions replace the default conditions
     * @return array Result of the action
     */
    public function mail_registered_participants($sSessionKey, $iSurveyID, $overrideAllConditions = array())
    {
        Yii::app()->loadHelper('admin/token');
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update')) {
            if (!tableExists("{{tokens_$iSurveyID}}")) {
                return array('status' => 'Error: No survey participants table');
            }

            $command = new CDbCriteria();
            $command->condition = '';
            if (count($overrideAllConditions)) {
                foreach ($overrideAllConditions as $condition) {
                    $command->addCondition($condition);
                }
            } else {
                $sNow = date("Y-m-d H:i:s", strtotime((string) Yii::app()->getConfig('timeadjust'), strtotime(date("Y-m-d H:i:s"))));
                $command->addCondition('usesleft > 0');
                $command->addCondition("sent = 'N'");
                $command->addCondition("remindersent = 'N'");
                $command->addCondition("(completed ='N') or (completed='')");
                $command->addCondition("validfrom is null OR validfrom < '{$sNow}'");
                $command->addCondition("validuntil is null OR validuntil > '{$sNow}'");
                $command->addCondition("emailstatus = 'OK'");
            }
            $command->order = 'tid';

            $aAllTokens = Token::model($iSurveyID)->findAll($command);
            $iAllTokensCount = count($aAllTokens);
            unset($aAllTokens);

            $iMaxEmails = (int) Yii::app()->getConfig("maxemails");
            $command->limit = $iMaxEmails;
            $aResultTokens = Token::model($iSurveyID)->findAll($command);

            if (empty($aResultTokens)) {
                return array('status' => 'Error: No candidate tokens');
            }

            foreach ($aResultTokens as $key => $oToken) {
                $oToken->decrypt();
                //pattern taken from php_filter_validate_email PHP_5_4/ext/filter/logical_filters.c
                /* @todo : use LimeMailer::validateAddresses */
                $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

                //pattern to split in case of multiple emails for a participant
                $emailsToCheck = preg_split("/(,|;)/", (string) $oToken['email']);

                //Loop through each email and validate it
                foreach ($emailsToCheck as $emailToCheck) {
                    if (preg_match($pattern, (string) $emailToCheck) !== 1) {
                        unset($aResultTokens[$key]);
                        //subtract from 'left to send'
                        $iAllTokensCount--;
                        break;
                    }
                }
            }

            if (empty($aResultTokens)) {
                return array('status' => 'Error: No candidate tokens');
            }

            $aResult = emailTokens($iSurveyID, $aResultTokens, 'register');
            $iLeft = $iAllTokensCount - count($aResultTokens);
            $aResult['status'] = $iLeft . " left to send";

            return $aResult;
        } else {
            return array('status' => 'No permission');
        }
    }

    /**
     * Invite participants in a survey (RPC function)
     *
     * Returns array of results of sending.
     * The sending stops on the first error (ie. when a token is invalid).
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the survey that participants belong
     * @param array $aTokenIds Ids of the participant to invite
     * @param bool $bEmail Send only pending invites (TRUE) or resend invites only (FALSE)
     * @param bool $continueOnError Don't stop on first invalid participant
     * @return array Result of the action
     */
    public function invite_participants($sSessionKey, $iSurveyID, $aTokenIds = null, $bEmail = true, $continueOnError = false)
    {
        Yii::app()->loadHelper('admin/token');
        if (!$this->_checkSessionKey($sSessionKey)) {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update')) {
            if (!tableExists("{{tokens_$iSurveyID}}")) {
                return array('status' => 'Error: No survey participants table');
            }

            $iMaxEmails = (int) Yii::app()->getConfig("maxemails");
            $SQLemailstatuscondition = "emailstatus = 'OK'";

            $oTokens = TokenDynamic::model($iSurveyID);
            $aResultTokens = $oTokens->findUninvited($aTokenIds, $iMaxEmails, $bEmail, $SQLemailstatuscondition);
            $aAllTokens = $oTokens->findUninvitedIDs(false, 0, true, $SQLemailstatuscondition);
            $iAllTokensCount = count($aAllTokens);
            unset($aAllTokens);
            if (empty($aResultTokens)) {
                return array('status' => 'Error: No candidate tokens');
            }

            foreach ($aResultTokens as $key => $oToken) {
                //pattern taken from php_filter_validate_email PHP_5_4/ext/filter/logical_filters.c
                $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

                //pattern to split in case of multiple emails for a participant
                $emailsToCheck = preg_split("/(,|;)/", (string) $oToken['email']);

                //Loop through each email and validate it
                foreach ($emailsToCheck as $emailToCheck) {
                    if (preg_match($pattern, (string) $emailToCheck) !== 1) {
                        unset($aResultTokens[$key]);
                        break;
                    }
                }
            }

            if (empty($aResultTokens)) {
                return array('status' => 'Error: No candidate tokens');
            }
            $aResult = emailTokens($iSurveyID, $aResultTokens, 'invite', $continueOnError);
            $iLeft = $iAllTokensCount - count($aResultTokens);
            $aResult['status'] = $iLeft . " left to send";

            return $aResult;
        } else {
            return array('status' => 'No permission');
        }
    }


    /**
     * Send a reminder to participants in a survey (RPC function)
     *
     * Returns array of results of sending
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey that participants belong
     * @param int $iMinDaysBetween (optional) parameter days from last reminder
     * @param int $iMaxReminders (optional) parameter Maximum reminders count
     * @param array $aTokenIds Ids of the participant to remind (optional filter)
     * @param bool $continueOnError Don't stop on first invalid participant
     * @return array in case of success array of result of each email send action and count of invitations left to send in status key
     */
    public function remind_participants($sSessionKey, $iSurveyID, $iMinDaysBetween = null, $iMaxReminders = null, $aTokenIds = false, $continueOnError = false)
    {
        Yii::app()->loadHelper('admin/token');
        if (!$this->_checkSessionKey($sSessionKey)) {
                    return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!isset($oSurvey)) {
                    return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update')) {
            $timeadjust = Yii::app()->getConfig("timeadjust");

            if (!tableExists("{{tokens_$iSurveyID}}")) {
                            return array('status' => 'Error: No survey participants table');
            }

            $SQLemailstatuscondition = "emailstatus = 'OK'";
            $SQLremindercountcondition = '';
            $SQLreminderdelaycondition = '';
            $iMaxEmails = (int) Yii::app()->getConfig("maxemails");

            if (!is_null($iMinDaysBetween)) {
                $iMinDaysBetween = (int) $iMinDaysBetween;
                $compareddate = dateShift(date("Y-m-d H:i:s", time() - 86400 * $iMinDaysBetween), "Y-m-d H:i", $timeadjust);
                $SQLreminderdelaycondition = " ((remindersent = 'N' AND sent < '" . $compareddate . "')  OR  (remindersent < '" . $compareddate . "'))";
            }

            if (!is_null($iMaxReminders)) {
                $iMaxReminders = (int) $iMaxReminders;
                $SQLremindercountcondition = "remindercount < " . $iMaxReminders;
            }

            $oTokens = TokenDynamic::model($iSurveyID);
            $aAllTokens = $oTokens->findUninvitedIDs(false, 0, false, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $iAllTokensCount = count($aAllTokens);
            unset($aAllTokens); // save some memory before the next query

            $aResultTokens = $oTokens->findUninvited($aTokenIds, $iMaxEmails, false, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);

            if (empty($aResultTokens)) {
                            return array('status' => 'Error: No candidate tokens');
            }

            $aResult = emailTokens($iSurveyID, $aResultTokens, 'remind', $continueOnError);

            $iLeft = $iAllTokensCount - count($aResultTokens);
            $aResult['status'] = $iLeft . " left to send";
            return $aResult;
        } else {
                    return array('status' => 'No permission');
        }
    }


    /* Response specific functions */


    /**
     * Add a response to the survey responses collection.
     *
     * Returns the id of the inserted survey response
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to insert responses
     * @param array $aResponseData The actual response
     * @return int|array The response ID or an array with status message (can include result_id)
     * @todo Need to clean up return array, especially the case when response was added but file not uploaded.
     * @todo See discussion: https://bugs.limesurvey.org/view.php?id=13794
     */
    public function add_response($sSessionKey, $iSurveyID, $aResponseData)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        if (is_null($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'create')) {
            if (!Yii::app()->db->schema->getTable($oSurvey->responsesTableName)) {
                return array('status' => 'No survey response table');
            }

            //set required values if not set

            // @todo: Some of this is part of the validation and should be done in the model instead
            if (array_key_exists('submitdate', $aResponseData) && empty($aResponseData['submitdate'])) {
                unset($aResponseData['submitdate']);
            } elseif (!isset($aResponseData['submitdate'])) {
                $aResponseData['submitdate'] = date("Y-m-d H:i:s");
            }
            if (!isset($aResponseData['startlanguage'])) {
                $aResponseData['startlanguage'] = $oSurvey->language;
            }

            if ($oSurvey->isDateStamp) {
                if (array_key_exists('datestamp', $aResponseData) && empty($aResponseData['datestamp'])) {
                    unset($aResponseData['datestamp']);
                } elseif (!isset($aResponseData['datestamp'])) {
                    $aResponseData['datestamp'] = date("Y-m-d H:i:s");
                }
                if (array_key_exists('startdate', $aResponseData) && empty($aResponseData['startdate'])) {
                    unset($aResponseData['startdate']);
                } elseif (!isset($aResponseData['startdate'])) {
                    $aResponseData['startdate'] = date("Y-m-d H:i:s");
                }
            }

            SurveyDynamic::sid($iSurveyID);
            $survey_dynamic = new SurveyDynamic();
            $aBasicDestinationFields = $survey_dynamic->tableSchema->columnNames;
            $aResponseData = array_intersect_key($aResponseData, array_flip($aBasicDestinationFields));
            $survey_dynamic->setAttributes($aResponseData, false);
            $survey_dynamic->encryptSave();

            if ($survey_dynamic->id) {
                $result_id = $survey_dynamic->id;
                $oResponse = Response::model($iSurveyID)->findByAttributes(array('id' => $result_id))->decrypt();
                foreach ($oResponse->getFiles() as $aFile) {
                    $sUploadPath = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/";
                    $sFileRealName = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/" . $aFile['filename'];
                    $sFileTempName = Yii::app()->getConfig('tempdir') . "/upload/" . $aFile['filename'];

                    if (!file_exists($sFileRealName)) {
                        if (!is_dir($sUploadPath)) {
                            mkdir($sUploadPath, 0777, true);
                        }

                        if (!rename($sFileTempName, $sFileRealName)) {
                            return array(
                                'status'    => 'Unable to move files ' . $sFileTempName . ' ' . $sFileRealName,
                                'result_id' => $result_id
                            );
                        }
                    }
                }

                return $result_id;
            } else {
                return array('status' => 'Unable to add response');
            }
        } else {
            return array('status' => 'No permission');
        }
    }

    /**
     * Update a response in a given survey.
     *
     * Routine supports only single response updates.
     * Response to update will be identified either by the response id, or the token if response id is missing.
     * Routine is only applicable for active surveys with alloweditaftercompletion = Y.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the Survey to update response
     * @param array $aResponseData The actual response
     * @return array|boolean TRUE(bool) on success. Array with error status on failure.
     */
    public function update_response($sSessionKey, $iSurveyID, $aResponseData)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (is_null($oSurvey)) {
            return array('status' => 'Invalid survey ID');
        }
        if (!$oSurvey->isActive) {
            return array('status' => 'Survey is not active');
        }

        if (!$oSurvey->isAllowEditAfterCompletion) {
            return array('status' => 'Survey does not allow edit after completion');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'update')) {
            if (!Yii::app()->db->schema->getTable($oSurvey->responsesTableName)) {
                return array('status' => 'No survey response table');
            }

            if (
                !isset($aResponseData['id'])
                && !isset($aResponseData['token'])
            ) {
                return array('status' => 'Missing response identifier (id|token)');
            }

            SurveyDynamic::sid($iSurveyID);
            $oSurveyDynamic = new SurveyDynamic();

            if (isset($aResponseData['id'])) {
                $aResponses = $oSurveyDynamic->findAllByPk((int) $aResponseData['id']);
            } else {
                $aResponses = $oSurveyDynamic->findAllByAttributes(array('token' => $aResponseData['token']));
            }

            if (empty($aResponses)) {
                return array('status' => 'No matching Response');
            }
            if (count($aResponses) > 1) {
                return array('status' => 'More then one matching response, updateing multiple responses at once is not supported');
            }

            $aBasicDestinationFields = $oSurveyDynamic->tableSchema->columnNames;
            $aInvalidFields = array_diff_key($aResponseData, array_flip($aBasicDestinationFields));
            if (count($aInvalidFields) > 0) {
                return array('status' => 'Invalid Column names supplied: ' . implode(', ', array_keys($aInvalidFields)));
            }

            unset($aResponseData['token']);

            foreach ($aResponseData as $sAtributeName => $value) {
                $aResponses[0]->setAttribute($sAtributeName, $value);
            }

            $bResult = $aResponses[0]->encryptSave(true);

            if ($bResult) {
                return $bResult;
            } else {
                return array('status' => 'Unable to edit response');
            }
        } else {
            return array('status' => 'No permission');
        }
    }

    /**
     * Delete a response in a given survey using its Id
     *
     * RPC Routine to delete responses of particular id in a survey.
     * Returns array
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the survey that participants belong
     * @param int $iResponseID Id of the response to delete
     * @return array Result of the change action
     */
    public function delete_response($sSessionKey, $iSurveyID, $iResponseID)
    {
         // check sessionKey is valid or not
        if ($this->_checkSessionKey($sSessionKey)) {
                $oSurvey = Survey::model()->findByPk($iSurveyID);
            if (!isset($oSurvey)) {
                  return array('status' => 'Error: Invalid survey ID');
            }

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'delete')) {
                // get response id from response table using ID
                $Response = Response::model($iSurveyID)->findByPk($iResponseID);
                if ($Response) {
                    // delete the files and timings and row
                    if ($Response->delete()) {
                        return array($iResponseID => 'deleted');
                    }
                    return array('status' => 'Response not deleted for unknow reason');
                } else {
                    return array('status' => 'Response Id not found');
                }
            } else {
                return array('status' => 'No permission');
            }
        } else {
            return array('status' => self::INVALID_SESSION_KEY);
        }
    }

    /**
     * Uploads one file to be used later.
     *
     * Returns the metadata on success.
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey to insert file
     * @param string $sFieldName the Field to upload file
     * @param string $sFileName the uploaded file name
     * @param string $sFileContent the uploaded file content encoded as BASE64
     * @return array The file metadata with final upload path or error description
     */
    public function upload_file($sSessionKey, $iSurveyID, $sFieldName, $sFileName, $sFileContent)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        if (is_null($oSurvey)) {
            return array('status' => 'Error: Invalid survey ID');
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'create')) {
            if (!Yii::app()->db->schema->getTable('{{responses_' . $iSurveyID . '}}')) {
                return array('status' => 'No survey response table');
            }
        } else {
            return array('status' => 'No permission');
        }

        $tempdir = Yii::app()->getConfig("tempdir");

        $sTempUploadDir = $tempdir . '/upload/';
        if (!file_exists($sTempUploadDir)) {
            if (!mkdir($sTempUploadDir)) {
                return array('status' => 'Can not make temporary upload directory');
            }
        }

        $aFieldMap = createFieldMap($oSurvey, 'short', false, false, Yii::app()->getConfig('defaultlang'));
        if (!isset($aFieldMap[$sFieldName])) {
            return array('status' => 'Can not obtain field map');
        }

        $aAttributes = QuestionAttribute::model()->getQuestionAttributes($aFieldMap[$sFieldName]['qid']);

        $iFileUploadTotalSpaceMB = Yii::app()->getConfig('iFileUploadTotalSpaceMB');

        $maxfilesize = (int) $aAttributes['max_filesize'];
        $allowed_filetypes = $aAttributes['allowed_filetypes'];
        $valid_extensions_array = explode(",", (string) $allowed_filetypes);
        $valid_extensions_array = array_map('trim', $valid_extensions_array);

        $pathinfo = pathinfo($sFileName);
        $ext = strtolower($pathinfo['extension']);

        // check to see that this file type is allowed
        if (!in_array($ext, $valid_extensions_array)) {
            return array('status' => 'The extension ' . $ext . ' is not valid. Valid extensions are: ' . $allowed_filetypes);
        }

        // This also accounts for BASE64 overhead
        $size = (0.001 * 3 * strlen($sFileContent)) / 4;

        $randfilename = 'futmp_' . randomChars(15) . '_' . $pathinfo['extension'];
        $randfileloc = $sTempUploadDir . $randfilename;

        if ($size > $maxfilesize) {
            return array('status' => sprintf('Sorry, this file is too large. Only files up to %s KB are allowed.', $maxfilesize));
        }

        if ($iFileUploadTotalSpaceMB > 0 && ((calculateTotalFileUploadUsage() + ($size / 1024 / 1024)) > $iFileUploadTotalSpaceMB)) {
            return array('status' => 'Not enough free space available');
        }

        $uploaded = file_put_contents($randfileloc, base64_decode($sFileContent));
        if ($uploaded === false) {
            return array('status' => 'Unable to write file');
        }

        return array(
            "success"   => true,
            "size"      => $size,
            "name"      => rawurlencode(basename($sFileName)),
            "ext"       => $ext,
            "filename"  => $randfilename,
            "msg"       => gT("The file has been successfully uploaded.")
        );
    }

    /**
     * Export responses in base64 encoded string
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param string $sDocumentType any format available by plugins (for example : pdf, csv, xls, doc, json)
     * @param string $sLanguageCode (optional) The language to be used
     * @param string $sCompletionStatus (optional) 'complete','incomplete' or 'all' - defaults to 'all'
     * @param string $sHeadingType (optional) 'code','full' or 'abbreviated' Optional defaults to 'code'
     * @param string $sResponseType (optional)'short' or 'long' Optional defaults to 'short'
     * @param integer $iFromResponseID (optional) Frpm response id
     * @param integer $iToResponseID (optional) To response id
     * @param array $aFields (optional) Name the fields to export
     * @param array $aAdditionalOptions (optional) Addition options for export, @see \FormattingOptions, example : 'convertY', 'convertN', 'nValue', 'yValue', 'headerSpacesToUnderscores', 'useEMCode'
     * @return array|string On success: Requested file as base 64-encoded string. On failure array with error information
     * */
    public function export_responses($sSessionKey, $iSurveyID, $sDocumentType, $sLanguageCode = null, $sCompletionStatus = 'all', $sHeadingType = 'code', $sResponseType = 'short', $iFromResponseID = null, $iToResponseID = null, $aFields = null, $aAdditionalOptions = null)
    {
        $iSurveyID = (int) $iSurveyID;
        $survey = Survey::model()->findByPk($iSurveyID);

        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export')) {
            return array('status' => 'No permission');
        }
        Yii::app()->loadHelper('admin/exportresults');
        if (!tableExists($survey->responsesTableName)) {
            return array('status' => 'No Data, survey table does not exist.');
        }
        if (!($maxId = SurveyDynamic::model($iSurveyID)->getMaxId(null, true))) {
            return array('status' => 'No Data, could not get max id.');
        }
        if (!empty($sLanguageCode) && !in_array($sLanguageCode, $survey->getAllLanguages())) {
            return array('status' => 'Language code not found for this survey.');
        }

        if (empty($sLanguageCode)) {
            $sLanguageCode = $survey->language;
        }
        if (is_null($aFields)) {
            $aFields = array_keys(createFieldMap($survey, 'full', true, false, $sLanguageCode));
        }
        if ($sDocumentType == 'xls') {
            // Cut down to the first 255 fields
            $aFields = array_slice($aFields, 0, 255);
        }
        $oFormattingOptions = new FormattingOptions();

        if (is_array($aAdditionalOptions)) {
            foreach ($aAdditionalOptions as $option => $value) {
                if (property_exists($oFormattingOptions, $option )){
                    $oFormattingOptions->$option = $value;
                }
            }
            /* fix some specific option (GUI use 1 and 2, but default for remote control are set to Y and N before) */
            if (!empty($oFormattingOptions->convertY) && !isset($oFormattingOptions->yValue)) {
                $oFormattingOptions->yValue = 'Y';
            }
            if (!empty($oFormattingOptions->convertN) && !isset($oFormattingOptions->nValue)) {
                $oFormattingOptions->nValue = 'N';
            }
        }

        if ($iFromResponseID != null) {
            $oFormattingOptions->responseMinRecord = (int) $iFromResponseID;
        } else {
            $oFormattingOptions->responseMinRecord = 1;
        }

        if ($iToResponseID != null) {
            $oFormattingOptions->responseMaxRecord = (int) $iToResponseID;
        } else {
            $oFormattingOptions->responseMaxRecord = $maxId;
        }

        $oFormattingOptions->selectedColumns = $aFields;
        $oFormattingOptions->responseCompletionState = $sCompletionStatus;
        $oFormattingOptions->headingFormat = $sHeadingType;
        $oFormattingOptions->answerFormat = $sResponseType;
        $oFormattingOptions->output = 'file';

        $oExport = new ExportSurveyResultsService();
        $sTempFile = $oExport->exportResponses($iSurveyID, $sLanguageCode, $sDocumentType, $oFormattingOptions, '');
        return new BigFile($sTempFile, true, 'base64');
    }

    /**
     * Export token response in a survey.
     *
     * Returns the requested file as base64 encoded string
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID ID of the Survey
     * @param string $sDocumentType pdf, csv, xls, doc, json
     * @param array|string $aTokens The tokens (or single token) for which responses needed
     * @param string $sLanguageCode The language to be used
     * @param string $sCompletionStatus Optional 'complete','incomplete' or 'all' - defaults to 'all'
     * @param string $sHeadingType 'code','full' or 'abbreviated' Optional defaults to 'code'
     * @param string $sResponseType 'short' or 'long' Optional defaults to 'short'
     * @param array $aFields Optional Selected fields
     * @return array|string On success: Requested file as base 64-encoded string. On failure array with error information
     *
     */
    public function export_responses_by_token($sSessionKey, $iSurveyID, $sDocumentType, $aTokens, $sLanguageCode = null, $sCompletionStatus = 'all', $sHeadingType = 'code', $sResponseType = 'short', $aFields = null)
    {
        $iSurveyID = (int) $iSurveyID;
        $survey = Survey::model()->findByPk($iSurveyID);
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }
        Yii::app()->loadHelper('admin/exportresults');
        if (!tableExists($survey->responsesTableName)) {
            return array('status' => 'No Data, survey table does not exist.');
        }
        if (!($maxId = SurveyDynamic::model($iSurveyID)->getMaxId())) {
            return array('status' => 'No Data, could not get max id.');
        }
        if (!empty($sLanguageCode) && !in_array($sLanguageCode, $survey->getAllLanguages())) {
            return array('status' => 'Language code not found for this survey.');
        }
        if (is_array($aTokens)) {
            if (count($aTokens) == 0) {
                return array('status' => 'No Data, empty tokens array parameter');
            } else {
                $aTokensQuoted = Array();
                foreach ($aTokens as $token) {
                    array_push($aTokensQuoted, App()->db->quoteValue("$token"));
                }
                $tokenFilter = " IN (" . implode(",", $aTokensQuoted) . ")";
            }
        } else {
            if (!SurveyDynamic::model($iSurveyID)->findByAttributes(array('token' => $aTokens))) {
                return array('status' => 'No Response found for Token');
            }
            $tokenFilter = "=" . App()->db->quoteValue("$aTokens");
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export')) {
            return array('status' => 'No permission');
        }
        if (empty($sLanguageCode)) {
            $sLanguageCode = $survey->language;
        }
        if (is_null($aFields)) {
            $aFields = array_keys(createFieldMap($survey, 'full', true, false, $sLanguageCode));
        }
        $oFormattingOptions = new FormattingOptions();
        $oFormattingOptions->responseMinRecord = 1;
        $oFormattingOptions->responseMaxRecord = $maxId;
        $oFormattingOptions->selectedColumns = $aFields;
        $oFormattingOptions->responseCompletionState = $sCompletionStatus;
        $oFormattingOptions->headingFormat = $sHeadingType;
        $oFormattingOptions->answerFormat = $sResponseType;
        $oFormattingOptions->output = 'file';
        $oExport = new ExportSurveyResultsService();
        $sTableName = Yii::app()->db->tablePrefix . 'responses_' . $iSurveyID;
        $sTempFile = $oExport->exportResponses($iSurveyID, $sLanguageCode, $sDocumentType, $oFormattingOptions, "{$sTableName}.token" . $tokenFilter);
        return new BigFile($sTempFile, true, 'base64');
    }


    /**
     * Obtain all uploaded files for all responses
     *
     * @access public
     *
     * @param string  $sSessionKey  Auth credentials
     * @param int     $iSurveyID    ID of the Survey
     * @param string  $sToken       Response token
     * @param int     $responseId   Response ID
     *
     * @return array On success: array containing all uploads of the specified response
     *               On failure: array with error information
     */
    public function get_uploaded_files($sSessionKey, $iSurveyID, $sToken, $responseId = null)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }

        $iSurveyID = (int) $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (!$oSurvey->hasResponsesTable) {
            return array('status' => 'No Data, survey table does not exist.');
        }

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'read')) {
            return array('status' => 'No permission');
        }

        if (empty($responseId) and empty($sToken)) {
            return ['status' => 'Invalid arguments: both Token and Reponse ID are empty'];
        }
        $criteria = new CDbCriteria();
        if (!empty($responseId)) {
            $criteria->compare('id', $responseId);
        }
        if (!empty($sToken)) {
            $criteria->compare('token', $sToken);
        }
        $oResponses = Response::model($iSurveyID)->findAll($criteria);
        if (empty($oResponses)) {
            return ['status' => 'Could not find response for given token or response id'];
        }

        $uploaded_files = array();
        foreach ($oResponses as $key => $oResponse) {
            foreach ($oResponse->getFiles() as $aFile) {
                $sFileRealName = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/" . $aFile['filename'];

                if (!file_exists($sFileRealName)) {
                    return array('status' => 'Could not find uploaded files');
                }

                $uploaded_files[$aFile['filename']] = array(
                    'meta'    => $aFile,
                    'content' => base64_encode(file_get_contents($sFileRealName))
                );
            }
        }

        return $uploaded_files;
    }

    /**
     * Get survey fieldmap (RPC function)
     *
     * Returns the requested survey's fieldmap in an array
     *
     * @access public
     * @param string $sessionKey Auth credentials
     * @param int $surveyId ID of the Survey
     * @param string $language (optional) language of the survey to use (default from Survey)
     * @return array
     */
    public function get_fieldmap($sessionKey, $surveyId, $language = null)
    {
        if (!$this->_checkSessionKey($sessionKey)) {
            return ['status' => self::INVALID_SESSION_KEY];
        }
        $surveyId = (int) $surveyId;
        $survey = Survey::model()->findByPk($surveyId);
        if (!isset($survey)) {
            return ['status' => 'Error: Invalid survey ID'];
        }
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'read')) {
            return ['status' => 'No permission'];
        }
        if (empty($language) || !in_array($language, $survey->allLanguages)) {
            $language = $survey->language;
        }
        // Get the fieldmap
        $fieldmap = createFieldMap($survey, 'full', false, false, $language);
        if (empty($fieldmap)) {
            return ['status' => 'Can not obtain field map'];
        }
        return $fieldmap;
    }

    /**
     * Login with username and password
     *
     * @access protected
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return bool|string
     */
    protected function _doLogin($sUsername, $sPassword, $sPlugin)
    {
        /* @var $identity LSUserIdentity */
        $identity = new LSUserIdentity($sUsername, $sPassword);
        $identity->setPlugin($sPlugin);
        $event = new PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $sPlugin);
        $event->set('username', $sUsername);
        $event->set('password', $sPassword);
        App()->getPluginManager()->dispatchEvent($event, array($sPlugin));
        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
                // don't return an empty string
                return $identity->errorMessage;
            }
            return false;
        } else {
            return true;
        }
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
        $aUserData = User::model()->findByAttributes(array('users_name' => (string) $username))->attributes;

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
        foreach ($session as $k => $v) {
            Yii::app()->session[$k] = $v;
        }
        Yii::app()->user->setId($aUserData['uid']);

        return true;
    }

    /**
     * Check if the session key is valid. If yes returns true, otherwise false and sends an error message with error code 1
     *
     * @access protected
     * @param string $sSessionKey Auth credentials
     * @return bool
     */
    protected function _checkSessionKey($sSessionKey)
    {
        $sSessionKey = (string) $sSessionKey;
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()->deleteAll($criteria);
        $oResult = Session::model()->findByPk($sSessionKey);

        if (is_null($oResult)) {
            return false;
        } else {
            $this->_jumpStartSession($oResult->data);
            return true;
        }
    }


    /**
     * Import a participant into the LimeSurvey CPDB
     *
     * It stores attributes as well, if they are registered before within ui
     *
     * Call the function with $response = $myJSONRPCClient->cpd_importParticipants( $sessionKey, $aParticipants);
     *
     * @param int $sSessionKey
     * @param array $aParticipants
     * [[0] => ["email"=>"dummy-02222@limesurvey.com","firstname"=>"max","lastname"=>"mustermann"]]
     * @param bool $update
     * @return array with status
     */
    public function cpd_importParticipants($sSessionKey, $participants, $update = false)
    {
        if (!$this->_checkSessionKey($sSessionKey)) {
            return array('status' => self::INVALID_SESSION_KEY);
        }

        $aDefaultFields = array('participant_id', 'firstname', 'lastname', 'email', 'language', 'blacklisted');
        $aResponse = array(
            'ImportCount' => 0,
            'UpdateCount' => 0
        );

        $aAttributeRecords = ParticipantAttributeName::model()
            ->with('participant_attribute_names_lang')
            ->findAll();

        foreach ($participants as $participant) {
            $model = null;

            if (isset($participant['id'])) {
                $participant['participant_id'] = $participant['id'];
            }

            if (isset($participant['participant_id'])) {
                $model = Participant::model()->findByPk($participant['participant_id']);
            } else {
                $model = Participant::model()->findByAttributes(array(
                    'firstname' => $participant['firstname'],
                    'lastname'  => $participant['lastname'],
                    'email'     => $participant['email'],
                    'owner_uid' => Yii::app()->session['loginID']
                ));
            }

            // Participant not found, so we create a new one
            if (!$model) {
                if (!Permission::model()->hasGlobalPermission('participantpanel', 'create')) {
                    /* No permission to create : continue */
                    continue;
                }
                $model = new Participant();
                if (isset($participant['participant_id'])) {
                    $model->participant_id = $participant['participant_id'];
                } else {
                    $model->participant_id = Participant::genUuid();
                }
            } elseif (!$model->userHasPermissionToEdit()) {
                /* No permission to update : continue */
                continue;
            }

            $scenario = $model->getScenario(); // insert or update
            if ($scenario == 'update' && $update === false) {
                continue;
            }

            $model->firstname   = $participant['firstname'];
            $model->lastname    = $participant['lastname'];
            $model->email       = $participant['email'];
            $model->language    = $participant['language'] ?? 'en';
            $model->owner_uid   = Yii::app()->session['loginID'];
            $model->blacklisted = (isset($participant['blacklisted']) && $participant['blacklisted'] === 'Y') ? 'Y' : 'N';

            if ($scenario == 'insert') {
                $model->created = date('Y-m-d H:i:s');
                $model->created_by = Yii::app()->session['loginID'];
            } else { // update
                $model->modified = date('Y-m-d H:i:s');
            }

            if ($model->encryptSave()) {
                foreach ($participant as $sLabel => $sAttributeValue) {
                    if (!in_array($sLabel, $aDefaultFields)) {
                        foreach ($aAttributeRecords as $sKey => $arValue) {
                            $aAttributes = $arValue->getAttributes();
                            if ($aAttributes['defaultname'] == $sLabel) {
                                $attribute = ParticipantAttribute::model();
                                $attribute->attribute_id = $aAttributes['attribute_id'];
                                $attribute->participant_id = $model->participant_id;
                                $attribute->value = $sAttributeValue;
                                $attribute->encrypt();
                                $attribute->updateParticipantAttributeValue($attribute->attributes);
                            }
                        }
                    }
                }

                if ($scenario == 'insert') {
                    $aResponse['ImportCount']++;
                } else {
                    $aResponse['UpdateCount']++;
                }
            }
        }

        return $aResponse;
    }

    /**
     * Check the email, if it's in a valid format
     * @param $sEmail
     * @return bool
     */
    protected function _checkEmailFormat($sEmail)
    {
        if ($sEmail != '') {
            $aEmailAddresses = explode(';', (string) $sEmail);
            // Ignore additional email addresses
            $sEmailaddress = $aEmailAddresses[0];
            if (!validateEmailAddress($sEmailaddress)) {
                return false;
            }
            return true;
        }
        return false;
    }
}
