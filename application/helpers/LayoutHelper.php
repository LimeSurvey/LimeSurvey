<?php


class LayoutHelper
{
    /**
     * Header
     *
     * @param array $aData
     */
    public function showHeaders($aData, $sendHTTPHeader = true)
    {
        if (!isset($aData['display']['header']) || $aData['display']['header'] !== false) {
            // Send HTTP header
            if ($sendHTTPHeader) {
                header("Content-type: text/html; charset=UTF-8"); // needed for correct UTF-8 encoding
            }
            $this->getAdminHeader();
        }
    }


    /**
     * Prints Admin Header
     *
     * @access protected
     * @param bool $meta
     * @param bool $return
     * @return string|null
     * @throws CException
     */
    public function getAdminHeader($meta = false, $return = false)
    {
        if (empty(Yii::app()->session['adminlang'])) {
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
        }

        $aData = array();
        $aData['adminlang'] = Yii::app()->language;
        $aData['languageRTL'] = "";
        $aData['styleRTL'] = "";
        Yii::app()->loadHelper("surveytranslator");

        if (getLanguageRTL(Yii::app()->language)) {
            $aData['languageRTL'] = " dir=\"rtl\" ";
            $aData['bIsRTL'] = true;
        } else {
            $aData['languageRTL'] = " dir=\"ltr\" ";
            $aData['bIsRTL'] = false;
        }

        $aData['meta'] = "";
        if ($meta) {
            $aData['meta'] = $meta;
        }

        $aData['baseurl'] = Yii::app()->baseUrl.'/';
        $aData['datepickerlang'] = "";

        $aData['sitename'] = Yii::app()->getConfig("sitename");
        $aData['firebug'] = useFirebug();

        if (!empty(Yii::app()->session['dateformat'])) {
            $aData['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);
        }

        // Register admin theme package with asset manager
        $oAdminTheme = AdminTheme::getInstance();

        $aData['sAdmintheme'] = $oAdminTheme->name;
        $aData['aPackageScripts'] = $aData['aPackageStyles'] = array();

        $sOutput = Yii::app()->getController()->renderPartial("/layouts/header", $aData, true);

        if ($return) {
            return $sOutput;
        } else {
            echo $sOutput;
        }
    }


    /**
     * _showadminmenu() function returns html text for the administration button bar
     *
     * @access public
     * @param $aData
     * @return string
     * @throws CException
     * @global string $scriptname
     * @global string $surveyid
     * @global string $setfont
     * @global string $imageurl
     * @global int $surveyid
     * @global string $homedir
     */
    public function showadminmenu($aData)
    {
        // We don't wont the admin menu to be shown in login page
        if (!Yii::app()->user->isGuest) {
            // Default password notification
            if (Yii::app()->session['pw_notify'] && Yii::app()->getConfig("debug") < 2) {
                $not = new UniqueNotification(array(
                    'user_id' => App()->user->id,
                    'importance' => Notification::HIGH_IMPORTANCE,
                    'title' => gT('Password warning'),
                    'message' => '<span class="fa fa-exclamation-circle text-warning"></span>&nbsp;'.
                        gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.")
                ));
                $not->save();
            }
            if (!(App()->getConfig('ssl_disable_alert')) && strtolower(App()->getConfig('force_ssl') != 'on') && \Permission::model()->hasGlobalPermission("superadmin")) {
                $not = new UniqueNotification(array(
                    'user_id' => App()->user->id,
                    'importance' => Notification::HIGH_IMPORTANCE,
                    'title' => gT('SSL not enforced'),
                    'message' => '<span class="fa fa-exclamation-circle text-warning"></span>&nbsp;'.
                        gT("Warning: Please enforce SSL encrpytion in Global settings/Security after SSL is properly configured for your webserver.")
                ));
                $not->save();
            }

            // Count active survey
            $aData['dataForConfigMenu']['activesurveyscount'] = $aData['activesurveyscount'] = Survey::model()->permission(Yii::app()->user->getId())->active()->count();

            // Count survey
            $aData['dataForConfigMenu']['surveyscount'] = Survey::model()->count();

            // Count user
            $aData['dataForConfigMenu']['userscount'] = User::model()->count();

            //Check if have a comfortUpdate key
            if (getGlobalSetting('emailsmtpdebug') != '') {
                $aData['dataForConfigMenu']['comfortUpdateKey'] = gT('Activated');
            } else {
                $aData['dataForConfigMenu']['comfortUpdateKey'] = gT('None');
            }

            $aData['sitename'] = Yii::app()->getConfig("sitename");

            $updateModel = new UpdateForm();
            $updateNotification = $updateModel->updateNotification;
            $aData['showupdate'] = Yii::app()->getConfig('updatable') && $updateNotification->result && !$updateNotification->unstable_update;

            // Fetch extra menus from plugins, e.g. last visited surveys
            $aData['extraMenus'] = $this->fetchExtraMenus($aData);

           // $aData['extraMenus'] = ''; //todo extraMenu should work

            // Get notification menu
            $surveyId = isset($aData['surveyid']) ? $aData['surveyid'] : null;
            Yii::import('application.controllers.admin.NotificationController');
            $aData['adminNotifications'] = NotificationController::getMenuWidget($surveyId, true /* show spinner */);

            Yii::app()->getController()->renderPartial("/layouts/adminmenu", $aData);
        }
        return null;
    }

    /**
     * Get extra menus from plugins that are using event beforeAdminMenuRender
     *
     * @param array $aData
     * @return array<ExtraMenu>
     */
    protected function fetchExtraMenus(array $aData)
    {
        //todo this is different from Survey_Common_Action (no second parameter $this ...) correct usage?
        $event = new PluginEvent('beforeAdminMenuRender');

        $event->set('data', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $extraMenus = $result->get('extraMenus');

        if ($extraMenus === null) {
            $extraMenus = array();
        }

        return $extraMenus;
    }

    /**
     * This is for rendering a particular Menubar (e.g. the userGroupBar)
     *
     * @param $viewPathName
     * @param $data
     * @throws CException
     */
    public function renderMenuBar($aData){
        if(isset($aData['menubar_pathname']) ){
            Yii::app()->getController()->renderPartial($aData['menubar_pathname'], $aData);
        }
    }

    /**
     * Renders specific button bar with buttons like (saveBtn, saveAndCloseBtn, closeBtn)
     * If rendered or not depends on aData['fullpagebar'] is set to true in a specific action
     *
     * @param $aData
     * @throws CException
     */
    public function fullpagebar($aData)
    {
        if ((isset($aData['fullpagebar']))) {
            if (isset($aData['fullpagebar']['closebutton']['url']) && !isset($aData['fullpagebar']['closebutton']['url_keep'])) {
                $sAlternativeUrl = '/admin/index';
                $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }
            Yii::app()->getController()->renderPartial("/layouts/fullpagebar_view", $aData);
        }
    }

    /**
     * Display the update notification
     */
    public function updatenotification()
    {
        /**
         *  OLD $this was Survey_Common_Action ....
         *
         *  Never use Notification model for database update.
           TODO: Real fix: No database queries while doing database update, meaning
           don't call _renderWrappedTemplate.
        if (get_class($this) == 'databaseupdate') {
            return;
        }
         *
         *  NEW
         *
         * todo is that correct?? in this case i'm getting the name of a specific action, maybe it must be the new Controllername
         * for databaseupdate instead of 'databaseupdate'?
         */
        if (get_class(Yii::app()->getController()->getAction()) == 'databaseupdate') {
            return;
        }

        if (!Yii::app()->user->isGuest && Yii::app()->getConfig('updatable')) {
            $updateModel = new UpdateForm();
            $updateNotification = $updateModel->updateNotification;

            if ($updateNotification->result) {
                return Yii::app()->getController()->renderPartial("/admin/update/_update_notification",
                    array('security_update_available'=>$updateNotification->security_update));
            }
        }
    }

    /**
     * Display notifications
     */
    public function notifications()
    {
        $aMessage = App()->session['arrayNotificationMessages'];
        if (!is_array($aMessage)) {
            $aMessage = array();
        }
        unset(App()->session['arrayNotificationMessages']);
        return Yii::app()->getController()->renderPartial("/admin/notifications/notifications", array('aMessage'=>$aMessage));
    }

    /**
     *
     *
     * @return bool|string|string[]|null
     * @throws CException
     */
    public function loadEndScripts()
    {
        static $bRendered = false;
        if ($bRendered) {
            return true;
        }
        $bRendered = true;
        if (empty(Yii::app()->session['metaHeader'])) {
            Yii::app()->session['metaHeader'] = '';
        }

        unset(Yii::app()->session['metaHeader']);

        //todo the endScripts_view is empty, do we need this here??
        return Yii::app()->getController()->renderPartial('/admin/endScripts_view', array());
    }

    /**
     * Prints Admin Footer
     *
     * @access protected
     * @param string $url
     * @param bool $return
     * @return string|null
     */
    public function getAdminFooter($url, $return = false)
    {
        $aData['versionnumber'] = Yii::app()->getConfig("versionnumber");

        $aData['buildtext'] = "";
        if (Yii::app()->getConfig("buildnumber") != "") {
            $aData['buildtext'] = "+".Yii::app()->getConfig("buildnumber");
        }

        //If user is not logged in, don't print the version number information in the footer.
        if (empty(Yii::app()->session['loginID'])) {
            $aData['versionnumber'] = "";
            $aData['versiontitle'] = "";
            $aData['buildtext'] = "";
        } else {
            $aData['versiontitle'] = gT('Version');
        }

        $aData['imageurl'] = Yii::app()->getConfig("imageurl");
        $aData['url'] = $url;
        return Yii::app()->getController()->renderPartial("/admin/super/footer", $aData, $return);

    }

    /**
     * Shows a message box
     *
     * @access public
     * @param string $title
     * @param string $message
     * @param string $class
     * @param boolean $return
     * @return string|null
     * @throws CException
     */
    public function _showMessageBox($title, $message, $class = "message-box-error", $return = false)
    {
        $aData['title'] = $title;
        $aData['message'] = $message;
        $aData['class'] = $class;
        return Yii::app()->getController()->renderPartial('/layouts/messagebox', $aData, $return);
    }

    /**
     * Renders the titlebar of question editor page
     *
     * @param $aData
     */
    public function rendertitlebar($aData)
    {
        if (isset($aData['title_bar'])) {
            Yii::app()->getController()->renderPartial("/layouts/title_bar", $aData);
        }
    }

    /**
     * Show side menu for survey view
     *
     * @param array $aData all the needed data
     */
    public function renderSurveySidemenu($aData)
    {
        $iSurveyID = $aData['surveyid'];

        $survey = Survey::model()->findByPk($iSurveyID);
        // TODO : create subfunctions
        $sumresult1 = Survey::model()->with(array(
                'languagesettings' => array('condition'=>'surveyls_language=language'))
        )->find('sid = :surveyid', array(':surveyid' => $aData['surveyid'])); //$sumquery1, 1) ; //Checked

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
            $aData['permission'] = true;
        } else {
            $aData['gid'] = null;
            $aData['permission'] = false;
        }

        if (!is_null($sumresult1)) {
            $aData['activated'] = $survey->isActive;

            // Tokens
            $bTokenExists = $survey->hasTokensTable;
            if (!$bTokenExists) {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create');
            } else {
                $aData['tokenmanagement'] = Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'create')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'export')
                    || Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'import'); // and export / import ?
            }

