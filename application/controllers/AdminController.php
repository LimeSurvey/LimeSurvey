<?php
/*
   * LimeSurvey
   * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
   *	$Id: AdminController.php 11413 2011-11-21 22:08:16Z dragooongarg $
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

		$updatelastcheck = '';

		$this->_sessioncontrol();

		if (Yii::app()->getConfig('buildnumber') != "" && Yii::app()->getConfig('updatecheckperiod') > 0 && $updatelastcheck < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", "-". Yii::app()->getConfig('updatecheckperiod')." days"))
			updatecheck();

		unset(Yii::app()->session['FileManagerContext']);

		$this->user_id = Yii::app()->user->getId();

		if (!Yii::app()->getConfig("surveyid")) {Yii::app()->setConfig("surveyid", returnglobal('sid'));}         //SurveyID
		if (!Yii::app()->getConfig("ugid")) {Yii::app()->setConfig("ugid", returnglobal('ugid'));}                //Usergroup-ID
		if (!Yii::app()->getConfig("gid")) {Yii::app()->setConfig("gid", returnglobal('gid'));}                   //GroupID
		if (!Yii::app()->getConfig("qid")) {Yii::app()->setConfig("qid", returnglobal('qid'));}                   //QuestionID
		if (!Yii::app()->getConfig("lid")) {Yii::app()->setConfig("lid", returnglobal('lid'));}                   //LabelID
		if (!Yii::app()->getConfig("code")) {Yii::app()->setConfig("code", returnglobal('code'));}                // ??
		if (!Yii::app()->getConfig("action")) {Yii::app()->setConfig("action", returnglobal('action'));}          //Desired action
		if (!Yii::app()->getConfig("subaction")) {Yii::app()->setConfig("subaction", returnglobal('subaction'));} //Desired subaction
		if (!Yii::app()->getConfig("editedaction")) {Yii::app()->setConfig("editedaction", returnglobal('editedaction'));} // for html editor integration
	}

	/**
	 * Load and set session vars
	 *
	 * @access protected
	 * @return void
	 */
	protected function _sessioncontrol()
	{
		if (!Yii::app()->session["adminlang"] || Yii::app()->session["adminlang"]=='')
			Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

		Yii::import('application.libraries.Limesurvey_lang');
		$this->lang = new Limesurvey_lang(array('langcode' => Yii::app()->session['adminlang']));
		Yii::app()->setLang($this->lang);

		if (!empty($this->user_id))
			$this->_GetSessionUserRights($this->user_id);
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
		// Check if the DB is up to date
		if (!Yii::app()->db->schema->getTable('{{survey}}'))
		{
			$usrow = getGlobalSetting('DBVersion');
			if ((int) $usrow < Yii::app()->getConfig('dbversionnumber') && $action != 'update' && $action != 'authentication')
				$this->redirect($this->createUrl('update/db'));
		}

		if ($action != "update" && $action != "db")
			if (empty($this->user_id) && $action != "authentication"  && $action != "remotecontrol")
			{
				if (!($action == "index" && $action == "index"))
					Yii::app()->session['redirect_after_login'] = $this->createUrl('/');

				$this->redirect($this->createUrl('admin/authentication/login'));
			}

		return parent::run($action);
	}

	/**
	 * Routes all the actions to their respective places
	 *
	 * @access public
	 * @return array
	 */
	public function actions()
	{
		return array(
			'authentication' => 'application.controllers.admin.authentication',
			'index' => 'application.controllers.admin.index',
			'globalsettings' => 'application.controllers.admin.globalsettings',
			'quotas' => 'application.controllers.admin.quotas',
			'export' => 'application.controllers.admin.export',
			'assessments' =>'application.controllers.admin.assessments',
			'checkintegrity' => 'application.controllers.admin.checkintegrity',
			'survey' => 'application.controllers.admin.surveyaction',
			'printablesurvey' => 'application.controllers.admin.printablesurvey',
			'tokens' => 'application.controllers.admin.tokens',
			'surveypermission' => 'application.controllers.admin.surveypermission',
		);
	}

	/**
	 * Set Session User Rights
	 *
	 * @access public
	 * @return void
	 */
	public function _GetSessionUserRights($loginID)
	{
		$user = User::model()->findByPk($loginID);

		if (!empty($user))
		{
			Yii::app()->session['USER_RIGHT_SUPERADMIN'] = $user->superadmin;
			Yii::app()->session['USER_RIGHT_CREATE_SURVEY'] = ($user->create_survey || $user->superadmin);
			Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'] = ($user->participant_panel || $user->superadmin);
			Yii::app()->session['USER_RIGHT_CONFIGURATOR'] = ($user->configurator || $user->superadmin);
			Yii::app()->session['USER_RIGHT_CREATE_USER'] = ($user->create_user || $user->superadmin);
			Yii::app()->session['USER_RIGHT_DELETE_USER'] = ($user->delete_user || $user->superadmin);
			Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] = ($user->manage_template || $user->superadmin);
			Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] = ($user->manage_label || $user->superadmin);
		}

		// SuperAdmins
		// * original superadmin with uid=1 unless manually changed and defined
		//   in config-defaults.php
		// * or any user having USER_RIGHT_SUPERADMIN right

		// Let's check if I am the Initial SuperAdmin

		$user = User::model()->findByAttributes(array('parent_id' => 0));

		if (!is_null($user) && $user->uid == $loginID)
			$initialSuperadmin=true;
		else
			$initialSuperadmin=false;

		if ($initialSuperadmin === true)
		{
			Yii::app()->session['USER_RIGHT_SUPERADMIN'] = 1;
			Yii::app()->session['USER_RIGHT_INITIALSUPERADMIN'] = 1;
		}
		else
			Yii::app()->session['USER_RIGHT_INITIALSUPERADMIN'] = 0;
	}

	/**
	 * Prints Admin Header
	 *
	 * @access protected
	 * @param bool $meta
	 * @param bool $return
	 * @return mixed
	 */
	public function _getAdminHeader($meta = false, $return = false)
	{
		if (!Yii::app()->session["adminlang"] || Yii::app()->session["adminlang"]=='')
			Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

		$data = array();
		$data['adminlang'] = Yii::app()->session['adminlang'];

		//$data['admin'] = getLanguageRTL;
		$data['test'] = "t";
		$data['languageRTL']="";
		$data['styleRTL']="";

		Yii::app()->loadHelper("surveytranslator");

		if (getLanguageRTL(Yii::app()->session["adminlang"]))
		{
			$data['languageRTL'] = " dir=\"rtl\" ";
			$data['bIsRTL']=true;
		}
		else
		{
			$data['bIsRTL']=false;
		}

		$data['meta']="";
		if ($meta)
		{
			$data['meta']=$meta;
		}

		$data['baseurl'] = Yii::app()->baseUrl . '/';
		$data['datepickerlang']="";
		if (Yii::app()->session["adminlang"] != 'en')
			$data['datepickerlang'] = "<script type=\"text/javascript\" src=\"".$data['baseurl']."scripts/jquery/locale/jquery.ui.datepicker-".Yii::app()->session["adminlang"].".js\"></script>\n";

		$data['sitename'] = Yii::app()->getConfig("sitename");
		$data['admintheme'] = Yii::app()->getConfig("admintheme");

		if (Yii::app()->getConfig("css_admin_includes"))
			$data['css_admin_includes'] = array_unique(Yii::app()->getConfig("css_admin_includes"));

		$data['firebug'] = use_firebug();

		if (!empty(Yii::app()->session['dateformat']))
			$data['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);

		// Prepare flashmessage
		if (!empty(Yii::app()->session['flashmessage']) && Yii::app()->session['flashmessage'] != '')
		{
			//unset($_SESSION['flashmessage']);
			$data['flashmessage'] = Yii::app()->session['flashmessage'];
			unset(Yii::app()->session['flashmessage']);
		}

		return $this->renderPartial("/admin/super/header", $data, $return);
	}

	/**
	 * Prints Admin Footer
	 *
	 * @access protected
	 * @param string $url
	 * @param string $explanation
	 * @param bool $return
	 * @return mixed
	 */
	public function _getAdminFooter($url, $explanation, $return = false)
	{
		$clang = $this->lang;
		$data['clang'] = $clang;

		$data['versionnumber'] = Yii::app()->getConfig("versionnumber");

		$data['buildtext']="";
		if(Yii::app()->getConfig("buildnumber")!="") {
			$data['buildtext']= "Build ".Yii::app()->getConfig("buildnumber");
		}

		//If user is not logged in, don't print the version number information in the footer.
		$data['versiontitle'] = $clang->gT('Version');
		if (empty(Yii::app()->session['loginID']))
		{
			$data['versionnumber']="";
			$data['versiontitle']="";
			$data['buildtext']="";
		}

		$data['imageurl']= Yii::app()->getConfig("imageurl");
		$data['url']=$url;

		if (Yii::app()->getConfig("js_admin_includes"))
		{
			$data['js_admin_includes'] = array_unique(Yii::app()->getConfig("js_admin_includes"));
		}
		if (Yii::app()->getConfig("css_admin_includes"))
			$data['css_admin_includes'] = array_unique(Yii::app()->getConfig("css_admin_includes"));

		return $this->render("/admin/super/footer", $data, $return);

	}

	/**
	 * Shows a message...box
	 *
	 * @access public
	 * @param string $title
	 * @param string $message
	 * @param string $class
	 * @return void
	 */
	public function _showMessageBox($title,$message,$class="header ui-widget-header")
	{
		$data['title']=$title;
		$data['message']=$message;
		$data['class']=$class;
		$data['clang']=$this->lang;

		$this->render('/admin/super/messagebox', $data);
	}

	/**
	 * _showadminmenu() function returns html text for the administration button bar
	 *
	 * @access public
	 * @global string $homedir
	 * @global string $scriptname
	 * @global string $surveyid
	 * @global string $setfont
	 * @global string $imageurl
	 * @param int $surveyid
	 * @return string $adminmenu
	 */
	public function _showadminmenu($surveyid = false)
	{
		global $homedir, $scriptname, $setfont, $imageurl, $debug, $action, $updateavailable, $updatebuild, $updateversion, $updatelastcheck, $databasetype;

		$clang = $this->lang;
		$data['clang']= $clang;

		if (Yii::app()->session['pw_notify'] && Yii::app()->getConfig("debug")<2)  {
			Yii::app()->session['flashmessage'] = $clang->gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.");
		}

		$data['showupdate'] = (Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 && Yii::app()->getConfig("updatelastcheck")>0 && Yii::app()->getConfig("updateavailable")==1);
		$data['updateversion'] = Yii::app()->getConfig("updateversion");
		$data['updatebuild'] = Yii::app()->getConfig("updatebuild");
		$data['surveyid'] = $surveyid;

		$this->render("/admin/super/adminmenu", $data);

	}

	public function _css_admin_includes($include)
	{
		$css_admin_includes = Yii::app()->getConfig("css_admin_includes");
		$css_admin_includes[] = $include;
		Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
	}

	public function _js_admin_includes($include)
	{
		$js_admin_includes = Yii::app()->getConfig("js_admin_includes");
		$js_admin_includes[] = $include;
		Yii::app()->setConfig("js_admin_includes", $js_admin_includes);
	}

	public function _loadEndScripts()
	{
		static $out = false;
		if ($out)
			return true;
		$out = true;
		if (empty(Yii::app()->session['metaHeader']))
			Yii::app()->session['metaHeader'] = '';

		unset(Yii::app()->session['metaHeader']);

		if(empty(Yii::app()->session['checksessionpost']))
			Yii::app()->session['checksessionpost'] = '';

		$data['checksessionpost'] = Yii::app()->session['checksessionpost'];

		return $this->render('/admin/endScripts_view', $data);
	}
}