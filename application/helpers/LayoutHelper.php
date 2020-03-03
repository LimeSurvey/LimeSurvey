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

}
