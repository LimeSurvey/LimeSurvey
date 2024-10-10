<?php

class TopbarConfiguration
{
    /** @var string Name of the topbar view */
    private $viewName = '';

    /** @var string Topbar ID */
    private $id = '';

    /** @var array Data to be passed to the view */
    private $data = array();

    /** @var string Name of the view used to render the left side of the topbar */
    private $leftSideView = '';

    /** @var string Name of the view used to render the right side of the topbar */
    private $rightSideView = '';

    /** @var boolean Flag to hide the whole topbar */
    private $hide = false;

    /** @var array Maps views to the methods used to get their extra data */
    private $extraDataMapping = [
        'surveyTopbar_view' => 'TopbarConfiguration::getSurveyTopbarData',
        'responsesTopbarLeft_view' => 'TopbarConfiguration::getResponsesTopbarData',
        'responseViewTopbarRight_view' => 'TopbarConfiguration::getResponsesTopbarData',
        'surveyTopbarRight_view' => 'TopbarConfiguration::getRightSurveyTopbarData',
        'tokensTopbarLeft_view' => 'TopbarConfiguration::getTokensTopbarData',
        'tokensTopbarRight_view' => 'TopbarConfiguration::getTokensTopbarData',
        'questionTopbar_view' => 'TopbarConfiguration::getQuestionTopbarData',
        'questionTopbarLeft_view' => 'TopbarConfiguration::getQuestionTopbarData',
        'questionTopbarRight_view' => 'TopbarConfiguration::getQuestionTopbarData',
        'editQuestionTopbarLeft_view' => 'TopbarConfiguration::getQuestionTopbarData',
        'listquestionsTopbarLeft_view' => 'TopbarConfiguration::getQuestionTopbarData',
        'editGroupTopbarLeft_view' => 'TopbarConfiguration::getGroupTopbarData',
        'groupTopbarLeft_view' => 'TopbarConfiguration::getGroupTopbarData',
        'groupTopbarRight_view' => 'TopbarConfiguration::getGroupTopbarData',
        'listquestiongroupsTopbarLeft_view' => 'TopbarConfiguration::getGroupTopbarData',
    ];

    /**
     * Creates and instance of TopbarConfiguration based on the received $config array,
     * which is expected to have the following keys (all keys are optional):
     *  'name' => The name of the main view to use.
     *  'topbarId' => The topbar ID. Will normally be used as ID for container html element of the topbar.
     *  'leftSideView' => The name of the view to use for the left side of the topbar.
     *  'rightSideView' => The name of the view to use for the right side of the topbar.
     *  'hide' => Boolean indicating if the topbar should be hidden (used to hide the controller's default topbar on some actions)
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Set defaults
        $this->viewName = $config['name'] ?? 'surveyTopbar_view';
        $this->id = $config['topbarId'] ?? 'surveybarid';
        $this->hide = $config['hide'] ?? false;

        if (isset($config['leftSideView'])) {
            $this->leftSideView = $config['leftSideView'];
        }
        if (isset($config['rightSideView'])) {
            $this->rightSideView = $config['rightSideView'];
        } elseif (!empty($config['showSaveButton'])   ||
                  !empty($config['showCloseButton'])  ||
                  !empty($config['showImportButton']) ||
                  !empty($config['showExportButton']) ||
                  !empty($config['showBackButton'])   ||
                  !empty($config['showWhiteCloseButton'])) {
            // If no right side view has been specified, and one of the default buttons must be shown, use the default right side view.
            $this->rightSideView = "surveyTopbarRight_view";
        }

        $this->data = $config;
    }

    /**
     * Creates and instance of TopbarConfiguration based on the data array used for the views
     *
     * @param array $aData
     * @return TopbarConfiguration
     */
    public static function createFromViewData($aData)
    {
        $config = $aData['topBar'] ?? [];

        // If 'sid' is not specified in the topbar config, but is present in the $aData array, assign it to the config
        $sid = self::getSid($config);
        if (empty($sid)) {
            $sid = self::getSid($aData);
        }
        if (!empty($sid)) {
            $config['sid'] = $sid;
        }

        return new self($config);
    }

