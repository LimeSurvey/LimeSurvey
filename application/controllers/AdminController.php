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
*/

class AdminController extends LSYii_Controller
{
    public $lang = null;
    protected $user_id = 0;

    /**
    * Initialises this controller, does some basic checks and setups
    *
    * @access protected
    * @return void
    */
    protected function _init()
    {
        parent::_init();
        $dUpdateLastCheck = getGlobalSetting('updatelastcheck');

        $this->_sessioncontrol();

        if (Yii::app()->getConfig('buildnumber') != "" && Yii::app()->getConfig('updatecheckperiod') > 0 && $dUpdateLastCheck < dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", "-". Yii::app()->getConfig('updatecheckperiod')." days"))
            updateCheck();

        //unset(Yii::app()->session['FileManagerContext']);

        $this->user_id = Yii::app()->user->getId();
        Yii::app()->setConfig('adminimageurl', Yii::app()->getConfig('styleurl').Yii::app()->getConfig('admintheme').'/images/');
        Yii::app()->setConfig('adminstyleurl', Yii::app()->getConfig('styleurl').Yii::app()->getConfig('admintheme').'/');
        if (!Yii::app()->getConfig("surveyid")) {Yii::app()->setConfig("surveyid", returnGlobal('sid'));}         //SurveyID
        if (!Yii::app()->getConfig("ugid")) {Yii::app()->setConfig("ugid", returnGlobal('ugid'));}                //Usergroup-ID
        if (!Yii::app()->getConfig("gid")) {Yii::app()->setConfig("gid", returnGlobal('gid'));}                   //GroupID
        if (!Yii::app()->getConfig("qid")) {Yii::app()->setConfig("qid", returnGlobal('qid'));}                   //QuestionID
        if (!Yii::app()->getConfig("lid")) {Yii::app()->setConfig("lid", returnGlobal('lid'));}                   //LabelID
        if (!Yii::app()->getConfig("code")) {Yii::app()->setConfig("code", returnGlobal('code'));}                // ??
        if (!Yii::app()->getConfig("action")) {Yii::app()->setConfig("action", returnGlobal('action'));}          //Desired action
        if (!Yii::app()->getConfig("subaction")) {Yii::app()->setConfig("subaction", returnGlobal('subaction'));} //Desired subaction
        if (!Yii::app()->getConfig("editedaction")) {Yii::app()->setConfig("editedaction", returnGlobal('editedaction'));} // for html editor integration
    }

    /**
    * Shows a nice error message to the world
    *
    * @access public
    * @param string $message The error message
    * @param string|array $url URL. Either a string. Or array with keys url and title
    * @return void
    */
    public function error($message, $url = array())
    {
        $sURL = $url;
        $sMessage= $message;
        $clang = $this->lang;

        $this->_getAdminHeader();
        $sOutput = "<div class='messagebox ui-corner-all'>\n";
        $sOutput .= '<div class="warningheader">'.$clang->gT('Error').'</div><br />'."\n";
        $sOutput .= $sMessage . '<br /><br />'."\n";
        if (!empty($sURL) && !is_array($sURL))
        {
            $sTitle = $clang->gT('Back');
        }
        elseif (!empty($sURL['url']))
        {
            if (!empty($sURL['title']))
            {
                $sTitle = $sURL['title'];
            }
            else
            {
                $sTitle = $clang->gT('Back');
            }
            $sURL = $sURL['url'];
        }
        else
        {
            $sTitle = $clang->gT('Main Admin Screen');
            $sURL = $this->createUrl('/admin');
        }
        $sOutput .= '<input type="submit" value="'.$sTitle.'" onclick=\'window.open("'.$sURL.'", "_top")\' /><br /><br />'."\n";
        $sOutput .= '</div>'."\n";
        $sOutput .= '</div>'."\n";
        echo $sOutput;

        $this->_getAdminFooter('http://docs.limesurvey.org', $clang->gT('LimeSurvey online manual'));

        die;
    }
    /**
    * Load and set session vars
    *
    * @access protected
    * @return void
    */
    protected function _sessioncontrol()
    {
        Yii::import('application.libraries.Limesurvey_lang');
        // From personal settings
        if (Yii::app()->request->getPost('action') == 'savepersonalsettings') {
            if (Yii::app()->request->getPost('lang')=='auto')
            {
                $sLanguage= getBrowserLanguage();
            }
            else
            {
                $sLanguage=Yii::app()->request->getPost('lang');
            }
            Yii::app()->session['adminlang'] = $sLanguage;
        }

        if (empty(Yii::app()->session['adminlang']))
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

        $this->lang = new Limesurvey_lang(Yii::app()->session['adminlang']);
        Yii::app()->setLang($this->lang);

        if (!empty($this->user_id))
            $this->_setSessionUserRights($this->user_id);
    }

    /**
    * Checks for action specific authorization and then executes an action
    *
    * @access public
    * @param string $action
    * @return bool
    */
    public function run($action)
    {
        $sAction = $action;
        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}'))
        {
            $iCurrentDBVersion = (int)getGlobalSetting('DBVersion');
            if ( $iCurrentDBVersion < Yii::app()->getConfig('dbversionnumber') && $sAction != 'update' && $sAction != 'authentication')
                $this->redirect($this->createUrl('/admin/update/sa/db'));
        }

        if ($sAction != "update" && $sAction != "db")
            if (empty($this->user_id) && $sAction != "authentication"  && $sAction != "remotecontrol")
            {
                if (!empty($sAction) && $sAction != 'index')
                    Yii::app()->session['redirect_after_login'] = $this->createUrl('/');

                Yii::app()->session['redirectopage'] = Yii::app()->request->requestUri;

                $this->redirect($this->createUrl('/admin/authentication/sa/login'));
            }

            return parent::run($sAction);
    }

