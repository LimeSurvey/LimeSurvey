<?php

use LimeSurvey\Menu\Menu;
use LimeSurvey\Menu\MenuItem;

/**
 * Class LayoutHelper
 */
class LayoutHelper
{
    /**
     * Header (html header)
     *
     * @param array $aData
     * @param bool $sendHTTPHeader
     */
    public function showHeaders(array $aData, bool $sendHTTPHeader = true)
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
     */
    public function getAdminHeader(bool $meta = false, bool $return = false)
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

        $aData['baseurl'] = Yii::app()->baseUrl . '/';
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
     * This is the topbar for the whole application consiting of:
     * -- Create survey (link)
     * -- Surveys
     * -- Help
     * -- Configuration (collapse menu items e.g. 'Usermanagement', 'Dashboard')
     * -- Notifications
     * -- admin
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
    public function showadminmenu($aData): ?string
    {
        // We don't wont the admin menu to be shown in login page
        if (!Yii::app()->user->isGuest) {
            if (!(App()->getConfig('ssl_disable_alert')) && strtolower(App()->getConfig('force_ssl') != 'on') && \Permission::model()->hasGlobalPermission("superadmin")) {
                $not = new UniqueNotification(array(
                    'user_id' => App()->user->id,
                    'importance' => Notification::HIGH_IMPORTANCE,
                    'title' => gT('SSL not enforced'),
                    'message' => '<span class="ri-error-warning-fill"></span>&nbsp;' .
                        gT("Warning: Please enforce SSL encryption in Global settings/Security after SSL is properly configured for your webserver.")
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
            //new create process (including survey, survey group, import survey)
            $aData['extraMenus'][] = $this->getCreateMenu();

            // Get notification menu
            $surveyId = $aData['surveyid'] ?? null;
            Yii::import('application.controllers.admin.NotificationController');
            $aData['adminNotifications'] = NotificationController::getMenuWidget($surveyId, true /* show spinner */);

            Yii::app()->getController()->renderPartial("/layouts/adminmenu", $aData);
        }
        return null;
    }

    /**
     * Returns extra menu for the new create process (including survey, survey group, import survey)
     *
     * @return Menu
     */
    public function getCreateMenu() {
        $menuItemHeader = [
            'isDivider' => false,
            'isSmallText' => true,
            'label' => 'Create new',
            'href' => '#',
            'iconClass' => 'ri-add-line',
        ];
        $menuItems[] = (new MenuItem($menuItemHeader));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Survey'),
            'href' => \Yii::app()->createUrl('surveyAdministration/newSurvey'),
            'iconClass' => 'ri-add-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Survey (default values)'),
            'href' => \Yii::app()->createUrl('surveyAdministration/createSurvey'),
            'iconClass' => 'ri-add-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Survey group'),
            'href' => \Yii::app()->createUrl('admin/surveysgroups/sa/create'),
            'iconClass' => 'ri-add-circle-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $menuItemNewSurvey = [
            'isDivider' => false,
            'isSmallText' => false,
            'label' => gT('Import survey'),
            'href' => \Yii::app()->createUrl('surveyAdministration/newSurvey'),
            'iconClass' => 'ri-upload-line',
        ];
        $menuItems[] = (new MenuItem($menuItemNewSurvey));

        $options = [
            'label' => '+',
            'iconClass' => 'ri-add-line',
            'isDropDown' => true,
            'isDropDownButton' => true,
            'menuItems' => $menuItems,
            'isPrepended' => true,
        ];

        $createMenu = new Menu($options);

        return $createMenu;
    }