    /**
     * Gets the data for the specified view by calling the corresponding method and merging with general view data
     */
    public function getViewData($view)
    {
        if (empty($view) || empty($this->extraDataMapping[$view])) {
            return [];
        }
        $extraData = call_user_func($this->extraDataMapping[$view], !empty($this->data['sid']) ? $this->data['sid'] : null);
        if (!empty($extraData)) {
            return array_merge($extraData, $this->data);
        } else {
            return $this->data;
        }
    }

    /**
     * Get the data for the left side view
     */
    public function getLeftSideData()
    {
        return $this->getViewData($this->leftSideView);
    }

    /**
     * Get the data for the right side view
     */
    public function getRightSideData()
    {
        return $this->getViewData($this->rightSideView);
    }

    /**
     * This Method is returning the Data for Survey Top Bar
     *
     * @param int $sid Given Survey ID
     *
     * @return array
     * @throws CException
     *
     */
    public static function getSurveyTopbarData($sid)
    {
        if (empty($sid)) {
            return [];
        }

        $oSurvey = Survey::model()->findByPk($sid);
        $hasSurveyContentPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update');
        $hasSurveyActivationPermission = Permission::model()->hasSurveyPermission($sid, 'surveyactivation', 'update');
        $hasDeletePermission = Permission::model()->hasSurveyPermission($sid, 'survey', 'delete');
        $hasSurveyTranslatePermission = Permission::model()->hasSurveyPermission($sid, 'translations', 'read');
        $hasSurveyReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
        $hasSurveyExportPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export');
        $hasSurveyTokensPermission = Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')
            || Permission::model()->hasSurveyPermission($sid, 'tokens', 'create');
        $hasSurveyTokensReadPermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'read');
        $hasSurveyTokensExportPermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'export');
        $hasResponsesCreatePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'create');
        $hasResponsesReadPermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'read');
        $hasResponsesExportPermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'export');
        $hasResponsesStatisticsReadPermission = Permission::model()->hasSurveyPermission($sid, 'statistics', 'read');

        $isActive = $oSurvey->active == 'Y';
        $condition = array('sid' => $sid, 'parent_qid' => 0);
        $sumcount = Question::model()->countByAttributes($condition);
        $hasAdditionalLanguages = (count($oSurvey->additionalLanguages) > 0);
        $canactivate = $sumcount > 0 && $hasSurveyActivationPermission;
        $expired = $oSurvey->expires != '' && ($oSurvey->expires < dateShift(
            date("Y-m-d H:i:s"),
            "Y-m-d H:i",
            Yii::app()->getConfig('timeadjust')
        ));
        $notstarted = ($oSurvey->startdate != '') && ($oSurvey->startdate > dateShift(
            date("Y-m-d H:i:s"),
            "Y-m-d H:i",
            Yii::app()->getConfig('timeadjust')
        ));

        if (!$isActive) {
            $context = gT("Preview survey");
            $contextbutton = 'preview_survey';
        } else {
            $context = gT("Run survey");
            $contextbutton = 'execute_survey';
        }

        $language = $oSurvey->language;
        $conditionsCount = Condition::model()->with(array('questions' => array('condition' => 'sid =' . $sid)))->count();

        // Put menu items in tools menu
        $event = new PluginEvent('beforeToolsMenuRender', App()->getController());
        $event->set('surveyId', $oSurvey->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $extraToolsMenuItems = $event->get('menuItems');

        // Add new menus in survey bar
        $event = new PluginEvent('beforeSurveyBarRender', App()->getController());
        $event->set('surveyId', $oSurvey->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $beforeSurveyBarRender = $event->get('menus');

        $showToolsMenu = $hasDeletePermission
            || $hasSurveyTranslatePermission
            || $hasSurveyContentPermission
            || !is_null($extraToolsMenuItems);

        $editorEnabled = $event->get('isEditorEnabled');
        if ($editorEnabled===null) {
            $editorEnabled = Yii::app()->getConfig('editorEnabled') ?? false;
        }

        $enableEditorButton = true;
        if ($oSurvey->getTemplateEffectiveName() !== 'fruity_twentythree') {
            $enableEditorButton = false;
        }

        $editorUrl = Yii::app()->request->getUrlReferrer(
            Yii::app()->createUrl(
                'editorLink/index',
                ['route' => 'survey/' . $sid]
            )
        );
        App()->getClientScript()->registerScriptFile(
            App()->getConfig('adminscripts') . 'newQuestionEditor.js',
            CClientScript::POS_END
        );

        return array(
            'sid' => $sid,
            'oSurvey' => $oSurvey,
            'canactivate' => $canactivate,
            'candeactivate' => $hasSurveyActivationPermission,
            'expired' => $expired,
            'notstarted' => $notstarted,
            'context' => $context,
            'contextbutton' => $contextbutton,
            'language' => $language,
            'sumcount' => $sumcount,
            'hasSurveyContentPermission' => $hasSurveyContentPermission,
            'hasDeletePermission' => $hasDeletePermission,
            'hasSurveyTranslatePermission' => $hasSurveyTranslatePermission,
            'hasAdditionalLanguages' => $hasAdditionalLanguages,
            'conditionsCount' => $conditionsCount,
            'hasSurveyReadPermission' => $hasSurveyReadPermission,
            'hasSurveyTokensPermission' => $hasSurveyTokensPermission,
            'hasSurveyTokensReadPermission' => $hasSurveyTokensReadPermission,
            'hasResponsesCreatePermission' => $hasResponsesCreatePermission,
            'hasResponsesReadPermission' => $hasResponsesReadPermission,
            'hasSurveyActivationPermission' => $hasSurveyActivationPermission,
            'hasResponsesStatisticsReadPermission' => $hasResponsesStatisticsReadPermission,
            'hasSurveyExportPermission' => $hasSurveyExportPermission,
            'hasSurveyTokensExportPermission' => $hasSurveyTokensExportPermission,
            'hasResponsesExportPermission' => $hasResponsesExportPermission,
            'extraToolsMenuItems' => $extraToolsMenuItems ?? [],
            'beforeSurveyBarRender' => $beforeSurveyBarRender ?? [],
            'showToolsMenu' => $showToolsMenu,
            'surveyLanguages' => self::getSurveyLanguagesArray($oSurvey),
            'editorEnabled' => $editorEnabled,
            'editorUrl' => $editorUrl,
            'enableEditorButton' => $enableEditorButton,
        );
    }

    /**
     * Returns Data for Responses Top Bar
     *
     * @param $sid
     * @return array
     * @throws CException
     */
    public static function getResponsesTopbarData($sid)
    {
        if (empty($sid)) {
            return [];
        }

        $survey = Survey::model()->findByPk($sid);

        $hasResponsesReadPermission   = Permission::model()->hasSurveyPermission($sid, 'responses', 'read');
        $hasResponsesCreatePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'create');
        $hasStatisticsReadPermission  = Permission::model()->hasSurveyPermission($sid, 'statistics', 'read');
        $hasResponsesExportPermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'export');
        $hasResponsesDeletePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'delete');
        $hasResponsesUpdatePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'update');
        $isActive                     = $survey->active;
        $isTimingEnabled              = $survey->savetimings;

        return array(
            'oSurvey' => $survey,
            'hasResponsesReadPermission'   => $hasResponsesReadPermission,
            'hasResponsesCreatePermission' => $hasResponsesCreatePermission,
            'hasStatisticsReadPermission'  => $hasStatisticsReadPermission,
            'hasResponsesExportPermission' => $hasResponsesExportPermission,
            'hasResponsesDeletePermission' => $hasResponsesDeletePermission,
            'hasResponsesUpdatePermission' => $hasResponsesUpdatePermission,
            'isActive' => $isActive,
            'isTimingEnabled' => $isTimingEnabled,
        );
    }

    /**
     * Returns Data for Right Side of Survey Top Bar
     *
     * @param $sid
     * @return array
     * @throws CException
     */
    public static function getRightSurveyTopbarData($sid)
    {
        if (empty($sid)) {
            return [];
        }

        $closeUrl = Yii::app()->request->getUrlReferrer(
            Yii::app()->createUrl(
                "responses/browse/",
                ['surveyId' => $sid]
            )
        );

        return array(
            'closeUrl' => $closeUrl
        );
    }

    /**
     * Returns Data for Tokens Top Bar
     *
     * @param $sid
     * @return array
     * @throws CException
     */
    public static function getTokensTopbarData($sid)
    {
        if (empty($sid)) {
            return [];
        }

        $survey = Survey::model()->findByPk($sid);

        $hasTokensReadPermission   = Permission::model()->hasSurveyPermission($sid, 'tokens', 'read');
        $hasTokensCreatePermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'create');
        $hasTokensExportPermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'export');
        $hasTokensImportPermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'import');
        $hasTokensUpdatePermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'update');
        $hasTokensDeletePermission = Permission::model()->hasSurveyPermission($sid, 'tokens', 'delete');
        $hasSurveySettingsUpdatePermission = Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update');

        return array(
            'oSurvey' => $survey,
            'hasTokensReadPermission'   => $hasTokensReadPermission,
            'hasTokensCreatePermission' => $hasTokensCreatePermission,
            'hasTokensExportPermission' => $hasTokensExportPermission,
            'hasTokensImportPermission' => $hasTokensImportPermission,
            'hasTokensUpdatePermission' => $hasTokensUpdatePermission,
            'hasTokensDeletePermission' => $hasTokensDeletePermission,
            'hasSurveySettingsUpdatePermission' => $hasSurveySettingsUpdatePermission,
        );
    }

    /**
     * Returns Data for QuestionEditor Top Bar
     *
     * @param $sid
     * @return array
     * @throws CException
     */
    public static function getQuestionTopbarData($sid)
    {
        if (empty($sid)) {
            return [];
        }

        $survey = Survey::model()->findByPk($sid);

        $hasSurveyContentUpdatePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update');
        $hasSurveyContentReadPermission   = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
        $hasSurveyContentExportPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export');
        $hasSurveyContentCreatePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'create');
        $hasSurveyContentDeletePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'delete');

        return array(
            'oSurvey' => $survey,
            'hasSurveyContentUpdatePermission' => $hasSurveyContentUpdatePermission,
            'hasSurveyContentReadPermission' => $hasSurveyContentReadPermission,
            'hasSurveyContentExportPermission' => $hasSurveyContentExportPermission,
            'hasSurveyContentCreatePermission' => $hasSurveyContentCreatePermission,
            'hasSurveyContentDeletePermission' => $hasSurveyContentDeletePermission,
            'surveyLanguages' => self::getSurveyLanguagesArray($survey),
        );
    }

    /**
     * Returns Data for Groups Top Bar
     *
     * @param $sid
     * @return array
     * @throws CException
     */
    public static function getGroupTopbarData($sid)
    {
        return self::getQuestionTopbarData($sid);
    }

    /**
     * Tries to retrieve the survey ID from the config
     */
    protected static function getSid($config)
    {
        if (!empty($config['sid'])) {
            return $config['sid'];
        } elseif (!empty($config['surveyid'])) {
            return $config['surveyid'];
        } elseif (!empty($config['surveyId'])) {
            return $config['surveyId'];
        } elseif (!empty($config['oSurvey'])) {
            return $config['oSurvey']->sid;
        }
    }

    public function getViewName()
    {
        return $this->viewName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->getViewData($this->viewName);
    }

    public function getLeftSideView()
    {
        return $this->leftSideView;
    }

    public function getRightSideView()
    {
        return $this->rightSideView;
    }

    public function getSurveyData()
    {
        return $this->surveyData;
    }

    public function shouldHide()
    {
        return $this->hide;
    }

    /**
     * returns array of language codes by language name for all languages of the given survey
     * @param Survey $survey
     * @return array
     */
    private static function getSurveyLanguagesArray(Survey $survey)
    {
        $languages = [];
        foreach ($survey->allLanguages as $language) {
            $languages[$language] = getLanguageNameFromCode($language, false);
        }

        return $languages;
    }
}