            // Question explorer
            $aGroups = QuestionGroup::model()->findAllByAttributes(array('sid' => $iSurveyID), array('order'=>'group_order ASC'));
            $aData['quickmenu'] = $this->renderQuickmenu($aData);
            $aData['beforeSideMenuRender'] = $this->beforeSideMenuRender($aData);
            $aData['aGroups'] = $aGroups;
            $aData['surveycontent'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'read');
            $aData['surveycontentupdate'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'update');
            $aData['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
            Yii::app()->getController()->renderPartial("/admin/super/sidemenu", $aData);
        } else {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            Yii::app()->getController()->redirect(array("admin/index"));
        }
    }

    /**
     * Render the quick-menu that is shown
     * when side-menu is hidden.
     *
     * Only show home-icon for now.
     *
     * Add support for plugin to attach
     * icon elements using event afterQuickMenuLoad
     *
     * @param array $aData
     * @return string
     * @todo Make quick-menu user configurable
     */
    protected function renderQuickmenu(array $aData)
    {
        $event = new PluginEvent('afterQuickMenuLoad', $this);
        $event->set('aData', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $quickMenuItems = $result->get('quickMenuItems');
        if (!empty($quickMenuItems)) {
            usort($quickMenuItems, function($b1, $b2)
            {
                return (int) $b1['order'] > (int) $b2['order'];
            });
        }

        $aData['quickMenuItems'] = $quickMenuItems;

        if ($aData['quickMenuItems'] === null) {
            $aData['quickMenuItems'] = array();
        }

        $html = Yii::app()->getController()->renderPartial('/admin/super/quickmenu', $aData, true);
        return $html;
    }

    /**
     * Returns content from event beforeSideMenuRender
     * @param array $aData
     * @return string
     */
    protected function beforeSideMenuRender(array $aData)
    {
        $event = new PluginEvent('beforeSideMenuRender', $this);
        $event->set('aData', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);
        return $result->get('html');
    }

    /**
     * @param $aData
     */
    public function renderGeneraltopbar($aData) {
        $aData['topBar'] = isset($aData['topBar']) ? $aData['topBar'] : [];
        $aData['topBar'] = array_merge(
            [
                'type' => 'survey',
                'sid' => $aData['sid'],
                'gid' => $aData['gid'] ?? 0,
                'qid' => $aData['qid'] ?? 0,
                'showSaveButton' => false
            ],
            $aData['topBar']
        );

        Yii::app()->getClientScript()->registerPackage('admintoppanel');
        Yii::app()->getController()->renderPartial("/admin/survey/topbar/topbar_view", $aData);
    }

    /**
     * Render the save/cancel bar for Organize question groups/questions
     *
     * @param array $aData
     *
     * @since 2014-09-30
     * @author Olle Haerstedt
     */
    public function renderOrganizeQuestionBar($aData)
    {
        if (isset($aData['organizebar'])) {
            if (isset($aData['questionbar']['closebutton']['url'])) {
                $sAlternativeUrl = $aData['questionbar']['closebutton']['url'];
                $aData['questionbar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer(Yii::app()->createUrl($sAlternativeUrl));
            }

            $aData['questionbar'] = $aData['organizebar'];
            Yii::app()->getController()->renderPartial("/admin/survey/Question/questionbar_view", $aData);
        }
    }

    /**
     * listquestion groups
     * @param array $aData
     */
    public function renderListQuestionGroups(array $aData)
    {
        if (isset($aData['display']['menu_bars']['listquestiongroups'])) {
            Yii::app()->getController()->renderPartial("/admin/survey/QuestionGroups/listquestiongroups", $aData);
        }
    }

    /**
     * @param $aData
     * @throws CException
     */
    public function renderListQuestions($aData)
    {
        if (isset($aData['display']['menu_bars']['listquestions'])) {
            $iSurveyID = $aData['surveyid'];
            $oSurvey = $aData['oSurvey'];

            // The DataProvider will be build from the Question model, search method
            $model = new Question('search');

            // Global filter
            if (isset($_GET['Question'])) {
                $model->setAttributes($_GET['Question'], false);
            }

            // Filter group
            if (isset($_GET['gid'])) {
                $model->gid = $_GET['gid'];
            }

            // Set number of page
            if (isset($_GET['pageSize'])) {
                App()->user->setState('pageSize', (int) $_GET['pageSize']);
            }

            $aData['pageSize'] = App()->user->getState('pageSize', App()->params['defaultPageSize']);

            // We filter the current survey id
            $model->sid = $iSurveyID;

            $aData['model'] = $model;

            Yii::app()->getController()->renderPartial("/admin/survey/Question/listquestions", $aData);
        }
    }

    /**
     * Survey summary
     * @param array $aData
     */
    public function renderSurveySummary($aData)
    {
        if (isset($aData['display']['surveysummary'])) {
            if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid'])) {
                $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
            }
            $this->_surveysummary($aData);
        }
    }

    /**
     * Show survey summary
     *
     * todo: here are to many things that should be done in controller ...
     *
     * @param array $aData
     */
    private function _surveysummary($aData)
    {
        $iSurveyID = $aData['surveyid'];

        $aSurveyInfo = getSurveyInfo($iSurveyID);
        /** @var Survey $oSurvey */
        $oSurvey = $aData['oSurvey'];
        $activated = $aSurveyInfo['active'];

        $condition = array('sid' => $iSurveyID, 'parent_qid' => 0);
        $sumcount3 = Question::model()->countByAttributes($condition); //Checked
        $sumcount2 = QuestionGroup::model()->countByAttributes(array('sid' => $iSurveyID));

        //SURVEY SUMMARY
        $aAdditionalLanguages = $oSurvey->additionalLanguages;
        $surveysummary2 = [];
        if ($aSurveyInfo['anonymized'] != "N") {
            $surveysummary2[] = gT("Responses to this survey are anonymized.");
        } else {
            $surveysummary2[] = gT("Responses to this survey are NOT anonymized.");
        }
        if ($aSurveyInfo['format'] == "S") {
            $surveysummary2[] = gT("It is presented question by question.");
        } elseif ($aSurveyInfo['format'] == "G") {
            $surveysummary2[] = gT("It is presented group by group.");
        } else {
            $surveysummary2[] = gT("It is presented on one single page.");
        }
        if ($aSurveyInfo['questionindex'] > 0) {
            if ($aSurveyInfo['format'] == 'A') {
                $surveysummary2[] = gT("No question index will be shown with this format.");
            } elseif ($aSurveyInfo['questionindex'] == 1) {
                $surveysummary2[] = gT("A question index will be shown; participants will be able to jump between viewed questions.");
            } elseif ($aSurveyInfo['questionindex'] == 2) {
                $surveysummary2[] = gT("A full question index will be shown; participants will be able to jump between relevant questions.");
            }
        }
        if ($oSurvey->isDateStamp) {
            $surveysummary2[] = gT("Responses will be date stamped.");
        }
        if ($oSurvey->isIpAddr) {
            $surveysummary2[] = gT("IP Addresses will be logged");
        }
        if ($oSurvey->isRefUrl) {
            $surveysummary2[] = gT("Referrer URL will be saved.");
        }
        if ($oSurvey->isUseCookie) {
            $surveysummary2[] = gT("It uses cookies for access control.");
        }
        if ($oSurvey->isAllowRegister) {
            $surveysummary2[] = gT("If participant access codes are used, the public may register for this survey");
        }
        if ($oSurvey->isAllowSave && !$oSurvey->isTokenAnswersPersistence) {
            $surveysummary2[] = gT("Participants can save partially finished surveys");
        }
        if ($oSurvey->emailnotificationto != '') {
            $surveysummary2[] = gT("Basic email notification is sent to:").' '.htmlspecialchars($aSurveyInfo['emailnotificationto']);
        }
        if ($oSurvey->emailresponseto != '') {
            $surveysummary2[] = gT("Detailed email notification with response data is sent to:").' '.htmlspecialchars($aSurveyInfo['emailresponseto']);
        }

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if (trim($oSurvey->startdate) != '') {
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($oSurvey->startdate, 'Y-m-d H:i:s');
            $aData['startdate'] = $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        } else {
            $aData['startdate'] = "-";
        }

        if (trim($oSurvey->expires) != '') {
            //$constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($oSurvey->expires, 'Y-m-d H:i:s');
            //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $aData['expdate'] = $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        } else {
            $aData['expdate'] = "-";
        }

        $aData['language'] = getLanguageNameFromCode($oSurvey->language, false);

        if ($oSurvey->currentLanguageSettings->surveyls_urldescription == "") {
            $aSurveyInfo['surveyls_urldescription'] = htmlspecialchars($aSurveyInfo['surveyls_url']);
        }

        if ($oSurvey->currentLanguageSettings->surveyls_url != "") {
            $aData['endurl'] = " <a target='_blank' href=\"".htmlspecialchars($aSurveyInfo['surveyls_url'])."\" title=\"".htmlspecialchars($aSurveyInfo['surveyls_url'])."\">".flattenText($oSurvey->currentLanguageSettings->surveyls_url)."</a>";
        } else {
            $aData['endurl'] = "-";
        }

        $aData['sumcount3'] = $sumcount3;
        $aData['sumcount2'] = $sumcount2;

        if ($activated == "N") {
            $aData['activatedlang'] = gT("No");
        } else {
            $aData['activatedlang'] = gT("Yes");
        }

        $aData['activated'] = $activated;
        if ($oSurvey->isActive) {
            $aData['surveydb'] = Yii::app()->db->tablePrefix."survey_".$iSurveyID;
        }

        $aData['warnings'] = [];
        if ($activated == "N" && $sumcount3 == 0) {
            $aData['warnings'][] = gT("Survey cannot be activated yet.");
            if ($sumcount2 == 0 && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'create')) {
                $aData['warnings'][] = "<span class='statusentryhighlight'>[".gT("You need to add question groups")."]</span>";
            }
            if ($sumcount3 == 0 && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'create')) {
                $aData['warnings'][] = "<span class='statusentryhighlight'>".gT("You need to add questions")."</span>";
            }
        }
        $aData['hints'] = $surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
        //        $aData['tableusage'] = getDBTableUsage($iSurveyID);
        // ToDo: Table usage is calculated on every menu display which is too slow with big surveys.
        // Needs to be moved to a database field and only updated if there are question/subquestions added/removed (it's currently also not functional due to the port)

        $aData['tableusage'] = false;
        $aData['aAdditionalLanguages'] = $aAdditionalLanguages;
        $aData['groups_count'] = $sumcount2;

        // We get the state of the quickaction
        // If the survey is new (ie: it has no group), it is opened by default
        $quickactionState = SettingsUser::getUserSettingValue('quickaction_state');
        if ($quickactionState === null || $quickactionState === 0) {
            $quickactionState = 1;
            SettingsUser::setUserSetting('quickaction_state', 1);
        }
        $aData['quickactionstate'] = $quickactionState !== null ? intval($quickactionState) : 1;
        $aData['subviewData'] = $aData;

        Yii::app()->getClientScript()->registerPackage('surveysummary');

        $content = Yii::app()->getController()->renderPartial("/admin/survey/surveySummary_view", $aData, true);
        Yii::app()->getController()->renderPartial("/admin/super/sidebody", array(
            'content' => $content,
            'sideMenuOpen' => true
        ));
    }

    /**
     * todo: document me...
     *
     * @param $aData
     */
    public function renderGeneralTopbarAdditions($aData) {
        $aData['topBar'] = isset($aData['topBar']) ? $aData['topBar'] : [];
        $aData['topBar'] = array_merge(
            [
                'type' => 'survey',
                'sid' => $aData['sid'],
                'gid' => $aData['gid'] ?? 0,
                'qid' => $aData['qid'] ?? 0,
                'showSaveButton' => false
            ],
            $aData['topBar']
        );

        Yii::app()->getClientScript()->registerPackage((getLanguageRTL(Yii::app()->language) ? 'admintoppanelrtl' : 'admintoppanelltr'));

        if (isset($aData['qid'])) {
            $aData['topBar']['type'] = isset($aData['topBar']['type']) ? $aData['topBar']['type'] : 'question';
        } else if (isset($aData['gid'])) {
            $aData['topBar']['type'] = isset($aData['topBar']['type']) ? $aData['topBar']['type'] : 'group';
        } else if (isset($aData['surveyid'])) {
            $sid = $aData['sid'];
            $oSurvey       = Survey::model()->findByPk($sid);
            $respstatsread = Permission::model()->hasSurveyPermission($sid, 'responses', 'read')  ||
                Permission::model()->hasSurveyPermission($sid, 'statistics', 'read') ||
                Permission::model()->hasSurveyPermission($sid, 'responses', 'export');
            $surveyexport = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export');
            $oneLanguage  = (count($oSurvey->allLanguages) == 1);
            $aData['respstatsread'] = $respstatsread;
            $aData['surveyexport']  = $surveyexport;
            $aData['onelanguage']   = $oneLanguage;
            $aData['topBar']['type'] = isset($aData['topBar']['type']) ? $aData['topBar']['type'] : 'survey';
        }
        Yii::app()->getController()->renderPartial("/admin/survey/topbar/topbar_additions", $aData);


    }

}