    /**
     * Get extra menus from plugins that are using event beforeAdminMenuRender
     *
     * @param array $aData
     * @return array<ExtraMenu>
     */
    protected function fetchExtraMenus(array $aData): array
    {
        //todo this is different from SurveyCommonAction (no second parameter $this ...) correct usage?
        $event = new PluginEvent('beforeAdminMenuRender');

        $event->set('data', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $extraMenus = $result->get('extraMenus');

        if ($extraMenus === null) {
            $extraMenus = array();
        }

        return $extraMenus;
    }

    public function renderTopbarTemplate($aData)
    {
        $titleTextBreadcrumb = null;
        $titleBackLink = null;
        $isBreadCrumb = isset($aData['title_bar']); //only the existence is important, indicator for breadcrumb

        if (isset($aData['topbar']['title'])) {
            $titleTextBreadcrumb = $aData['topbar']['title'];
        } elseif ($isBreadCrumb) {
            $titleTextBreadcrumb = App()->getController()->renderPartial("/layouts/title_bar", $aData, true);
        }
        if (isset($aData['topbar']['backLink'])) {
            $titleBackLink = $aData['topbar']['backLink'];
        }

        $middle = $aData['topbar']['middleButtons'] ?? '';
        $rightSide = $aData['topbar']['rightButtons'] ?? '';
        if ($titleTextBreadcrumb !== null) {
            //special case for question administration (overview and editor)
            if (isset($aData['topBar']['name']) && ($aData['topBar']['name'] === 'questionTopbar_view')) {
                $topbarData = TopbarConfiguration::getSurveyTopbarData($aData['surveyid']);
                $topbarQuestionEditorData = TopbarConfiguration::getQuestionTopbarData($aData['surveyid']);
                $topbarQuestionEditorData['breadcrumb'] = $titleTextBreadcrumb;
                $topbarQuestionEditorData = array_merge($topbarQuestionEditorData, $aData);
                $topbarQuestionEditorData = array_merge($topbarQuestionEditorData, $topbarData);

                return App()->getController()->renderPartial(
                    '/questionAdministration/partial/topbarBtns/questionTopbar_view',
                    $topbarQuestionEditorData,
                    true
                );
            }

            return App()->getController()->widget(
                'ext.LimeTopbarWidget.TopbarWidget',
                [
                    'leftSide'     => $titleTextBreadcrumb,
                    'middle'       => $middle, //array of ButtonWidget
                    'rightSide'    => $rightSide, //array of ButtonWidget
                    'isBreadCrumb' => $isBreadCrumb,
                    'titleBackLink' => $titleBackLink
                ],
                true
            );
        }
        return ''; //no topbar shown in this case
    }

    /**
     * Display the update notification
     * @throws CException
     */
    public function updatenotification()
    {
        /**
         *  OLD $this was SurveyCommonAction ....
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
                App()->getClientScript()->registerScriptFile(App()->getConfig('packages') . DIRECTORY_SEPARATOR . 'comfort_update' . DIRECTORY_SEPARATOR . 'comfort_update.js');
                return App()->getController()->renderPartial(
                    "/admin/update/_update_notification",
                    array('security_update_available' => $updateNotification->security_update)
                );
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
        return Yii::app()->getController()->renderPartial("/admin/notifications/notifications", array('aMessage' => $aMessage));
    }

    /**
     *
     * @return bool|string|string[]|null
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
     * @param bool $questionEditor if footer is on question editor layout page
     * @return string|null
     */
    public function getAdminFooter(string $url, bool $return = false, bool $questionEditor = false): ?string
    {
        $aData['questionEditor'] = $questionEditor;
        $aData['versionnumber'] = Yii::app()->getConfig("versionnumber");

        $aData['buildtext'] = "";
        if (Yii::app()->getConfig("buildnumber") != "") {
            $aData['buildtext'] = "+" . Yii::app()->getConfig("buildnumber");
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
     * Show side menu for survey view
     *
     * @param array $aData all the needed data
     */
    public function renderSurveySidemenu(array $aData)
    {
        $iSurveyID = $aData['surveyid'];

        $survey = Survey::model()->findByPk($iSurveyID);

        if (App()->getConfig('editorEnabled') && $survey->getTemplateEffectiveName() == 'fruity_twentythree') {
            App()->controller->widget('ext.admin.survey.SurveySidemenuWidget.SurveySidemenuWidget', ['sid' => $iSurveyID]);
            return;
        }

        // TODO : create subfunctions
        $sumresult1 = Survey::model()->with(
            array(
                'languagesettings' => array('condition' => 'surveyls_language=language')
            )
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
            $aGroups = QuestionGroup::model()->findAllByAttributes(array('sid' => $iSurveyID), array('order' => 'group_order ASC'));
            $aData['quickmenu'] = $this->renderQuickmenu($aData);
            $aData['beforeSideMenuRender'] = $this->beforeSideMenuRender($aData);
            $aData['aGroups'] = $aGroups;
            $aData['surveycontent'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'read');
            $aData['surveycontentupdate'] = Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'update');
            $aData['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');

            Yii::app()->getController()->renderPartial("/layouts/sidemenu", $aData);
        } else {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            Yii::app()->getController()->redirect(array("dashboard/view"));
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
    protected function renderQuickmenu(array $aData): string
    {
        $event = new PluginEvent('afterQuickMenuLoad', $this);
        $event->set('aData', $aData);
        $result = App()->getPluginManager()->dispatchEvent($event);

        $quickMenuItems = $result->get('quickMenuItems');
        if (!empty($quickMenuItems)) {
            usort($quickMenuItems, function ($b1, $b2) {
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
     * todo: document me...
     *
     * @deprecated not used anymore
     * @param array $aData
     */
    /*
    public function renderGeneralTopbarAdditions(array $aData)
    {
        $aData['topBar'] = $aData['topBar'] ?? [];
        $aData['topBar'] = array_merge(
            [
                'type' => 'survey',
                'sid' => $aData['sid'],
                'gid' => $aData['gid'] ?? 0,
                'qid' => $aData['qid'] ?? 0,
                'showSaveButton' => false,
                'showCloseButton' => false,
            ],
            $aData['topBar']
        );

        if (isset($aData['qid'])) {
            $aData['topBar']['type'] = $aData['topBar']['type'] ?? 'question';
        } elseif (isset($aData['gid'])) {
            $aData['topBar']['type'] = $aData['topBar']['type'] ?? 'group';
        } elseif (isset($aData['surveyid'])) {
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
            $aData['topBar']['type'] = $aData['topBar']['type'] ?? 'survey';
        }
        Yii::app()->getController()->renderPartial("/admin/survey/topbar/topbar_additions", $aData);
    }*/
}
