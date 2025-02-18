<?php

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

/**
 * This is the model class for table "{{template_configuration}}".
 *
 * NOTE: if you only need to access to the table, you don't need to call prepareTemplateRendering
 *
 * The following are the available columns in table '{{template_configuration}}':
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
 * @property Template $template
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class TemplateConfiguration extends TemplateConfig
{
    /**
     * @var TemplateConfiguration $oParentTemplate The parent template name
     * A template configuration, in the database, can inherit from another one.
     * This used to manage the different configuration levels for a very same template: global, survey group, survey
     * This is not related to motherTemplate (inheritance between two different templates)
     */
    public $oParentTemplate;

    /**@var boolean
     * Should the magic getters automatically retreives the parent value when field is set to inherit. Only turn to
     * on for template rendering. There is no option inheritance on Manifest mode: values from XML are always used.
     */
    public $bUseMagicInherit = false;

    /**@var boolean
     * Indicate if this entry in DB get created on the fly. If yes, because of Cache, it can need a page redirect
     */
    public $bJustCreated = false;

    // Caches

    /** @var string $sPreviewImgTag the template preview image tag for the template list*/
    public $sPreviewImgTag;

    /** @var array $aInstancesFromTemplateName cache for method getInstanceFromTemplateName*/
    public static $aInstancesFromTemplateName;

    /** @var array $aPreparedToRender cache for method prepareTemplateRendering*/
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
     * @todo document me
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{template_configuration}}';
    }

    /**
     * @todo document me
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('template_name', 'required'),
            array('id, sid, gsid', 'numerical', 'integerOnly' => true),
            array('template_name', 'filter', 'filter' => function ($value) {
                return sanitize_filename($value, false, false, false);
            }),
            array('template_name', 'length', 'max' => 150),
            array('cssframework_name', 'length', 'max' => 45),
            array('files_css, files_js, files_print_css, options, cssframework_css, cssframework_js, packages_to_load',
                'safe'),
            array('options', 'sanitizeImagePathsOnJson'),
            // The following rule is used by search().
            array('id, template_name, sid, gsid, files_css, files_js, files_print_css, options, cssframework_name, cssframework_css, cssframework_js, packages_to_load', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @todo document me
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'template' => array(self::HAS_ONE, 'Template', array('name' => 'template_name'), 'together' => true),
        );
    }

    /**
     * @todo document me
     *
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'template_name' => gT('Template name'),
            'sid' => 'Sid',
            'gsid' => 'Gsid',
            'files_css' => gT('Files CSS'),
            'files_js' => gT('Files JS'),
            'files_print_css' => gT('Files Print CSS'),
            'options' => gT('Options'),
            'cssframework_name' => gT('CSS framework name'),
            'cssframework_css' => gT('CSS framework CSS'),
            'cssframework_js' => gT('CSS framework JS'),
            'packages_to_load' => gT('Packages to load'),
        );
    }

    /**
     * Gets an instance of a templateconfiguration by name
     *
     * @param string $sTemplateName
     * @param boolean $abstractInstance
     * @return TemplateConfiguration
     */
    public static function getInstanceFromTemplateName($sTemplateName, $abstractInstance = false)
    {
        if (!empty(self::$aInstancesFromTemplateName[$sTemplateName]) && !$abstractInstance) {
            return self::$aInstancesFromTemplateName[$sTemplateName];
        }

        $oInstance = self::model()->find(
            'template_name=:template_name AND sid IS NULL AND gsid IS NULL',
            array(':template_name' => $sTemplateName)
        );

        // If the survey configuration table of the wanted template doesn't exist (eg: manually deleted),
        // then we provide the default one.
        if (!is_a($oInstance, 'TemplateConfiguration')) {
            $oInstance = self::getInstanceFromTemplateName(App()->getConfig('defaulttheme'));
        }

        if ($abstractInstance === true) {
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
     * @param boolean $abstractInstance
     * @return TemplateConfiguration
     */
    public static function getInstanceFromSurveyGroup($iSurveyGroupId, $sTemplateName = null, $abstractInstance = false)
    {
        //if a template name is given also check against that
        $sTemplateName = $sTemplateName != null
            ? $sTemplateName
            : SurveysGroups::model()->findByPk($iSurveyGroupId)->template;

        $criteria = new CDbCriteria();
        $criteria->addCondition('gsid=:gsid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('gsid' => $iSurveyGroupId, 'template_name' => $sTemplateName);
        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);

        // No specific template configuration for this surveygroup => create one
        // TODO: Move to SurveyGroup creation, right now the 'lazy loading' approach is ok.
        if (!is_a($oTemplateConfigurationModel, 'TemplateConfiguration') && $sTemplateName != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName(
                $sTemplateName,
                $abstractInstance
            );
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
     * @param boolean $abstractInstance
     * @return TemplateConfiguration
     */
    public static function getInstanceFromSurveyId($iSurveyId, $sTemplateName = null, $abstractInstance = false)
    {
        // set template name if it does not exists or if it is inherit
        if ($sTemplateName === null || $sTemplateName == 'inherit') {
            $oSurvey = Survey::model()->findByPk($iSurveyId);
            // set real value from inheritance
            if (!empty($oSurvey->oOptions->template)) {
                $sTemplateName = $oSurvey->oOptions->template;
            }
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('sid=:sid');
        $criteria->addCondition('template_name=:template_name');
        $criteria->params = array('sid' => $iSurveyId, 'template_name' => $sTemplateName);

        $oTemplateConfigurationModel = TemplateConfiguration::model()->find($criteria);

        // If the TemplateConfiguration could not be found go up the inheritance hierarchy
        if (empty($oTemplateConfigurationModel)) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName(
                $sTemplateName,
                $abstractInstance
            );
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

        if ($bInherited) { // inherited values
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
                $aTemplateConfigurations[$key]['config']['options'] = isJson($oAttributes['options'])
                    ? json_decode((string) $oAttributes['options'], true)
                    : $oAttributes['options'];
            }
        }

        return $aTemplateConfigurations;
    }

    /**
     * For a given survey, it checks if its theme have a all the needed configuration entries (survey + survey group).
     * Else, it will create it.
     * @param int $iSurveyId
     * @return TemplateConfiguration the template configuration for the survey group
     */
    public static function checkAndcreateSurveyConfig($iSurveyId)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $iSurveyGroupId = $oSurvey->gsid;
        // set real value from inheritance
        $sTemplateName = $oSurvey->oOptions->template;

        // load or create a new entry if none is found based on $iSurveyId
        self::getInstanceFromSurveyId($iSurveyId, $sTemplateName);

        // load or create a new entry if none is found based on $iSurveyGroupId
        $oGroupTemplateConfigurationModel = self::getInstanceFromSurveyGroup($iSurveyGroupId, $sTemplateName);

        return $oGroupTemplateConfigurationModel;
    }

    /**
     * Get an instance of a fitting TemplateConfiguration
     * NOTE: for rendering prupose, you should never call this function directly, but rather Template::getInstance.
     * if force_xmlsettings_for_survey_rendering is on, then the configuration from the XML file should be loaded,
     * not the one from database
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
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromTemplateName(
                $sTemplateName,
                $abstractInstance
            );
        }

        if ($iSurveyGroupId != null && $iSurveyId == null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromSurveyGroup(
                $iSurveyGroupId,
                $sTemplateName,
                true
            );
        }

        if ($iSurveyId != null) {
            $oTemplateConfigurationModel = TemplateConfiguration::getInstanceFromSurveyId(
                $iSurveyId,
                $sTemplateName,
                true
            );
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

        $criteria = new LSDbCriteria();

        $criteria->join = 'INNER JOIN {{templates}} AS tmpl ON ' .
            App()->db->quoteColumnName("t.template_name") .
            ' = tmpl.name';
        //Don't show survey specific settings on the overview
        $criteria->addCondition('t.sid IS NULL');
        $criteria->addCondition('t.gsid IS NULL');
        $criteria->addInCondition('tmpl.name', array_keys(Template::getTemplateList()));

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
            'criteria' => $criteria
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @param integer $gsid Survey group, else get global
     * @return CActiveDataProvider
     * @throws Exception
     */
    public function searchGrid(?int $gsid = null)
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $pageSizeTemplateView = App()->user->getState('pageSizeTemplateView', App()->params['defaultPageSize']);
        $criteria = new LSDbCriteria();

        $criteria->join = 'INNER JOIN {{templates}} AS template ON ' .
            App()->db->quoteColumnName("t.template_name") .
            ' = template.name';
        $criteria->together = true;
        //Don't show surveyspecifi settings on the overview
        $criteria->addCondition('t.sid IS NULL');
        $criteria->addInCondition('template.name', array_keys(Template::getTemplateList()));

        if ($gsid !== null) {
            /* Group configuration */
            $criteria->compare('t.gsid', $gsid);
        } else {
            /* Global configuration */
            $criteria->addCondition('t.gsid IS NULL');
        }

        $criteria->compare('id', $this->id);
        if (!empty($this->template_name)) {
            $templateNameEscaped = strtr($this->template_name, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $criteria->addCondition('template.name LIKE :templatename1 OR template.title LIKE :templatename2');
            $criteria->params[':templatename1'] = '%' . $templateNameEscaped . '%';
            $criteria->params[':templatename2'] = '%' . $templateNameEscaped . '%';
        }
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

        Yii::import('application.helpers.SurveyThemeHelper');
        $coreTemplates = SurveyThemeHelper::getStandardTemplateList();
        if ($this->template_type == 'core') {
            $criteria->addInCondition('template_name', $coreTemplates);
        } elseif ($this->template_type == 'user') {
            $criteria->addNotInCondition('template_name', $coreTemplates);
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSizeTemplateView,
            ),
            'sort' => array(
                'defaultOrder' => 'template_name ASC' // Set default order here
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
            $sDescription = App()->twigRenderer->convertTwigToHtml($this->template->description);
            $sDescription = viewHelper::purified($sDescription);
        } catch (\Exception $e) {
          // It should never happen, but let's avoid to anoy final user in production mode :)
            if (YII_DEBUG) {
                App()->setFlashMessage(
                    "Twig error in template " .
                    $this->template->name .
                    " description <br> Please fix it and reset the theme <br>" .
                    $e,
                    'error'
                );
            }
        }

          return $sDescription;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
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
     *
     * @param string $sTemplateName the name of the template to import
     * @param array $aDatas Data
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
                $aDatas['aOptions']        = (empty($aDatas['aOptions']))
                    ? json_decode($oMotherTemplate->options)
                    : $aDatas['aOptions'];
            }
        }

        return parent::importManifest($sTemplateName, $aDatas);
    }

    /**
     * @todo document me
     */
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

    /**
     * Check if the template exists and is valid
     *
     * @return bool
     */
    public function checkTemplate()
    {
        if (empty($this->bTemplateCheckResult)) {
            $this->bTemplateCheckResult = true;
            if (
                !is_object($this->template) ||
                (is_object($this->template) && !Template::checkTemplateXML($this->template))
            ) {
                $this->bTemplateCheckResult = false;
            }
        }
        return $this->bTemplateCheckResult;
    }

    /**
     * Sets bUseMagicInherit sTemplate isStandard and path of the theme
     *
     * @param string $sTemplateName
     * @param string $iSurveyId
     * @param bool $bUseMagicInherit
     */
    public function setBasics($sTemplateName = '', $iSurveyId = '', $bUseMagicInherit = false)
    {
        $this->bUseMagicInherit = $bUseMagicInherit;
        $this->sTemplateName = $this->template->name;
        $this->setIsStandard(); // Check if  it is a CORE template
        $this->path = ($this->isStandard)
            ? App()->getConfig("standardthemerootdir") . DIRECTORY_SEPARATOR . $this->template->folder . DIRECTORY_SEPARATOR
            : App()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $this->template->folder . DIRECTORY_SEPARATOR;
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
        $sField = 'files_' . $sType;
        $oFiles = json_decode((string) $this->$sField, true);

        $oFiles['replace'][] = $sFile;

        $this->$sField = json_encode($oFiles);

        if ($this->save()) {
            return true;
        } else {
            throw new Exception("could not add $sFile to  $sField replacements! " . $this->getErrors());
        }
    }

    /**
     * @todo document me
     *
     * @return string
     */
    public function getTypeIcon()
    {
        if (empty($this->sTypeIcon)) {
            Yii::import('application.helpers.SurveyThemeHelper');
            $this->sTypeIcon = (SurveyThemeHelper::isStandardTemplate($this->template->name)) ?
                gT("Core theme") :
                gT("User theme");
        }
        return $this->sTypeIcon;
    }

    /**
     * Displays survey theme action buttons
     *
     * @return string
     */
    public function getButtons()
    {
        /* What ? We can get but $this->getAttribute ??? */
        $gsid = App()->request->getQuery('id', null); // $this->gsid;
        // don't show any buttons if user doesn't have update permission
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            /* Global settings */
            if (empty($gsid) || App()->getController()->action->id != "surveysgroups") {
                return '';
            }
            /* SurveysGroups settings */
            $oSurveysGroups = SurveysGroups::model()->findByPk($gsid);
            if (empty($oSurveysGroups)) {
                return '';
            }
            if (!$oSurveysGroups->hasPermission('surveys', 'update')) {
                return '';
            }
        }
        /* Use sanitized filename for previous bad upload */
        $templateName = sanitize_filename($this->template_name, false, false, false);
        $sEditorUrl = App()->getController()->createUrl(
            'admin/themes/sa/view',
            array("templatename" => $templateName)
        );
        $sExtendUrl = App()->getController()->createUrl('admin/themes/sa/templatecopy');
        $sOptionUrl = (App()->getController()->action->id == "surveysgroups") ?
            App()->getController()->createUrl(
                'themeOptions/updateSurveyGroup',
                array("id" => $this->id, "gsid" => $gsid)
            ) :
            App()->getController()->createUrl(
                'themeOptions/update',
                array("id" => $this->id)
            );

        $sUninstallUrl = Yii::app()->getController()->createUrl('themeOptions/uninstall/');
        $sResetUrl     = Yii::app()->getController()->createUrl('themeOptions/reset/', array("gsid" => (int) $gsid));

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Theme editor'),
            'url'              => $sEditorUrl,
            'linkId'           => 'template_editor_link_' . $this->id,
            'linkClass'        => '',
            'iconClass'        => 'ri-brush-fill',
            'enabledCondition' => App()->getController()->action->id !== "surveysgroups",

        ];

        $dropdownItems[] = [
            'title'            => gT('Theme options'),
            'url'              => $sOptionUrl,
            'linkId'           => 'template_options_link_' . $this->id ,
            'linkClass'        => '',
            'iconClass'        => 'ri-dashboard-3-fill',
            'enabledCondition' => $this->getHasOptionPage(),
        ];


        $dropdownItems[] = [
            'title'            => gT('Extend'),
            'url'              => $sExtendUrl,
            'linkId'           => 'extendthis_' . $this->id,
            'linkClass'        => 'selector--ConfirmModal ',
            'iconClass'        => 'ri-file-copy-line text-success',
            'enabledCondition' => App()->getController()->action->id !== "surveysgroups",
            'linkAttributes'   => [
                'title'            => sprintf(gT('Type in the new name to extend %s'), $templateName),
                'data-button-no'   => gt('Cancel'),
                'data-button-yes'  => gt('Extend'),
                'data-text'        => gT('Please type in the new theme name above.'),
                'data-post'        => json_encode([
                    "copydir" => $templateName,
                    "action"  => "templatecopy",
                    "newname" => [ "value" => "extends_" . $templateName,
                                    "type" => "text",
                                    "class" => "form-control col-md-12" ]
                    ]),
            ]
        ];


        $dropdownItems[] = [
            'title'            => gT('Uninstall'),
            'url'              => $sUninstallUrl,
            'linkId'           => 'remove_fromdb_link_' . $this->id,
            'linkClass'        => 'selector--ConfirmModal ',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => App()->getController()->action->id !== "surveysgroups" &&
                                    $templateName != App()->getConfig('defaulttheme'),
            'linkAttributes'   => [
                'title'            => gT('Uninstall this theme'),
                'data-button-no'   => gt('Cancel'),
                'data-button-yes'  => gt('Uninstall'),
                'data-text'        => gT('This will reset all the specific configurations of this theme.')
                                         . '<br>' . gT('Do you want to continue?'),
                'data-post'        => json_encode([ "templatename" => $templateName ]),
                'data-button-type' => "btn-danger"
            ]
        ];

        $dropdownItems[] = [
            'title'            => gT('Reset'),
            'url'              => $sResetUrl,
            'linkId'           => 'remove_fromdb_link_' . $this->id,
            'linkClass'        => 'selector--ConfirmModal ',
            'iconClass'        => 'ri-refresh-line text-warning',
            'enabledCondition' => App()->getController()->action->id !== "surveysgroups",
            'linkAttributes'   => [
                'title'            => gT('Reset this theme'),
                'data-button-no'   => gt('Cancel'),
                'data-button-yes'  => gt('Reset'),
                'data-text'        => gT('This will reload the configuration file of this theme.') . '<br>' . gT('Do you want to continue?'),
                'data-post'        => json_encode([ "templatename" => $templateName ]),
                'data-button-type' => "btn-warning"
            ]
        ];
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Returns true if this theme or any mothertemplate has a TemplateConfiguration set
     *
     * @return bool
     * @throws Exception
     */
    public function getHasOptionPage()
    {
        $filteredName = Template::templateNameFilter($this->template->name);
        $oRTemplate = $this->prepareTemplateRendering($filteredName);

        $sOptionFile = 'options' . DIRECTORY_SEPARATOR . 'options.twig';
        while (!file_exists($oRTemplate->path . $sOptionFile)) {
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
            $oTemplateConfigurationModel = new TemplateManifest();
            $oTemplateConfigurationModel->setBasics();
            $oXmlOptions = get_object_vars($oTemplateConfigurationModel->config->options);

            // compare template options to options from the XML and add if missing
            foreach ($oXmlOptions as $key => $value) {
                if (!array_key_exists($key, $oOptions)) {
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

    /**
     * @todo document me
     *
     * @param $from
     * @param $to
     * @return string
     */
    private function getRelativePath($from, $to)
    {
        $dir = explode(DIRECTORY_SEPARATOR, is_file($from) ? dirname((string) $from) : rtrim((string) $from, DIRECTORY_SEPARATOR));
        $file = explode(DIRECTORY_SEPARATOR, (string) $to);

        while ($dir && $file && ($dir[0] == $file[0])) {
            array_shift($dir);
            array_shift($file);
        }
        return str_repeat('..' . DIRECTORY_SEPARATOR, count($dir)) . implode(DIRECTORY_SEPARATOR, $file);
    }

    /**
     * Return image information
     *
     * @param string $file with Path
     * @return array|null
     */
    private function getImageInfo($file, $pathPrefix = '')
    {
        if (!file_exists($file)) {
            return;
        }
        // Currently it's private and only used one time, before put this function in twig :
        // must validate directory is inside rootdir
        $checkImage = LSYii_ImageValidator::validateImage($file);
        if (!$checkImage['check']) {
            return;
        }
        $filePath = $this->getRelativePath(App()->getConfig('rootdir'), $file);
        $previewFilePath = App()->getAssetManager()->publish($file);
        $fileName = basename($file);
        return [
            'preview' => $previewFilePath,
            'filepath' => $pathPrefix . $fileName,
            'filepathOptions' => $filePath,
            'filename' => $fileName
        ];
    }

    /**
     * @todo document me
     *
     * @return array
     */
    public function getOptionPageAttributes()
    {
        $aData = $this->attributes;
        $aData['maxFileSize'] = getMaximumFileUploadSize();
        $aData['imageFileList'] = [];
        Yii::import('application.helpers.SurveyThemeHelper');
        $categoryList = SurveyThemeHelper::getFileCategories($this->template_name, $this->sid);

        // Compose list of image files for each category
        foreach ($categoryList as $category) {
            // Get base path for category
            $pathPrefix = empty($category->pathPrefix) ? '' : $category->pathPrefix;
            $basePath = $category->path;
            // If the category is theme, add the "files folder" to the base path, as that's the directory to scan for files
            if ($category->name == 'theme') {
                $filesFolder = $this->getAttributeValue('files_folder') . DIRECTORY_SEPARATOR;
                $basePath = $basePath . $filesFolder;
                $pathPrefix = $pathPrefix . $filesFolder;
            }
            // Get full list of files
            $fileList = Template::getOtherFiles($basePath);
            // Order File List alphabetically
            usort($fileList, function ($a, $b) {
                return strcasecmp((string) $a['name'], (string) $b['name']);
            });
            // Keep only image files
            foreach ($fileList as $file) {
                $imageInfo = $this->getImageInfo($basePath . $file['name'], $pathPrefix);
                if ($imageInfo) {
                    $aData['imageFileList'][$imageInfo['filepath']] = array_merge(
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

    /**
     * Prepares the rendering of the custom options.js and options.twig that can be used in every theme
     *
     * @return mixed
     */
    public function getOptionPage()
    {
        $oSimpleInheritance = Template::getInstance($this->template->name, $this->sid, $this->gsid, null, true);
        $oSimpleInheritance->options = 'inherit';
        $oSimpleInheritanceTemplate = $oSimpleInheritance->prepareTemplateRendering($this->template->name);

        // TODO: It's not clear which class prepareTemplateRendering() returns or should return.
        /** @var Template */
        $oTemplate = $this->prepareTemplateRendering($this->template->name);

        $renderArray = array('templateConfiguration' => $oTemplate->getOptionPageAttributes());

        $oTemplate->setToInherit();
        $oTemplate->setOptions();

        $oOptions = (array) $oSimpleInheritanceTemplate->oOptions;

        //We add some extra values to the option page
        //This is just a dirty hack, and somewhere in the future we will correct it
        $renderArray['oParentOptions'] = array_merge(
            ($oOptions),
            array(
                'packages_to_load' =>  $oTemplate->packages_to_load,
                'files_css' => $oTemplate->files_css
            )
        );

        $renderArray['aOptionAttributes'] = TemplateManifest::getOptionAttributes($oSimpleInheritance->path);
        $renderArray['aFontOptions'] = TemplateManifest::getFontDropdownOptions();
        return App()->twigRenderer->renderOptionPage($oTemplate, $renderArray);
    }

    /**
     * From a list of json files in db it will generate a PHP array ready to use by removeFileFromPackage()
     *
     * @param TemplateConfiguration $oTemplate
     * @param string $sType
     * @param string $sAction Action
     * @return array
     * @internal param string $jFiles json
     */
    protected function getFilesTo($oTemplate, $sType, $sAction)
    {
        // Todo: make it in a recursive way
        if (!empty($this->aFilesTo[$oTemplate->template->name])) {
            if (!empty($this->aFilesTo[$oTemplate->template->name][$sType])) {
                if (!empty($this->aFilesTo[$oTemplate->template->name][$sType][$sAction])) {
                    return $this->aFilesTo[$oTemplate->template->name][$sType][$sAction];
                } else {
                    $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
                }
            } else {
                $this->aFilesTo[$oTemplate->template->name][$sType]           = array();
                $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
            }
        } else {
            $this->aFilesTo[$oTemplate->template->name]                   = array();
            $this->aFilesTo[$oTemplate->template->name][$sType]           = array();
            $this->aFilesTo[$oTemplate->template->name][$sType][$sAction] = array();
        }

        $sField = 'files_' . $sType;
        $oFiles = $this->getOfiles($oTemplate, $sField);

        $aFiles = array();

        if ($oFiles) {
            foreach ($oFiles as $action => $aFileList) {
                if (is_array($aFileList)) {
                    if ($action == $sAction) {
                        // Specific inheritance of one of the value of the json array
                        if ($aFileList[0] == 'inherit') {
                            $aParentjFiles = json_decode((string) $oTemplate->getParentConfiguration->$sField, true);
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
     * @return bool|mixed
     */
    protected function getOfiles($oTemplate, $sField)
    {
        if (!empty($this->Ofiles[$oTemplate->template->name])) {
            if (!empty($this->Ofiles[$oTemplate->template->name][$sField])) {
                return $this->Ofiles[$oTemplate->template->name][$sField];
            } else {
                $this->Ofiles[$oTemplate->template->name][$sField] = array();
            }
        } else {
            $this->Ofiles[$oTemplate->template->name] = array();
            $this->Ofiles[$oTemplate->template->name][$sField] = array();
        }

        $files = $oTemplate->$sField;
        $oFiles = [];
        if (!empty($files)) {
            $oFiles = json_decode((string) $files, true);
            if ($oFiles === null) {
                App()->setFlashMessage(
                    sprintf(
                        gT('Error: Malformed JSON - field %s must be either a JSON array or the string "inherit". Found "null".'),
                        $sField
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
     * @param string $sPackageName name of the package to edit
     * @param string $sType        the type of settings to change (css or js)
     * @param $aSettings           array of local setting
     * @return void
     */
    protected function removeFileFromPackage($sPackageName, $sType, $aSettings)
    {
        foreach ($aSettings as $sFile) {
            App()->clientScript->removeFileFromPackage($sPackageName, $sType, $sFile);
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
            $this->oMotherTemplate = $instance->prepareTemplateRendering($sMotherTemplateName, '');
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
        App()->setFlashMessage(
            sprintf(
                gT("Theme '%s' has been uninstalled because it's not compatible with this LimeSurvey version."),
                $sTemplateName
            ),
            'error'
        );
        App()->getController()->redirect(array("themeOptions/index", "#" => "surveythemes"));
        App()->end();
    }

    /**
     * Set the default configuration values for the template, and use the motherTemplate value if needed
     *
     * @return void
     */
    protected function setThisTemplate()
    {
        // Mandatory setting in config XML
        $this->apiVersion = (!empty($this->template->api_version)) ? $this->template->api_version : null;
        $this->viewPath = $this->path . $this->getTemplateConfigurationForAttribute($this, 'view_folder')->template->view_folder . DIRECTORY_SEPARATOR;
        $this->filesPath = $this->path . $this->getTemplateConfigurationForAttribute($this, 'files_folder')->template->files_folder . DIRECTORY_SEPARATOR;
        $this->generalFilesPath = App()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . 'generalfiles' . DIRECTORY_SEPARATOR;
        // Options are optional
        $this->setOptions();

        // Not mandatory (use package dependances)
        $this->setCssFramework();
        $this->packages = $this->getDependsPackages($this);
        if (!empty($this->packages_to_load)) {
            $templateToLoadPackages = json_decode($this->packages_to_load);
            if (!empty($templateToLoadPackages->add)) {
                $this->packages = array_merge($templateToLoadPackages->add, $this->packages);
            }
            if (!empty($templateToLoadPackages->remove)) {
                $this->packages = array_diff($this->packages, $templateToLoadPackages->remove);
            }
        }

        // Add depend package according to packages
        $this->depends = array_merge($this->depends, $this->packages);
    }

    /**
     * @todo document me
     * @return void
     */
    private function setCssFramework()
    {
        if (!empty($this->cssframework_name)) {
            $this->cssFramework = new \stdClass();
            $this->cssFramework->name = $this->cssframework_name;
            $this->cssFramework->css  = json_decode($this->cssframework_css);
            $this->cssFramework->js   = json_decode($this->cssframework_js);
        } else {
            $this->cssFramework = new \stdClass();
            $this->cssFramework->name = '';
            $this->cssFramework->css  = '';
            $this->cssFramework->js   = '';
        }
    }

    /**
     * Decodes json string from the database field "options" and stores it inside $this->oOptions
     * Also triggers inheritence checks
     * @return void
     */
    protected function setOptions()
    {
        $this->oOptions = new stdClass();
        if (!empty($this->options)) {
            $this->oOptions = json_decode($this->options);
        }
        // unset "comment" property which is auto generated from HTML comments in xml file
        unset($this->oOptions->comment);

        $this->setOptionInheritance();
    }

    /**
     * Loop through all theme options defined, trigger check for inheritance and write the new value back to the options object
     * @return void
     */
    protected function setOptionInheritance()
    {
        $oOptions = $this->oOptions;

        if (!empty($oOptions)) {
            foreach ($oOptions as $sKey => $sOption) {
                $this->oOptions->$sKey = $this->getOptionKey($sKey);
            }
        }
    }

    /**
     * Search through the inheritence chain and find the inherited value for theme option
     * @param string $key
     * @return mixed
     */
    protected function getOptionKey($key)
    {
        $aOptions = json_decode($this->options, true);
        if (isset($aOptions[$key])) {
            $value = $aOptions[$key];
            if ($value === 'inherit') {
                $oParentConfig = $this->getParentConfiguration();
                if ($oParentConfig->id != $this->id) {
                    return $this->getParentConfiguration()->getOptionKey($key);
                } else {
                    $this->uninstallIncorectTheme($this->template_name);
                }
            }
            return  $value;
        } else {
            return null;
        }
    }

    /**
     * loads the main theme template from the parent theme that it is extending, as a package. Ready to be registered
     *
     * @param string[] $packages
     * @return string[]
     */
    protected function addMotherTemplatePackage($packages)
    {
        if (!empty($this->template->extends)) {
            $sMotherTemplateName = (string) $this->template->extends;
            $packages[]          = 'survey-template-' . $sMotherTemplateName;
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

        $sFieldName  = 'cssframework_' . $sType;
        $aFieldValue = json_decode((string) $this->$sFieldName, true);

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
                $this->aFrameworkAssetsToReplace[$sType] = array_merge(
                    $this->aFrameworkAssetsToReplace,
                    (array) $aFieldValue['remove']
                );
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

    /**
     * @todo document me
     * @return TemplateConfiguration
     */
    public function getParentConfiguration()
    {
        if (empty($this->oParentTemplate)) {
            //check for surveygroup id if a survey is given
            if ($this->sid != null) {
                $oSurvey = Survey::model()->findByPk($this->sid);
                // set template name from real inherited value
                $sTemplateName = !empty($oSurvey->oOptions->template) ?
                    $oSurvey->oOptions->template :
                    $this->template->name;
                $oParentTemplate = Template::getTemplateConfiguration($sTemplateName, null, $oSurvey->gsid);
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
                    $oParentTemplate = Template::getTemplateConfiguration(
                        $this->template->name,
                        null,
                        $oSurveyGroup->parent_id
                    );
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
        self::model()->updateAll(
            array('template_name' => $sNewName),
            "template_name = :oldname",
            array(':oldname' => $sOldName)
        );
    }

    /**
     * Proxy for the AR method to manage the inheritance
     * If one of the field that can be inherited is set to "inherit", then it will return the value of its parent
     * NOTE: this is recursive, if the parent field itself is set to inherit, then it will
     * the value of the parent of the parent, etc
     *
     * @param string $name the name of the attribute
     * @return mixed
     */
    public function __get($name)
    {
        $aAttributesThatCanBeInherited = array(
            'files_css',
            'files_js',
            'options',
            'cssframework_name',
            'cssframework_css',
            'cssframework_js',
            'packages_to_load'
        );

        if (in_array($name, $aAttributesThatCanBeInherited) && $this->bUseMagicInherit) {
            // Full inheritance of the whole field
            $sAttribute = parent::__get($name);
            if ($sAttribute === 'inherit') {
                // NOTE: this is object recursive (if parent configuration field is set to inherit,
                // then it will lead to this method again.)
                $oParentConfiguration = $this->getParentConfiguration();
                /**
                 * We check if $oParentConfiguration is the same as $this because if it is, $oParentConfiguration->$name will
                 * try to directly access the property instead of calling the magic method, and it will fail for dynamic properties.
                 * @todo: Review the behavior of getParentConfiguration(). Returning the same object seems to be a bug.
                 */
                if ($oParentConfiguration !== $this) {
                    $sAttribute = $oParentConfiguration->$name;
                } else {
                    $sAttribute = $oParentConfiguration->getAttribute($name);
                }
            }
        } else {
            $sAttribute = parent::__get($name);
        }

        return $sAttribute;
    }

    /**
     * @todo document me
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
            [':template_name' => $this->template_name]
        );
    }

    /**
     * Get showpopups value from config or template configuration
     */
    public function getshowpopups()
    {
        $config = (int) App()->getConfig('showpopups');
        if ($config == 2) {
            if (isset($this->oOptions->showpopups)) {
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
    public function setOptionKeysToInherit()
    {
        $oTemplate = $this->getParentConfiguration();
        $oTemplate->bUseMagicInherit = true;
        $oTemplate->setOptions();

        $aOptions = array();
        if ((string) $this->options === 'inherit') {
            foreach ($oTemplate->oOptions as $key => $value) {
                $aOptions[$key] = 'inherit';
            }
            $this->options = json_encode($aOptions);
        }
    }

    /**
     * Sanitizes the theme options making sure that paths are valid.
     * Options that match a file will be marked as invalid if the file
     * is not valid, or replaced with the virtual path if the file is valid.
     */
    public function sanitizeImagePathsOnJson($attribute, $params)
    {
        $excludedOptions = [
            'cssframework'
        ];
        // Validates all options of the theme. Not only classic ones which are expected to hold a path,
        // as other options may hold a path as well (eg. custom theme options)
        $decodedOptions = json_decode((string) $this->$attribute, true);
        if (is_array($decodedOptions)) {
            Yii::import('application.helpers.SurveyThemeHelper');
            foreach ($decodedOptions as $option => &$value) {
                if (in_array($option, $excludedOptions)) {
                    continue;
                }
                $value = SurveyThemeHelper::sanitizePathInOption($value, $this->template_name, $this->sid);
            }
            $this->$attribute = json_encode($decodedOptions);
        }
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

    /**
     * Returns the html to render the previewimage of the template.
     * Pass $justTheUrl = true to return just the URL of the preview image
     * @param bool $justTheUrl
     * @return string
     */
    public function getPreview($justTheUrl = false)
    {
        $previewUrl = '';
        if (empty($this->sPreviewImgTag)) {
            if (is_a($this->template, 'Template')) {
                $sTemplateFileFolder = Template::getTemplatesFileFolder($this->template->name);
                $previewPath         = Template::getTemplatePath($this->template->name) . '/' . $sTemplateFileFolder;

                if ($previewPath && file_exists($previewPath . '/preview.png')) {
                    $previewUrl = Template::getTemplateURL($this->template->name) . $sTemplateFileFolder . '/preview.png';
                    $this->sPreviewImgTag = '<img src="' .
                        $previewUrl .
                        '" alt="template preview" height="200" class="img-thumbnail p-0 rounded-0" />';
                }
            } else {
                $this->sPreviewImgTag = '<em>' . gT('No preview available') . '</em>';
            }
        }

        return $justTheUrl ? $previewUrl : $this->sPreviewImgTag;
    }

    /**
     * Prepare all the needed datas to render the temple
     * If any problem (like template doesn't exist), it will load the default theme configuration
     * NOTE 1: This function will create/update all the packages needed to render the template, which imply to do the
     *         same for all mother templates
     * NOTE 2: So if you just want to access the TemplateConfiguration AR Object, you don't need to call it. Call it
     *         only before rendering anything related to the template.
     *
     * @param  string $sTemplateName the name of the template to load.
     *                               The string comes from the template selector in survey settings
     * @param  string $iSurveyId the id of the survey. If
     * @param bool $bUseMagicInherit
     * @return self
     */
    public function prepareTemplateRendering($sTemplateName = '', $iSurveyId = '', $bUseMagicInherit = true)
    {
        if (!empty($sTemplateName) && !empty($iSurveyId)) {
            if (!empty(self::$aPreparedToRender[$sTemplateName][$iSurveyId][$bUseMagicInherit])) {
                return self::$aPreparedToRender[$sTemplateName][$iSurveyId][$bUseMagicInherit];
            }
            self::$aPreparedToRender[$sTemplateName][$iSurveyId][$bUseMagicInherit] = [];
        }

        $this->setBasics($sTemplateName, $iSurveyId, $bUseMagicInherit);
        $this->setMotherTemplates(); // Recursive mother templates configuration
        $this->setThisTemplate(); // Set the main config values of this template
        $this->createTemplatePackage($this); // Create an asset package ready to be loaded
        $this->removeFiles();
        $this->getshowpopups();

        if (!empty($sTemplateName) && !empty($iSurveyId)) {
            self::$aPreparedToRender[$sTemplateName][$iSurveyId][$bUseMagicInherit] = $this;
        }
        return $this;
    }

    /**
     * Create a package for the asset manager.
     * The asset manager will push to tmp/assets/xyxyxy/ the whole template directory (with css, js, files, etc.)
     * And it will publish the CSS and the JS defined in config.xml. So CSS can use relative path for pictures.
     * The publication of the package itself is in LSETwigViewRenderer::renderTemplateFromString()
     *
     * @param TemplateConfiguration|TemplateManifest $oTemplate TemplateManifest
     */
    protected function createTemplatePackage($oTemplate)
    {
        // Each template in the inheritance tree needs a specific alias
        $sPathName  = 'survey.template-' . $oTemplate->sTemplateName . '.path';
        $sViewName  = 'survey.template-' . $oTemplate->sTemplateName . '.viewpath';

        Yii::setPathOfAlias($sPathName, $oTemplate->path);
        Yii::setPathOfAlias($sViewName, $oTemplate->viewPath);

        // First we add the framework replacement (bootstrap.css must be loaded before template.css)
        $aCssFiles  = $this->getFrameworkAssetsReplacement('css');
        $aJsFiles   = $this->getFrameworkAssetsReplacement('js');

        // This variable will be used to add the variation name to the body class
        // via $aClassAndAttributes['class']['body']
        $this->aCssFrameworkReplacement = $aCssFiles;

        // Then we add the template config files
        $aTCssFiles = $this->getFilesToLoad($oTemplate, 'css');
        $aTJsFiles  = $this->getFilesToLoad($oTemplate, 'js');

        $aCssFiles  = array_merge($aCssFiles, $aTCssFiles);
        $aJsFiles   = array_merge($aJsFiles, $aTJsFiles);

        // Remove/Replace mother template files
        if (
            App()->getConfig('force_xmlsettings_for_survey_rendering') ||
            ($this->template instanceof Template &&  $this->template->extends) ||
            !empty($this->config->metadata->extends)
        ) {
              $aCssFiles = $this->changeMotherConfiguration('css', $aCssFiles);
              $aJsFiles  = $this->changeMotherConfiguration('js', $aJsFiles);
        }

        //For fruity_twentythree surveytheme we completely replace the variation theme css file:
        $aCssFiles = $this->replaceVariationFilesWithRtl($aCssFiles);

        $this->sPackageName = 'survey-template-' . $this->sTemplateName;
        $sTemplateurl       = $oTemplate->getTemplateURL();

        $aDepends = empty($oTemplate->depends) ? array() : $oTemplate->depends;

        // The package "survey-template-{sTemplateName}" will be available from anywhere in the app now.
        // To publish it : Yii::app()->clientScript->registerPackage( 'survey-template-{sTemplateName}' );
        // Depending on settings, it will create the asset directory, and publish the css and js files
        App()->clientScript->addPackage($this->sPackageName, array(
            'devBaseUrl'  => $sTemplateurl, // Used when asset manager is off
            'basePath'    => $sPathName, // Used when asset manager is on
            'css'         => $aCssFiles,
            'js'          => $aJsFiles,
            'depends'     => $aDepends,
        ));
    }

    /**
     * When rtl language is chosen:
     * if a css file in folder variations is in array cssFiles, then it will be replaced with the
     * *-rtl version
     * @param array $cssFiles
     * @return array
     */
    private function replaceVariationFilesWithRtl(array $cssFiles)
    {
        if (getLanguageRTL(App()->getLanguage()) == 'rtl') {
            foreach ($cssFiles as $index => $cssFile) {
                if (strpos($cssFile, 'css/variations/theme_') !== false) {
                    $cssFileSplitArray = explode('.', $cssFile);
                    $cssFiles[$index] =  $cssFileSplitArray[0] . '-rtl.css';
                }
            }
        }
        return $cssFiles;
    }
}
