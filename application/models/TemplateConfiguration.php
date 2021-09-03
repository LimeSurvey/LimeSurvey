<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2015 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/*
 * This is the model class for table "{{template_configuration}}".
 *
 * NOTE: if you only need to access to the table, you don't need to call prepareTemplateRendering
 *
 * The followings are the available columns in table '{{template_configuration}}':
 * @property integer $id Primary key
 * @property string $template_name
 * @property integer $sid Survey ID
 * @property integer $gsid
 * @property integer $uid user ID
 * @property string $files_css
 * @property string $files_js
 * @property string $files_print_css
 * @property string $options
 * @property string $cssframework_name
 * @property string $cssframework_css
 * @property string $cssframework_js
 * @property string $packages_to_load
 * @property string $packages_ltr
 * @property string $packages_rtl
 * @property string $packages_rtl
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */

use LimeSurvey\Datavalueobjects\ThemeFileCategory;
use LimeSurvey\Datavalueobjects\ThemeFileInfo;
class TemplateConfiguration extends TemplateConfig
{

    /**
     * @var TemplateConfiguration $oParentTemplate The parent template name
     * A template configuration, in the database, can inherit from another one.
     * This used to manage the different configuration levels for a very same template: global, survey group, survey
     * This is not related to motherTemplate (inheritance between two different templates)
     */
    public $oParentTemplate;

    /**@var boolean Should the magic getters automatically retreives the parent value when field is set to inherit. Only turn to on for template rendering. There is no option inheritance on Manifest mode: values from XML are always used. */
    public $bUseMagicInherit = false;

    /**@var boolean Indicate if this entry in DB get created on the fly. If yes, because of Cache, it can need a page redirect  */
    public $bJustCreated = false;

    // Caches

    /** @var string $sPreviewImgTag the template preview image tag for the template list*/
    public $sPreviewImgTag;

    /** @var array $aInstancesFromTemplateName cache for method getInstanceFromTemplateName*/
    public static $aInstancesFromTemplateName;

    /** @var array $aInstancesFromTemplateName cache for method prepareTemplateRendering*/
    public static $aPreparedToRender;

    /** @var boolean $bTemplateCheckResult is the template valid?*/
    private $bTemplateCheckResult;

    /** @var string $sTypeIcon the type of template for icon (core vs user)*/
    private $sTypeIcon;

    /** @var array $aFilesTo cache for the method getFilesTo*/
    private $aFilesTo;

    /** @var array $aFrameworkAssetsToReplace cache for the method getFrameworkAssetsToReplace()*/
    private $aFrameworkAssetsToReplace;

    /** @var array $aReplacements cache for the method getFrameworkAssetsReplacement */
    private $aReplacements;

    /** @var array $Ofiles cache for the method getOfiles */
    private $Ofiles;

    public $generalFilesPath; //Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.'generalfiles'.DIRECTORY_SEPARATOR;

    /** @var int $showpopups show warnings when running survey */
    public $showpopups; //

