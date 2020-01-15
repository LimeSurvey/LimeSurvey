<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);

    //All paths relative from /application/views

    //headers will be generated with the template file /admin/super/header.php

// $this->_showHeaders($aData);  it comes from here ...AdminController()->getAdminHeader()
// ###################################################  HEADER #####################################################
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
/*
if ($meta) {
    $aData['meta'] = $meta;
}*/

$aData['baseurl'] = Yii::app()->baseUrl.'/';
$aData['datepickerlang'] = "";

$aData['sitename'] = Yii::app()->getConfig("sitename");

//todo: do we need this here ...
//useFirebug();
$aData['firebug'] = '';

if (!empty(Yii::app()->session['dateformat'])) {
    $aData['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);
}

// Register admin theme package with asset manager
$oAdminTheme = AdminTheme::getInstance();

$aData['sAdmintheme'] = $oAdminTheme->name;
$aData['aPackageScripts'] = $aData['aPackageStyles'] = array();

$this->renderPartial("/admin/super/header", $aData, true);
//################################################# END HEADER #######################################################


// --> source code from Survey_Common_Action ...
//The adminmenu bar will be generated from /admin/super/adminmenu.php
//$this->_showadminmenu($aData);
//################################################## ADMIN MENU #####################################################
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
   // $aData['extraMenus'] = $this->fetchExtraMenus($aData);  this is from Survey_Common_Action
    $event = new PluginEvent('beforeAdminMenuRender', $this);
    $event->set('data', $aData);
    $result = App()->getPluginManager()->dispatchEvent($event);

    $extraMenus = $result->get('extraMenus');

    if ($extraMenus === null) {
        $extraMenus = array();
    }

    $aData['extraMenus'] = $extraMenus;


    // Get notification menu
    $surveyId = isset($aData['surveyid']) ? $aData['surveyid'] : null;
    Yii::import('application.controllers.admin.NotificationController');
    $aData['adminNotifications'] = NotificationController::getMenuWidget($surveyId, true /* show spinner */);

    $this->renderPartial("/admin/super/adminmenu", $aData);
}

/**
 *
    // Generated through /admin/usergroup/usergroupbar_view
    $this->_userGroupBar($aData);
 *
    // Generated through /admin/super/fullpagebar_view
    $this->_fullpagebar($aData);

    $this->_updatenotification();
    $this->_notifications();
 */
    
    //The load indicator for pjax
    echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

    echo '<!-- Full page, started in Survey_Common_Action::render_wrapped_template() -->
                <div class="container-fluid full-page-wrapper" id="in_survey_common_action">
                    ';

    echo $content;

    echo '</div>';

    
    // Footer
    if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
       // Yii::app()->getController()->_loadEndScripts();
        //echo 'this is the footer ...';
    }

    if (!Yii::app()->user->isGuest) {
        if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
           // Yii::app()->getController()->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));

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
            $aData['url'] = 'http://manual.limesurvey.org';
            $this->renderPartial("/admin/super/footer", $aData, false);
        }
    } else {
        echo '</body>
        </html>';
    }