    /**
    * Routes all the actions to their respective places
    *
    * @access public
    * @return array
    */
    public function actions()
    {
        $sActions = $this->getActionClasses();

        foreach ($sActions as $sAction => $sClass)
        {
            $sActions[$sAction] = "application.controllers.admin.{$sClass}";
        }

        return $sActions;
    }

    public function getActionClasses()
    {
        return array(
        'assessments'      => 'assessments',
        'authentication'   => 'authentication',
        'checkintegrity'   => 'checkintegrity',
        'conditions'       => 'conditionsaction',
        'database'         => 'database',
        'dataentry'        => 'dataentry',
        'dumpdb'           => 'dumpdb',
        'emailtemplates'   => 'emailtemplates',
        'export'           => 'export',
        'expressions'      => 'expressions',
        'globalsettings'   => 'globalsettings',
        'htmleditor_pop'   => 'htmleditor_pop',
        'limereplacementfields' => 'limereplacementfields',
        'index'            => 'index',
        'kcfinder'         => 'kcfinder',
        'labels'           => 'labels',
        'participants'     => 'participantsaction',
        'printablesurvey'  => 'printablesurvey',
        'question'         => 'question',
        'questiongroup'    => 'questiongroup',
        'quotas'           => 'quotas',
        'remotecontrol'    => 'remotecontrol',
        'responses'        => 'responses',
        'saved'            => 'saved',
        'statistics'       => 'statistics',
        'survey'           => 'surveyadmin',
        'surveypermission' => 'surveypermission',
        'user'             => 'useraction',
        'usergroups'       => 'usergroups',
        'templates'        => 'templates',
        'tokens'           => 'tokens',
        'translate'        => 'translate',
        'update'           => 'update',
        );
    }

    /**
    * Set Session User Rights
    *
    * @access public
    * @return boolean
    */
    public function _setSessionUserRights()
    {
        $iLoginID=Yii::app()->user->getId();
        if(!$iLoginID)
            return false;
        $oUser = User::model()->findByPk($iLoginID);
        if(!$oUser)
            return false;
        $userrights=array();
        foreach(User::$UserRights as $right)
        {
            $userrights[$right]=($oUser->$right || $oUser->superadmin);
        }
        $userrights['initialsuperadmin']=(!$oUser->parent_id);
        // initialsuperadminare a superadmin
        // initialsuperadmin can have less right than superadmin in session only: like old situation
        $userrights['superadmin']=($userrights['superadmin'] || $userrights['initialsuperadmin']);
        foreach($userrights as $right=>$value)
        {
            Yii::app()->session['USER_RIGHT_'.strtoupper($right)]=($value)? 1:0;
        }
        return true;
    }