    // for survey theme gridview columns
    public $template_type;
    public $template_extends;
    public $template_description;


    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{template_configuration}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('template_name', 'required'),
            array('id, sid, gsid', 'numerical', 'integerOnly'=>true),
            array('template_name', 'length', 'max'=>150),
            array('cssframework_name', 'length', 'max'=>45),
            array('files_css, files_js, files_print_css, options, cssframework_css, cssframework_js, packages_to_load', 'safe'),
            array('options', 'sanitizeImagePathsOnJson'),
            // The following rule is used by search().
            array('id, template_name, sid, gsid, files_css, files_js, files_print_css, options, cssframework_name, cssframework_css, cssframework_js, packages_to_load', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'template' => array(self::HAS_ONE, 'Template', array('name' => 'template_name'), 'together' => true),
        );
    }

    /** @inheritdoc */
    public function defaultScope()
    {
        return array('order'=> Yii::app()->db->quoteColumnName($this->getTableAlias(false, false).'.template_name'));
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'template_name' => gT('Template Name'),
            'sid' => 'SID',
            'gsid' => 'GSID',
            'files_css' => gT('Files CSS'),
            'files_js' => gT('Files JS'),
            'files_print_css' => gT('Files Print CSS'),
            'options' => gT('Options'),
            'cssframework_name' => gT('CSS framework name'),
            'cssframework_css' => gT('CSS framework CSS'),
            'cssframework_js' => gT('Css framework JS'),
            'packages_to_load' => gT('Packages to load'),
        );
    }

    /**
     * Gets an instance of a templateconfiguration by name
     *
     * @param string $sTemplateName
     * @return TemplateConfiguration
     */
    public static function getInstanceFromTemplateName($sTemplateName, $abstractInstance = false)
    {
        if (!empty(self::$aInstancesFromTemplateName[$sTemplateName]) && !$abstractInstance) {
            return self::$aInstancesFromTemplateName[$sTemplateName];
        }

        $oInstance = self::model()->find(
            'template_name=:template_name AND sid IS NULL AND gsid IS NULL',
            array(':template_name'=>$sTemplateName)
        );

        // If the survey configuration table of the wanted template doesn't exist (eg: manually deleted), then we provide the default one.
        if (!is_a($oInstance, 'TemplateConfiguration')) {
            $oInstance = self::getInstanceFromTemplateName(getGlobalSetting('defaulttheme'));
        }

        if($abstractInstance === true) {
            return $oInstance;
        }

        self::$aInstancesFromTemplateName[$sTemplateName] = $oInstance;

        return $oInstance;
    }

    /**
     * Returns a TemplateConfiguration Object based on a surveygroup ID
     * If no instance is existing, it will create one.
     *
     * @param integer $iSurveyGroupId
     * @param string $sTemplateName
     * @return TemplateConfiguration
     */
    public static function getInstanceFromSurveyGroup($iSurveyGroupId, $sTemplateName = null, $abstractInstance = false)
    {
        //if a template name is given also check against that
        $sTemplateName = $sTemplateName != null ? $sTemplateName : SurveysGroups::model()->findByPk($iSurveyGroupId)->template;

        $criteria = new CDbCriteria();
        $criteria->addCondition('gsid=:gsid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('gsid' => $iSurveyGroupId, 'template_name' => $sTemplateName);
        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);

        // No specific template configuration for this surveygroup => create one
        // TODO: Move to SurveyGroup creation, right now the 'lazy loading' approach is ok.
        if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration') && $sTemplateName != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName($sTemplateName, $abstractInstance);
            $oTemplateConfigurationModel->bUseMagicInherit = false;
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->sid = null;
            $oTemplateConfigurationModel->gsid = $iSurveyGroupId;
            $oTemplateConfigurationModel->setToInherit();
            $oTemplateConfigurationModel->save();

            $oTemplateConfigurationModel->bJustCreated = true;
        }

        return $oTemplateConfigurationModel;
    }

    /**
     * Returns a TemplateConfiguration Object based on a surveyID
     * If no instance is existing, it will create one.
     *
     * @param integer $iSurveyId
     * @param string $sTemplateName
     * @return TemplateConfiguration
     */
    public static function getInstanceFromSurveyId($iSurveyId, $sTemplateName = null, $abstractInstance = false)
    {

        //if a template name is given also check against that
        $sTemplateName = $sTemplateName != null ? $sTemplateName : Survey::model()->findByPk($iSurveyId)->template;

        $criteria = new CDbCriteria();
        $criteria->addCondition('sid=:sid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('sid' => $iSurveyId, 'template_name' => $sTemplateName);

        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);


        // No specific template configuration for this surveygroup => create one
        // TODO: Move to SurveyGroup creation, right now the 'lazy loading' approach is ok.
        if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration') && $sTemplateName != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName($sTemplateName, $abstractInstance);
            $oTemplateConfigurationModel->bUseMagicInherit = false;
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->gsid = null;
            $oTemplateConfigurationModel->sid = $iSurveyId;
            $oTemplateConfigurationModel->setToInherit();
            $oTemplateConfigurationModel->save();
        }

        return $oTemplateConfigurationModel;
    }



    /**
     * Returns a Theme options array based on a surveyID
     *
     * @param integer $iSurveyId
     * @param bool $bInherited should inherited theme option values be used?
     * @return array
     */

    public static function getThemeOptionsFromSurveyId($iSurveyId = 0, $bInherited = false)
    {
        $aTemplateConfigurations = array();
        // fetch all themes belonging to $iSurveyId
        $criteria = new CDbCriteria();
        $criteria->addCondition('sid=:sid');
        $criteria->params = array('sid' => $iSurveyId);
        $oTemplateConfigurations = self::model()->findAll($criteria);

        if ($bInherited){ // inherited values
            foreach ($oTemplateConfigurations as $key => $oTemplateConfiguration) {
                $oTemplateConfiguration->bUseMagicInherit = true;
                $oTemplateConfiguration->setOptions();
                // set array with values
                $aTemplateConfigurations[$key]['id'] = null;
                $aTemplateConfigurations[$key]['sid'] = $iSurveyId;
                $aTemplateConfigurations[$key]['template_name'] = $oTemplateConfiguration->template_name;
                $aTemplateConfigurations[$key]['config']['options'] = (array)$oTemplateConfiguration->oOptions;
            }
        } else { // db values
            foreach ($oTemplateConfigurations as $key => $oTemplateConfiguration) {
                $oTemplateConfiguration->bUseMagicInherit = false;
                $oAttributes = $oTemplateConfiguration->attributes;
                // set array with values
                $aTemplateConfigurations[$key]['id'] = null;
                $aTemplateConfigurations[$key]['sid'] = $iSurveyId;
                $aTemplateConfigurations[$key]['template_name'] = $oAttributes['template_name'];
                $aTemplateConfigurations[$key]['config']['options'] = isJson($oAttributes['options'])?(array)json_decode($oAttributes['options']):$oAttributes['options'];
            }
        }

        return $aTemplateConfigurations;
    }

    /**
     * For a given survey, it checks if its theme have a all the needed configuration entries (survey + survey group). Else, it will create it.
     * @TODO: recursivity for survey group
     * @param int $iSurveyId
     * @return TemplateConfiguration the template configuration for the survey group
     */
    public static function checkAndcreateSurveyConfig($iSurveyId)
    {
        //if a template name is given also check against that
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $sTemplateName  = $oSurvey->template;
        $iSurveyGroupId = $oSurvey->gsid;

        $criteria = new CDbCriteria();
        $criteria->addCondition('sid=:sid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('sid' => $iSurveyId, 'template_name' => $sTemplateName);

        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);


        // TODO: Move to SurveyGroup creation, right now the 'lazy loading' approach is ok.
        if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration') && $sTemplateName != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName($sTemplateName);
            $oTemplateConfigurationModel->bUseMagicInherit = false;
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->gsid = null;
            $oTemplateConfigurationModel->sid = $iSurveyId;
            $oTemplateConfigurationModel->setToInherit();
            $oTemplateConfigurationModel->save();
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('gsid=:gsid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('gsid' => $iSurveyGroupId, 'template_name' => $sTemplateName);
        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);

        if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration') && $sTemplateName != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName($sTemplateName);
            $oTemplateConfigurationModel->bUseMagicInherit = false;
            $oTemplateConfigurationModel->id = null;
            $oTemplateConfigurationModel->isNewRecord = true;
            $oTemplateConfigurationModel->sid = null;
            $oTemplateConfigurationModel->gsid = $iSurveyGroupId;
            $oTemplateConfigurationModel->setToInherit();
            $oTemplateConfigurationModel->save();
        }

        return $oTemplateConfigurationModel;
    }

    /**
     * Get an instance of a fitting TemplateConfiguration
     * NOTE: for rendering prupose, you should never call this function directly, but rather Template::getInstance.
     *       if force_xmlsettings_for_survey_rendering is on, then the configuration from the XML file should be loaded, not the one from database
     *
     * @param string $sTemplateName
     * @param integer $iSurveyGroupId
     * @param integer $iSurveyId
     * @return TemplateConfiguration
     */
    public static function getInstance($sTemplateName = null, $iSurveyGroupId = null, $iSurveyId = null, $abstractInstance = false)
    {

        $oTemplateConfigurationModel = new TemplateConfiguration();

        if ($sTemplateName != null && $iSurveyGroupId == null && $iSurveyId == null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName($sTemplateName, $abstractInstance);
        }

        if ($iSurveyGroupId != null && $iSurveyId == null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromSurveyGroup($iSurveyGroupId, $sTemplateName, $abstractInstance);
        }

        if ($iSurveyId != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromSurveyId($iSurveyId, $sTemplateName, $abstractInstance);
        }

        return $oTemplateConfigurationModel;

    }


    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->join = 'INNER JOIN {{templates}} AS template ON '.Yii::app()->db->quoteColumnName("t.template_name").' = template.name';
        //Don't show surveyspecifi settings on the overview
        $criteria->addCondition('t.sid IS NULL');
        $criteria->addCondition('t.gsid IS NULL');
        $criteria->addCondition('template.name IS NOT NULL');

        $criteria->compare('id', $this->id);
        $criteria->compare('template_name', $this->template_name, true);
        $criteria->compare('files_css', $this->files_css, true);
        $criteria->compare('files_js', $this->files_js, true);
        $criteria->compare('files_print_css', $this->files_print_css, true);
        $criteria->compare('options', $this->options, true);
        $criteria->compare('cssframework_name', $this->cssframework_name, true);
        $criteria->compare('cssframework_css', $this->cssframework_css, true);
        $criteria->compare('cssframework_js', $this->cssframework_js, true);
        $criteria->compare('packages_to_load', $this->packages_to_load, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function searchGrid()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $pageSizeTemplateView = Yii::app()->user->getState('pageSizeTemplateView', Yii::app()->params['defaultPageSize']);
        $criteria = new CDbCriteria;

        $criteria->join = 'INNER JOIN {{templates}} AS template ON '.Yii::app()->db->quoteColumnName("t.template_name").' = template.name';
        $criteria->together = true;
        //Don't show surveyspecifi settings on the overview
        $criteria->addCondition('t.sid IS NULL');
        $criteria->addCondition('t.gsid IS NULL');
        $criteria->addCondition('template.name IS NOT NULL');

        $criteria->compare('id', $this->id);
        $criteria->compare('template_name', $this->template_name, true);
        $criteria->compare('files_css', $this->files_css, true);
        $criteria->compare('files_js', $this->files_js, true);
        $criteria->compare('files_print_css', $this->files_print_css, true);
        $criteria->compare('options', $this->options, true);
        $criteria->compare('cssframework_name', $this->cssframework_name, true);
        $criteria->compare('cssframework_css', $this->cssframework_css, true);
        $criteria->compare('cssframework_js', $this->cssframework_js, true);
        $criteria->compare('packages_to_load', $this->packages_to_load, true);
        $criteria->compare('template.description', $this->template_description, true);
        $criteria->compare('template.extends', $this->template_extends, true);



        $coreTemplates = Template::getStandardTemplateList();
        if ($this->template_type == 'core'){
            $criteria->addInCondition('template_name', $coreTemplates);
        } elseif ($this->template_type == 'user'){
            $criteria->addNotInCondition('template_name', $coreTemplates);
        }

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>$pageSizeTemplateView,
            ),
        ));
    }

    /**
     * Twig statements can be used in Theme description
     */
    public function getDescription()
    {
      $sDescription = $this->template->description;

      // If wrong Twig in manifest, we don't want to block the whole list rendering
      // Note: if no twig statement in the description, twig will just render it as usual
      try {
          $sDescription = Yii::app()->twigRenderer->convertTwigToHtml($this->template->description);
      } catch (\Exception $e) {
        // It should never happen, but let's avoid to anoy final user in production mode :) 
        if (YII_DEBUG){
          Yii::app()->setFlashMessage("Twig error in template ".$this->template->name." description <br> Please fix it and reset the theme <br>".$e, 'error');
        }
      }

      return $sDescription;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TemplateConfiguration the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }


    /**
     * Create a new entry in {{templates}} and {{template_configuration}} table using the template manifest
     * @param string $sTemplateName the name of the template to import
     * @return boolean true on success | exception
     * @throws Exception
     */
    public static function importManifest($sTemplateName, $aDatas = array())
    {
        if (!empty($aDatas['extends'])) {

            $oMotherTemplate = self::getInstanceFromTemplateName($aDatas['extends']);
            if (is_a($oMotherTemplate, 'TemplateConfiguration')) {
                $aDatas['api_version']     = $oMotherTemplate->template->api_version;
                $aDatas['view_folder']     = $oMotherTemplate->template->view_folder;
                $aDatas['author_email']    = $oMotherTemplate->template->author_email;
                $aDatas['author_url']      = $oMotherTemplate->template->author_url;
                $aDatas['copyright']       = $oMotherTemplate->template->copyright;
                $aDatas['version']         = $oMotherTemplate->template->version;
                $aDatas['license']         = $oMotherTemplate->template->license;
                $aDatas['files_folder']    = $oMotherTemplate->template->files_folder;
                $aDatas['aOptions']        = (empty($aDatas['aOptions'])) ? json_decode($oMotherTemplate->options) : $aDatas['aOptions'];
            }
        }

        return parent::importManifest($sTemplateName, $aDatas);
    }

    public function setToInherit()
    {
        $this->files_css         = 'inherit';
        $this->files_js          = 'inherit';
        $this->files_print_css   = 'inherit';
        $this->options           = 'inherit';
        $this->cssframework_name = 'inherit';
        $this->cssframework_css  = 'inherit';
        $this->cssframework_js   = 'inherit';
        $this->packages_to_load  = 'inherit';
    }

    public function checkTemplate()
    {
        if (empty($this->bTemplateCheckResult)) {
            $this->bTemplateCheckResult = true;
            if (!is_object($this->template) || (is_object($this->template) && !Template::checkTemplateXML($this->template->folder))) {
                $this->bTemplateCheckResult = false;
            }
        }
        return $this->bTemplateCheckResult;
    }

    public function setBasics($sTemplateName = '', $iSurveyId = '', $bUseMagicInherit = false)
    {
        $this->bUseMagicInherit = $bUseMagicInherit;
        $this->sTemplateName = $this->template->name;
        $this->setIsStandard(); // Check if  it is a CORE template
        $this->path = ($this->isStandard)
            ? Yii::app()->getConfig("standardthemerootdir").DIRECTORY_SEPARATOR.$this->template->folder.DIRECTORY_SEPARATOR
            : Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.$this->template->folder.DIRECTORY_SEPARATOR;
    }

    /**
     * Add a file replacement in the field `file_{css|js|print_css}` in table {{template_configuration}},
     * eg: {"replace": [ {original files to replace here...}, "css/template.css",]}
     * In general, should be called from TemplateManifest, after adding a file replacement inside the manifest.
     *
     * @param string $sFile the file to replace
     * @param string $sType css|js
     * @return bool|void
     * @throws Exception
     */
    public function addFileReplacement($sFile, $sType)
    {
        $sField = 'files_'.$sType;
        $oFiles = (array) json_decode($this->$sField);

        $oFiles['replace'][] = $sFile;

        $this->$sField = json_encode($oFiles);

        if ($this->save()) {
            return true;
        } else {
            throw new Exception("could not add $sFile to  $sField replacements! ".$this->getErrors());
        }
    }

    public function getTypeIcon()
    {
        if (empty($this->sTypeIcon)) {
            $this->sTypeIcon = (Template::isStandardTemplate($this->template->name)) ?gT("Core theme") : gT("User theme");
        }
        return $this->sTypeIcon;
    }


    public function getButtons()
    {
        // don't show any buttons if user doesn't have update permission
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            return '';
        }
        $gsid          = Yii::app()->request->getQuery('id', null);
        $sEditorUrl = Yii::app()->getController()->createUrl('admin/themes/sa/view', array("templatename"=>$this->template_name));
        $sUninstallUrl = Yii::app()->getController()->createUrl('admin/themeoptions/sa/uninstall/');
        $sExtendUrl    = Yii::app()->getController()->createUrl('admin/themes/sa/templatecopy');
        $sResetUrl     = Yii::app()->getController()->createUrl('admin/themeoptions/sa/reset/', array("gsid"=>$gsid));
        $sOptionUrl    = (App()->getController()->action->id == "surveysgroups")?Yii::app()->getController()->createUrl('admin/themeoptions/sa/updatesurveygroup', array("id"=>$this->id, "gsid"=>$gsid)):Yii::app()->getController()->createUrl('admin/themeoptions/sa/update', array("id"=>$this->id));

        $sEditorLink = "<a
            id='template_editor_link_".$this->template_name."'
            href='".$sEditorUrl."'
            class='btn btn-default btn-block'>
                <span class='icon-templates'></span>
                ".gT('Theme editor')."
            </a>";

        $OptionLink = '';
        if ($this->hasOptionPage) {
            $OptionLink .= "<a
                id='template_options_link_".$this->template_name."'
                href='".$sOptionUrl."'
                class='btn btn-default btn-block'>
                    <span class='fa fa-tachometer'></span>
                    ".gT('Theme options')."
                </a>";
        }

        $sUninstallLink = '<a
            id="remove_fromdb_link_'.$this->template_name.'"
            href="'.$sUninstallUrl.'"
            data-post=\'{ "templatename": "'.$this->template_name.'" }\'
            data-text="'.gT('This will reset all the specific configurations of this theme.').'<br>'.gT('Do you want to continue?').'"
            title="'.gT('Uninstall this theme').'"
            class="btn btn-danger btn-block selector--ConfirmModal">
                <span class="icon-trash"></span>
                '.gT('Uninstall').'
            </a>';

         $sExtendLink = '<a
            id="extendthis_'.$this->template_name.'"
            href="'.$sExtendUrl.'"
            data-post=\''
            .json_encode([
                "copydir" => $this->template_name,
                "action" => "templatecopy",
                "newname" => ["value"=> "extends_".$this->template_name, "type" => "text", "class" => "form-control col-sm-12"]
            ])
            .'\'
            data-text="'.gT('Please type in the new theme name above.').'"
            title="'.sprintf(gT('Type in the new name to extend %s'), $this->template_name).'"
            class="btn btn-primary btn-block selector--ConfirmModal">
                <i class="fa fa-copy"></i>
                '.gT('Extend').'
            </a>';

        $sResetLink = '<a
                id="remove_fromdb_link_'.$this->template_name.'"
                href="'.$sResetUrl.'"
                data-post=\'{ "templatename": "'.$this->template_name.'" }\'
                data-text="'.gT('This will reload the configuration file of this theme.').'<br>'.gT('Do you want to continue?').'"
                title="'.gT('Reset this theme').'"
                class="btn btn-warning btn-block selector--ConfirmModal">
                    <span class="icon-trash"></span>
                    '.gT('Reset').'
            </a>';

        if (App()->getController()->action->id == "surveysgroups") {
            $sButtons = $OptionLink;
        } else {
            $sButtons = $sEditorLink.$OptionLink.$sExtendLink;

            if ($this->template_name != getGlobalSetting('defaulttheme')) {
                $sButtons .= $sUninstallLink;
            } else {
                $sButtons .= '
                    <a
                        class="btn btn-danger btn-block"
                        disabled
                        data-toggle="tooltip"
                        title="' . gT('You cannot uninstall the default template.').'"
                    >
                        <span class="icon-trash"></span>
                        '.gT('Uninstall').'
                    </a>
                ';
            }
        }

        $sButtons .= $sResetLink;


        return $sButtons;
    }

    public function getHasOptionPage()
    {
        $filteredName = Template::templateNameFilter($this->template->name);
        $oRTemplate = $this->prepareTemplateRendering($filteredName);

        $sOptionFile = 'options'.DIRECTORY_SEPARATOR.'options.twig';
        while (!file_exists($oRTemplate->path.$sOptionFile)) {

            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                return false;
                break;
            }
            $oRTemplate = $oMotherTemplate->prepareTemplateRendering($this->template->name);
        }
        return true;
    }

    /**
     * Set a value on a given option at global setting level (survey level not affected).
     * Will be used to turn ON ajax mode on update.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setGlobalOption($name, $value)
    {
        if ($this->options != 'inherit') {
            $oOptions = json_decode($this->options);

            if (empty($this->sid)) {
                $oOptions->$name = $value;
                $sOptions = json_encode($oOptions);
                $this->options = $sOptions;
                $this->save();
            }
        }
    }

    /**
     * Apply options from XML configuration for all missing template options
     *
     * @return void
     */
    public function addOptionFromXMLToLiveTheme()
    {
        if ($this->options != 'inherit') {
            $oOptions = get_object_vars(json_decode($this->options));
            $oTemplateConfigurationModel = new TemplateManifest;
            $oTemplateConfigurationModel->setBasics();
            $oXmlOptions = get_object_vars($oTemplateConfigurationModel->config->options);

            // compare template options to options from the XML and add if missing
            foreach ($oXmlOptions as $key=>$value){
                if (!array_key_exists($key, $oOptions)){
                  $this->addOptionToLiveTheme($key, $value);
                }
            }
        }
    }

    /**
     * Add an option definition to the current theme.
     * Will be used to turn ON ajax mode on update.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addOptionToLiveTheme($name, $value)
    {
        if ($this->options != 'inherit') {

            $oOptions = json_decode($this->options);
            $oOptions->$name = $value;
            $sOptions = json_encode($oOptions);
            $this->options = $sOptions;
            $this->save();

        }
    }


    /**
     * Set option (unless if options is set to "inherit").
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setOption($name, $value)
    {
        if ($this->options != 'inherit') {
            $oOptions = json_decode($this->options);

            $oOptions->$name = $value;
            $sOptions = json_encode($oOptions);
            $this->options = $sOptions;
            $this->save();
        }
    }

    private function _getRelativePath($from, $to) {
        $dir = explode(DIRECTORY_SEPARATOR, is_file($from) ? dirname($from) : rtrim($from, DIRECTORY_SEPARATOR));
        $file = explode(DIRECTORY_SEPARATOR, $to);

        while ($dir && $file && ($dir[0] == $file[0])) {
            array_shift($dir);
            array_shift($file);
        }
        return str_repeat('..'.DIRECTORY_SEPARATOR, count($dir)) . implode(DIRECTORY_SEPARATOR, $file);
    }

    /**
     * Return image information
     * @param string $file with Path
     * @return array|null
     */
    private function _getImageInfo($file, $pathPrefix = '')
    {
        if(!file_exists($file)) {
            return;
        }
        // Currently it's private and only used one time, before put this function in twig : must validate directory is inside rootdir
        $checkImage = LSYii_ImageValidator::validateImage($file);
        if (!$checkImage['check']) {
            return;
        }
        $filePath = $this->_getRelativePath(Yii::app()->getConfig('rootdir'), $file);
        $previewFilePath = App()->getAssetManager()->publish($file);
        $fileName = basename($file);
        return ['preview' => $previewFilePath, 'filepath' => $pathPrefix . $fileName, 'filepathOptions' => $filePath, 'filename' => $fileName];
    }

    protected function getOptionPageAttributes()
    {
        $aData = $this->attributes;
        $aData['maxFileSize'] = getMaximumFileUploadSize();
        $aData['imageFileList'] = [];
        $categoryList = $this->getFileCategories();

        // Compose list of image files for each category
        foreach ($categoryList as $category) {
            // Get base path for category
            $pathPrefix = empty($category->pathPrefix) ? '' : $category->pathPrefix;
            $basePath = $category->path;
            // If the category is theme, add the "files folder" to the base path, as that's the directory to scan for files
            if ($category->name == 'theme') {
                $filesFolder = $this->getAttributeValue('files_folder');
                $basePath = $basePath . $filesFolder;
                $pathPrefix = $pathPrefix . $filesFolder;
            }
            // Get full list of files
            $fileList = Template::getOtherFiles($basePath);

            // Keep only image files
            foreach ($fileList as $file) {
                $imageInfo = $this->_getImageInfo($basePath . $file['name'], $pathPrefix);
                if ($imageInfo) {
                    $aData['imageFileList'][] = array_merge(
                        [
                            'group' => $category->title,
                        ],
                        $imageInfo
                    );
                }
            };
        }

        return $aData;
    }

    public function getOptionPage()
    {
        $oSimpleInheritance = Template::getInstance($this->template->name, $this->sid, $this->gsid, null, true);
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering($this->template->name);

        $oTemplate = $this->prepareTemplateRendering($this->template->name);

        $renderArray = array('templateConfiguration' => $oTemplate->getOptionPageAttributes());

        $oTemplate->setOptions();
        $oTemplate->setOptionInheritance();

        //We add some extra values to the option page
        //This is just a dirty hack, and somewhere in the future we will correct it
        $renderArray['oParentOptions'] = array_merge(
            ((array) $oSimpleInheritanceTemplate->oOptions),
            array('packages_to_load' =>  $oTemplate->packages_to_load,
            'files_css' => $oTemplate->files_css)
        );

        return Yii::app()->twigRenderer->renderOptionPage($oTemplate, $renderArray);
    }


    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @param TemplateConfiguration $oTemplate
     * @param string $sType
     * @return array
     * @internal param string $jFiles json
     */
    protected function getFilesTo($oTemplate, $sType, $sAction)
    {
        // Todo: make it in a recursive way
        if ( !empty($this->aFilesTo[$oTemplate->template->name]) ) {
            if ( !empty($this->aFilesTo[$oTemplate->template->name][$sType]) ) {
                if ( !empty($this->aFilesTo[$oTemplate->template->name][$sType][$sAction] ) ){
                    return $this->aFilesTo[$oTemplate->template->name][$sType][$sAction];
                }else{
                    $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
                }
            }else{
                $this->aFilesTo[$oTemplate->template->name][$sType]           = array();
                $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
            }
        }else{
            $this->aFilesTo[$oTemplate->template->name]                   = array();
            $this->aFilesTo[$oTemplate->template->name][$sType]           = array();
            $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
        }


        $sField = 'files_'.$sType;
        $oFiles = $this->getOfiles($oTemplate, $sField);

        $aFiles = array();

        if ($oFiles) {

            foreach ($oFiles as $action => $aFileList) {

                if (is_array($aFileList)) {
                    if ($action == $sAction) {

                        // Specific inheritance of one of the value of the json array
                        if ($aFileList[0] == 'inherit') {
                            $aParentjFiles = (array) json_decode($oTemplate->getParentConfiguration->$sField);
                            $aFileList = $aParentjFiles[$action];
                        }

                        $aFiles = array_merge($aFiles, $aFileList);
                    }
                }
            }
        }

        $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = $aFiles;
        return $aFiles;
    }


    /**
     * Get the json files (to load/replace/remove) from  a theme, and checks if its correctly formated
     *
     * @param $oTemplate the theme to check
     * @param $sField name of the DB field to get (file_css, file_js, file_print_css)
     */
    protected function getOfiles($oTemplate, $sField)
    {
        if ( !empty($this->Ofiles[$oTemplate->template->name])){
          if (!empty($this->Ofiles[$oTemplate->template->name][$sField] )) {
              return $this->Ofiles[$oTemplate->template->name][$sField];
            }else{
                $this->Ofiles[$oTemplate->template->name][$sField] = array();
            }
        }else{
            $this->Ofiles[$oTemplate->template->name] = array();
            $this->Ofiles[$oTemplate->template->name][$sField] = array();
        }


        $files = $oTemplate->$sField;
        $oFiles = [];
        if (!empty($files)) {
            $oFiles = json_decode($files, true);
            if ($oFiles === null) {
                Yii::app()->setFlashMessage(
                    sprintf(
                        gT('Error: Malformed JSON: Field %s must be either a JSON array or the string "inherit". Found "%s".'),
                        $sField,
                        $oFiles
                    ),
                    'error'
                );
                return false;
            }
        }

        $this->Ofiles[$oTemplate->template->name][$sField] = $oFiles;
        return $oFiles;
    }

    /**
     * Proxy for Yii::app()->clientScript->removeFileFromPackage()
     *
     * @param string $sPackageName     string   name of the package to edit
     * @param string $sType            string   the type of settings to change (css or js)
     * @param $aSettings        array    array of local setting
     * @return array
     */
    protected function removeFileFromPackage($sPackageName, $sType, $aSettings)
    {
        foreach ($aSettings as $sFile) {
            Yii::app()->clientScript->removeFileFromPackage($sPackageName, $sType, $sFile);
        }
    }

    /**
     * Configure the mother template (and its mother templates)
     * This is an object recursive call to TemplateConfiguration::prepareTemplateRendering()
     */
    protected function setMotherTemplates()
    {
        if (!empty($this->template->extends)) {
            $sMotherTemplateName   = $this->template->extends;
            $instance = TemplateConfiguration::getInstanceFromTemplateName($sMotherTemplateName);
            $instance->template->checkTemplate();
            $this->oMotherTemplate = $instance->prepareTemplateRendering($sMotherTemplateName, null);
        }
    }

    /**
     * Get the closest template in the hierarchy that has the definition for $attribute
     *
     * @param TemplateConfiguration $oRTemplate
     * @param string $attribute
     * @return TemplateConfiguration
     */
    protected function getTemplateConfigurationForAttribute($oRTemplate, $attribute)
    {
        while (empty($oRTemplate->getRelatedTemplate()->$attribute)) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                //throw new Exception("can't find a template for template '{$oRTemplate->template_name}' for path '$sPath'.");
                $this->uninstallIncorectTheme($this->template_name);
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }
        return $oRTemplate;
    }

    /**
     * Uninstall a theme and, display error message, and redirect to theme list
     * @param string $sTemplateName
     */
    protected function uninstallIncorectTheme($sTemplateName)
    {
        TemplateConfiguration::uninstall($sTemplateName);
        Yii::app()->setFlashMessage(sprintf(gT("Theme '%s' has been uninstalled because it's not compatible with this LimeSurvey version."), $sTemplateName), 'error');
        Yii::app()->getController()->redirect(array("admin/themeoptions"));
        Yii::app()->end();
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     */
    protected function setThisTemplate()
    {

        $this->apiVersion       = (!empty($this->template->api_version)) ? $this->template->api_version : null; // Mandtory setting in config XML
        $this->viewPath         = $this->path.$this->getTemplateConfigurationForAttribute($this, 'view_folder')->template->view_folder.DIRECTORY_SEPARATOR;
        $this->filesPath        = $this->path.$this->getTemplateConfigurationForAttribute($this, 'files_folder')->template->files_folder.DIRECTORY_SEPARATOR;
        $this->generalFilesPath = Yii::app()->getConfig("userthemerootdir").DIRECTORY_SEPARATOR.'generalfiles'.DIRECTORY_SEPARATOR;
        // Options are optional
        $this->setOptions();

        // Not mandatory (use package dependances)
        $this->setCssFramework();
        $this->packages = $this->getDependsPackages($this);
        if (!empty($this->packages_to_load)) {
            $templateToLoadPackages = json_decode($this->packages_to_load);
            if (!empty($templateToLoadPackages->add)) {
                $this->packages = array_merge($templateToLoadPackages->add,  $this->packages);
            }
            if (!empty($templateToLoadPackages->remove)) {
                $this->packages =  array_diff($this->packages, $templateToLoadPackages->remove);
            }
        }

        // Add depend package according to packages
        $this->depends = array_merge($this->depends, $this->packages);
    }

    private function setCssFramework()
    {
        if (!empty($this->cssframework_name)) {
            $this->cssFramework = new \stdClass();
            $this->cssFramework->name = $this->cssframework_name;
            $this->cssFramework->css  = json_decode($this->cssframework_css);
            $this->cssFramework->js   = json_decode($this->cssframework_js);

        } else {
            $this->cssFramework = '';
        }
    }

    protected function setOptions()
    {
        $this->oOptions = array();
        if (!empty($this->options)) {
            $this->oOptions = json_decode($this->options);
        }

        $this->setOptionInheritance();
    }

    protected function setOptionInheritance()
    {
        $oOptions = $this->oOptions;

        if (!empty($oOptions)) {
            foreach ($oOptions as $sKey => $sOption) {
                    $oOptions->$sKey = $this->getOptionKey($sKey);
            }
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getOptionKey($key)
    {
        $aOptions = (array) json_decode($this->options);
        if (isset($aOptions[$key])) {
            $value = $aOptions[$key];
            if ($value === 'inherit') {
                $oParentConfig = $this->getParentConfiguration();
                if ($oParentConfig->id != $this->id){
                    return $this->getParentConfiguration()->getOptionKey($key);
                }else{
                    $this->uninstallIncorectTheme($this->template_name);
                }

            }
            return  $value;
        } else {
            return null;
        }
    }

    protected function addMotherTemplatePackage($packages)
    {
        if (!empty($this->template->extends)) {
            $sMotherTemplateName = (string) $this->template->extends;
            $packages[]          = 'survey-template-'.$sMotherTemplateName;
        }
        return $packages;
    }

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @param boolean $bInlcudeRemove   also get the files to remove
     * @return array
     */
    protected function getFrameworkAssetsToReplace($sType, $bInlcudeRemove = false)
    {
        if (empty($this->aFrameworkAssetsToReplace)) {
            $this->aFrameworkAssetsToReplace = array();
        }

        $this->aFrameworkAssetsToReplace[$sType] = array();

        $sFieldName  = 'cssframework_'.$sType;
        $aFieldValue = (array) json_decode($this->$sFieldName);

        if (!empty($aFieldValue) && !empty($aFieldValue['replace'])) {
            $this->aFrameworkAssetsToReplace[$sType] = (array) $aFieldValue['replace'];

            // Inner field inheritance
            foreach ($this->aFrameworkAssetsToReplace[$sType] as $key => $aFiles) {
                foreach ($aFiles as $sReplacement) {
                    if ($sReplacement == "inherit") {
                        $aParentReplacement = $this->getParentConfiguration()->getFrameworkAssetsToReplace($sType);
                        $this->aFrameworkAssetsToReplace[$sType][$key][1] = $aParentReplacement[$key][1];
                    }
                }
            }

            if ($bInlcudeRemove && isset($aFieldValue['remove'])) {
                $this->aFrameworkAssetsToReplace[$sType] = array_merge($this->aFrameworkAssetsToReplace, (array) $aFieldValue['remove']);
            }
        }


        return $this->aFrameworkAssetsToReplace[$sType];
    }

    /**
     * Get the list of file replacement from Engine Framework
     * @param string  $sType            css|js the type of file
     * @return array
     */
    protected function getFrameworkAssetsReplacement($sType)
    {
        if (empty($this->aReplacements)) {
            $this->aReplacements = array();
        }
        $this->aReplacements[$sType] = array();

        $aFrameworkAssetsToReplace = $this->getFrameworkAssetsToReplace($sType);

        foreach ($aFrameworkAssetsToReplace as $key => $aAsset) {
            $aReplace = $aAsset[1];
            $this->aReplacements[$sType][] = $aReplace;
        }


        return $this->aReplacements[$sType];
    }


    public function getParentConfiguration()
    {
        if (empty($this->oParentTemplate)) {

            //check for surveygroup id if a survey is given
            if ($this->sid != null) {
                $oSurvey = Survey::model()->findByPk($this->sid);
                $oParentTemplate = Template::getTemplateConfiguration($this->template->name, null, $oSurvey->gsid);
                if (is_a($oParentTemplate, 'TemplateConfiguration')) {
                    $this->oParentTemplate = $oParentTemplate;
                    $this->oParentTemplate->bUseMagicInherit = $this->bUseMagicInherit;
                    return $this->oParentTemplate;
                }
            }

            //check for surveygroup id if a surveygroup is given
            if ($this->sid == null && $this->gsid != null) {
                $oSurveyGroup = SurveysGroups::model()->findByPk($this->gsid);
                //Switch if the surveygroup inherits from a parent surveygroup
                if ($oSurveyGroup != null && $oSurveyGroup->parent_id != 0) {
                    $oParentTemplate = Template::getTemplateConfiguration($this->template->name, null, $oSurveyGroup->parent_id);
                    if (is_a($oParentTemplate, 'TemplateConfiguration')) {
                        $this->oParentTemplate = $oParentTemplate;
                        $this->oParentTemplate->bUseMagicInherit = $this->bUseMagicInherit;
                        return $this->oParentTemplate;
                    }

                }
            }

            //in the endcheck for general global template
            $this->oParentTemplate = Template::getTemplateConfiguration($this->template_name, null, null);
            $this->oParentTemplate->bUseMagicInherit = $this->bUseMagicInherit;
            return $this->oParentTemplate;
        }
        return $this->oParentTemplate;
    }


    /**
     * Change the template name inside the configuration entries (called from template editor)
     * NOTE: all tests (like template exist, etc) are done from template controller.
     *
     * @param string $sOldName The old name of the template
     * @param string $sNewName The newname of the template
     */
    public static function rename($sOldName, $sNewName)
    {
        self::model()->updateAll(array('template_name' => $sNewName), "template_name = :oldname", array(':oldname'=>$sOldName));
    }


    /**
     * Proxy for the AR method to manage the inheritance
     * If one of the field that can be inherited is set to "inherit", then it will return the value of its parent
     * NOTE: this is recursive, if the parent field itself is set to inherit, then it will the value of the parent of the parent, etc
     *
     * @param string $name the name of the attribute
     * @return mixed
     */
    public function __get($name)
    {
        $aAttributesThatCanBeInherited = array('files_css', 'files_js', 'options', 'cssframework_name', 'cssframework_css', 'cssframework_js', 'packages_to_load');

        if (in_array($name, $aAttributesThatCanBeInherited) && $this->bUseMagicInherit) {
            // Full inheritance of the whole field
            $sAttribute = parent::__get($name);
            if ($sAttribute === 'inherit') {
                // NOTE: this is object recursive (if parent configuration field is set to inherit, then it will lead to this method again.)
                $sAttribute = $this->getParentConfiguration()->$name;
            }
        } else {
            $sAttribute = parent::__get($name);
        }

        return $sAttribute;
    }

    /**
     * @return string
     */
    public function getTemplateAndMotherNames()
    {
        $oRTemplate = $this;
        $sTemplateNames = $this->sTemplateName;

        while (!empty($oRTemplate->oMotherTemplate)) {

            $sTemplateNames .= ' ' . $oRTemplate->template->extends;
            $oRTemplate      = $oRTemplate->oMotherTemplate;
            if (!($oRTemplate instanceof TemplateConfiguration)) {
                // Throw alert: should not happen
                break;
            }
        }

        return $sTemplateNames;
    }

    /**
     * Get the global template configuration with same name as $this.
     * The global config has no sid, no gsid and no uid.
     * @return TemplateConfiguration
     */
    public function getGlobalParent()
    {
        return self::model()->find(
            'sid IS NULL AND uid IS NULL and gsid IS NULL AND template_name = :template_name',
            [':template_name'=>$this->template_name]
        );
    }

    /**
     * Get showpopups value from config or template configuration
     */
    public function getshowpopups(){
        $config = (int)Yii::app()->getConfig('showpopups');
        if ($config == 2){
            if (isset($this->oOptions->showpopups)){
                $this->showpopups = (int)$this->oOptions->showpopups;
            } else {
               $this->showpopups = 1;
           }
        } else {
            $this->showpopups = $config;
        }
    }

    /**
     * Set each option key value to 'inherit' instead of having only one 'inherit' value for options.
     * Keys are fetched from parent xml configuration.
     */
    public function setOptionKeysToInherit(){
        $oTemplate = $this->getParentConfiguration();
        $oTemplate->bUseMagicInherit = true;
        $oTemplate->setOptions();

        $aOptions = array();
        if ((string)$this->options === 'inherit'){
            foreach ($oTemplate->oOptions as $key => $value) {
                $aOptions[$key] = 'inherit';
            }
            $this->options = json_encode($aOptions);
        }
    }

    /**
     * Returns a list of file categories for the theme.
     * Each category is related to a directory which holds files for the theme.
     * This files are usually listed to be selected as values for options.
     *
     * @return LimeSurvey\Datavalueobjects\ThemeFileCategory[]
     */
    public function getFileCategories()
    {
        // We need to determine the paths first. They may already be set, but if they're not, we need to get them from the template.
        // Note that the template cannot be accessed as relation until the model is saved.
        $template = $this->getRelatedTemplate();
        if (empty($this->path)) {
            $isStandard = Template::isStandardTemplate($this->template_name);
            $path = $isStandard
                ? Yii::app()->getConfig("standardthemerootdir") . DIRECTORY_SEPARATOR . $template->folder . DIRECTORY_SEPARATOR
                : Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $template->folder . DIRECTORY_SEPARATOR;
        } else {
            $path = $this->path;
        }
        $generalFilesPath = !empty($this->generalFilesPath) ? $this->generalFilesPath : Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . 'generalfiles' . DIRECTORY_SEPARATOR;

        /** @var LimeSurvey\Datavalueobjects\ThemeFileCategory[] */
        $categoryList = [];
        $categoryList[] = new ThemeFileCategory('generalfiles', gT("Global"), $generalFilesPath, 'image::generalfiles::');
        $categoryList[] = new ThemeFileCategory('theme', gT("Theme"), $path, 'image::theme::');
        if (!empty($this->sid)) {
            $categoryList[] = new ThemeFileCategory('survey', gT("Survey"), Yii::app()->getConfig('uploaddir') . '/surveys/' . $this->sid . '/images/', 'image::survey::');
        }
        return $categoryList;
    }

    /**
     * Returns the virtual path for $path
     * If $path is not valid, returns null.
     *
     * @param string $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     *
     * @return string|null the virtual path if it's valid, of null if it's not.
     */
    public function getVirtualThemeFilePath($path)
    {
        /** @var LimeSurvey\Datavalueobjects\ThemeFileInfo|null */
        $fileInfo = $this->getThemeFileInfo($path);

        if (empty($fileInfo)) {
            return null;
        }

        return $fileInfo->virtualPath;
    }

    /**
     * Returns the real aboslute path of $path
     * If $path is not valid, returns null.
     *
     * @param string $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     *
     * @return string|null the real absolute path if it's valid, of null if it's not.
     */
    public function getRealThemeFilePath($path)
    {
        /** @var LimeSurvey\Datavalueobjects\ThemeFileInfo|null */
        $fileInfo = $this->getThemeFileInfo($path);

        if (empty($fileInfo)) {
            return null;
        }

        return $fileInfo->realPath;
    }

    /**
     * Validates $path and returns its file info
     * Rules:
     *  - valid virtual path
     *  - real path for an available category
     *
     * @param string $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     *
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|null the file info if it's valid, or null if it's not.
     */
    private function getThemeFileInfo($path)
    {
        if (is_null($path)) {
            return null;
        }

        /** @var LimeSurvey\Datavalueobjects\ThemeFileCategory[] */
        $categoryList = $this->getFileCategories();

        // Check if the path matches the virtual path category format
        $prefix = $this->getVirtualPathPrefix($path);
        if (!empty($prefix)) {
            // Find category that matches the prefix
            $filteredCategories = array_filter($categoryList, function ($v) use ($prefix) { return $v->pathPrefix == $prefix; });
            if (empty($filteredCategories)) {
                return null;    // No category matched the path's prefix
            }
            $category = reset($filteredCategories);
            $categoryPath = realpath($category->path) . '/';

            // Validate that the file exists
            $realPath = realpath($categoryPath . '/' . substr($path, strlen($prefix)));

            // If the file exists and no traversing is done (the real path starts with the category's base path),
            // return the file info
            if ($realPath !== false && strpos($realPath, $categoryPath) === 0) {
                $virtualPath = str_replace($categoryPath, $category->pathPrefix, $realPath);
                return new ThemeFileInfo($realPath, $virtualPath, $category);
            } else {
                return null;
            }
        }

        // Path doesn't match the virtual path category format, so we try the determine if it belongs to a category.

        // Handle the case of absolute paths and paths relative to the root dir.
        $result = $this->getThemeFileInfoFromAbsolutePath($path, $categoryList);
        if ($result !== false) {
            return $result;
        }

        // If we got here, the path was not absolute (nor relative to the root dir), so we check
        // if it's relative to a category.
        $result = $this->getThemeFileInfoFromRelativePath($path, $categoryList);
        if ($result !== false) {
            return $result;
        }

        // The path didn't belong to any category
        return null;
    }

    /**
     * Returns a path's ThemeFileInfo if it's an absolute path or relative to the root dir.
     * The function returns false if the path is not found, and null if it's found but doesn't
     * match a category.
     * @param string $path
     * @param LimeSurvey\Datavalueobjects\ThemeFileCategory[] $categoryList
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|null|false
     */
    private function getThemeFileInfoFromAbsolutePath($path, $categoryList)
    {
        // Check if the path is relative to the root dir or an absolute path
        $absolutePath = realpath(Yii::app()->getConfig('rootdir') . '/' . $path);
        if ($absolutePath === false && strpos($path, '/') === 0) {
            $absolutePath = realpath($path);
        }
        // If the path was absolute (or relative to the root dir), we check if it's within
        // a category's bounds.
        if (!empty($absolutePath)) {
            foreach ($categoryList as $category) {
                // Get real path for the category
                $categoryPath = realpath($category->path) . '/';
                if (strpos($absolutePath, $categoryPath) === 0) {
                    $virtualPath = str_replace($categoryPath, $category->pathPrefix, $absolutePath);
                    return new ThemeFileInfo($absolutePath, $virtualPath, $category);
                }
            }
            // The path didn't belong to any category
            return null;
        }
        return false;
    }

    /**
     * Returns a path's ThemeFileInfo if it's relative to a category.
     * The function returns false if the path is not relative to any category.
     * @param string $path
     * @param LimeSurvey\Datavalueobjects\ThemeFileCategory[] $categoryList
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|false
     */
    private function getThemeFileInfoFromRelativePath($path, $categoryList)
    {
        foreach ($categoryList as $category) {
            // Get real path for the category
            $categoryPath = realpath($category->path) . '/';

            // Get the realpath for the file. 
            $realPath = realpath($categoryPath . '/' . $path);

            // If the path is not found try with next category
            if ($realPath === false) {
                continue;
            }

            // Ok, now we know the path exists and is relative to the category, but
            // it could be traversing out.
            // Now let's check if it's within this category's bounds.

            // If the real path starts with category's path, we return the file info.
            if (strpos($realPath, $categoryPath) === 0) {
                $virtualPath = str_replace($categoryPath, $category->pathPrefix, $realPath);
                return new ThemeFileInfo($realPath, $virtualPath, $category);
            }
        }
        return false;
    }

    /**
     * Sanitizes the theme options making sure that paths are valid.
     * Options that match a file will be cleared if the file is not valid,
     * or replaced with the virtual path if the file is valid.
     */
    public function sanitizeImagePathsOnJson($attribute, $params)
    {
        // Validates all options of the theme. Not only classic ones which are expected to hold a path,
        // as other options may hold a path as well (eg. custom theme options)
        $decodedOptions = json_decode($this->$attribute, true);
        if (is_array($decodedOptions)) {
            foreach($decodedOptions as &$value) {
                if (empty($value) || $value == 'inherit') {
                    continue;
                }
                // Alternative A - If option value is a path that matches a virtual path, transform the value to the virtual path
                $virtualPath = $this->getVirtualThemeFilePath($value);
                if (!empty($virtualPath)) {
                    $value = $virtualPath;
                    continue;
                }
                // Alternative B - If any of the following:
                // - option value matches a virtual path format or
                // - option value matches a real existing path to a file either relative to the root LS installation or to the current workgin dir or absolute
                // Mark the value as invalid, as that's not allowed
                if ($this->isVirtualPath($value) || realpath($value) !== false || realpath(Yii::app()->getConfig('rootdir') . '/' . $value) !== false) {
                    $value = 'invalid:' . $value;
                }
            }
            $this->$attribute = json_encode($decodedOptions);
        }
    }

    /**
     * Returns the virtual path prefix of $virtualPath.
     *
     * @param string $virtualPath
     * @return string|null the virtual path prefix, or null if $virtualPath doesn't match the format
     */
    private function getVirtualPathPrefix($virtualPath)
    {
        if (preg_match('/(image::\w+::)/', $virtualPath, $m)) {
            return $m[1];
        } else {
            return null;
        }
    }

    /**
     * Returns true if $value matches the virtual path format.
     * It doesn't check the path validity.
     *
     * @param string $value
     * @return boolean
     */
    private function isVirtualPath($value)
    {
        return !empty($this->getVirtualPathPrefix($value));
    }

    /**
     * Returns the related Template.
     * The template can only be accessed as a relation when this model is stored in the DB. Before
     * saving, $this->template is null. In that case, this method will load the approriate Template.
     * @return Template|null
     */
    private function getRelatedTemplate()
    {
        $template = !empty($this->template) ? $this->template : Template::model()->findByAttributes(['name' => $this->template_name]);
        return $template;
    }

    /**
     * Returns the value of the specified attribute ($attributeName) from
     * the closest Template in the hierarchy.
     *
     * @param string $attributeName
     * @return mixed
     */
    private function getAttributeValue($attributeName)
    {
        return $this->getTemplateConfigurationForAttribute($this, $attributeName)->template->$attributeName;
    }
}