    /**
    * Prints Admin Header
    *
    * @access protected
    * @param string $sMetaHeaders
    * @return mixed
    */
    public function _getAdminHeader($sMetaHeaders = false)
    {
        if (empty(Yii::app()->session['adminlang']))
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

        $aData = array();
        $aData['adminlang'] = Yii::app()->session['adminlang'];
        $aData['test'] = "t";
        $aData['languageRTL']="";
        $aData['styleRTL']="";

        Yii::app()->loadHelper("surveytranslator");

        if (getLanguageRTL(Yii::app()->session["adminlang"]))
        {
            $aData['languageRTL'] = " dir=\"rtl\" ";
            $aData['bIsRTL']=true;
        }
        else
        {
            $aData['bIsRTL']=false;
        }

        $aData['meta']="";
        if ($sMetaHeaders)
        {
            $aData['meta']=$sMetaHeaders;
        }

        $aData['baseurl'] = Yii::app()->baseUrl . '/';
        $aData['datepickerlang']="";
        if (Yii::app()->session["adminlang"] != 'en')
            $aData['datepickerlang'] = "<script type=\"text/javascript\" src=\"".Yii::app()->getConfig('generalscripts')."jquery/locale/jquery.ui.datepicker-".Yii::app()->session["adminlang"].".js\"></script>\n";

        $aData['sitename'] = Yii::app()->getConfig("sitename");
        $aData['admintheme'] = Yii::app()->getConfig("admintheme");
        $aData['firebug'] = useFirebug();

        if (!empty(Yii::app()->session['dateformat']))
            $aData['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);

        // Prepare flashmessage
        if (!empty(Yii::app()->session['flashmessage']) && Yii::app()->session['flashmessage'] != '')
        {
            $aData['flashmessage'] = Yii::app()->session['flashmessage'];
            unset(Yii::app()->session['flashmessage']);
        }

        $aData['css_admin_includes'] = $this->_css_admin_includes(array(), true);

        return $this->renderPartial("/admin/super/header", $aData);
    }

    /**
    * Prints Admin Footer
    *
    * @access protected
    * @param string $sURL
    * @param string $sExplanation
    * @return mixed
    */
    public function _getAdminFooter($sURL, $sExplanation)
    {
        $clang = $this->lang;
        $data['clang'] = $clang;

        $data['versionnumber'] = Yii::app()->getConfig("versionnumber");

        $data['buildtext'] = "";
        if(Yii::app()->getConfig("buildnumber")!="") {
            $data['buildtext']= "Build ".Yii::app()->getConfig("buildnumber");
        }

        //If user is not logged in, don't print the version number information in the footer.
        if (empty(Yii::app()->session['loginID']))
        {
            $data['versionnumber']="";
            $data['versiontitle']="";
            $data['buildtext']="";
        }
        else
        {
            $data['versiontitle'] = $clang->gT('Version');
        }

        $data['imageurl'] = Yii::app()->getConfig("imageurl");
        $data['url'] = $sURL;

        $data['js_admin_includes']  = $this->_js_admin_includes(array(), true);
        $data['css_admin_includes'] = $this->_css_admin_includes(array(), true);

        return $this->render("/admin/super/footer", $data);

    }

    /**
    * Shows a message box
    *
    * @access public
    * @param string $sTitle
    * @param string $sMessage
    * @param string $sClass
    * @return void
    */
    public function _showMessageBox($sTitle, $sMessage, $sClass="header ui-widget-header")
    {
        $aData['title'] = $sTitle;
        $aData['message'] = $sMessage;
        $aData['class'] = $sClass;
        $aData['clang'] = $this->lang;

        $this->render('/admin/super/messagebox', $aData);
    }

    /**
    * _showadminmenu() function returns html text for the administration button bar
    *
    * @access public
    * @param int $iSurveyID
    * @return void
    */
    public function _showadminmenu($iSurveyID = false)
    {

        $clang = $this->lang;
        $aData['clang']= $clang;

        if (Yii::app()->session['pw_notify'] && Yii::app()->getConfig("debug")<2)  {
            Yii::app()->session['flashmessage'] = $clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.");
        }

        $aData['showupdate'] = (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 && getGlobalSetting("updatelastcheck")>0 && getGlobalSetting("updateavailable")==1 && Yii::app()->getConfig("updatable") );
        $aData['updateversion'] = getGlobalSetting("updateversion");
        $aData['updatebuild'] = getGlobalSetting("updatebuild");
        $aData['surveyid'] = $iSurveyID;
        $aData['iconsize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $this->render("/admin/super/adminmenu", $aData);
    }

    /**
    * put your comment there...
    * 
    */
    public function _loadEndScripts()
    {
        static $bOut = false;
        if ($bOut)
            return true;
        $bOut = true;
        if (empty(Yii::app()->session['metaHeader']))
            Yii::app()->session['metaHeader'] = '';

        unset(Yii::app()->session['metaHeader']);

        return $this->render('/admin/endScripts_view', array());
    }
    
    /**
    * put your comment there...
    * 
    * @param array $aIncludes
    * @param boolean $bReset
    */
    public function _css_admin_includes($aIncludes = array(), $bReset = false)
    {
        return $this->_admin_includes('css', $aIncludes, $bReset);
    }

    /**
    * put your comment there...
    * 
    * @param array $aIncludes
    * @param boolean $bReset
    */
    public function _js_admin_includes($aIncludes = array(), $bReset = false)
    {
        return $this->_admin_includes('js', $aIncludes, $bReset);
    }

    /**
    * put your comment there...
    * 
    * @param string $sMethod
    * @param array $aIncludes
    * @param boolean $bReset
    */
    private function _admin_includes($sMethod, $aIncludes = array(), $bReset = false)
    {
        $sMethod = in_array($sMethod, array('js', 'css')) ? $sMethod : 'js';
        $aIncludes = (array) $aIncludes;
        $aAdminIncludes = (array) Yii::app()->getConfig("{$sMethod}_admin_includes");
        $aAdminIncludes = array_merge($aAdminIncludes, $aIncludes);
        $aAdminIncludes = array_filter($aAdminIncludes);
        $aAdminIncludes = array_unique($aAdminIncludes);
        if ($bReset)
        {
            Yii::app()->setConfig("{$sMethod}_admin_includes", array());
        }
        else
        {
            Yii::app()->setConfig("{$sMethod}_admin_includes", $aAdminIncludes);
        }

        return $aAdminIncludes;
    }
}
