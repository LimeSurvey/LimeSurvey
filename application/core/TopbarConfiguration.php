<?php

class TopbarConfiguration
{
    /** @var string Name of the topbar view */
    private $viewName = '';

    /** @var string Topbar ID */
    private $id = '';

    /** @var array Data to be passed to the view */
    private $data = array();

    /** @var array Data for general survey topbar */
    private $surveyData = array();

    /** @var string Name of the view used to render the left side of the topbar */
    private $leftSideView = '';

    /** @var string Name of the view used to render the right side of the topbar */
    private $rightSideView = '';

    /** @var array List of properties that can have public getter */
    private static $publicReadableProperties = ['viewName', 'id', 'data', 'leftSideView', 'rightSideView', 'surveyData'];

    public function __construct(array $config = [])
    {
        // Set defaults
        $this->viewName = isset($config['name']) ? $config['name'] : 'surveyTopbar_view';
        $this->id = isset($config['topbarId']) ? $config['topbarId'] : 'surveybarid';

        if (isset($config['leftSideView'])) $this->$leftSideView = $config['leftSideView'];
        if (isset($config['rightSideView'])) $this->$rightSideView = $config['rightSideView'];

        $this->data = $config;

        // If the topbar is the general survey topbar, and the config contains a SID, add the Survey Topbar data to the data array
        if ($this->viewName == 'surveyTopbar_view' && !empty($this->data['sid'])) {
            $this->surveyData = $this->getSurveyTopbarData($this->data['sid']);
            $this->data = array_merge(
                $this->surveyData,
                $this->data
            );
        }
    }

    /**
     * Creates and instance of TopbarConfiguration based on the data array used for the views
     * 
     * @param array $aData
     * @return TopbarConfiguration
     */
    public static function fromViewData($aData)
    {
        $config = isset($aData['topBar']) ? $aData['topBar'] : [];

        // If 'sid' is not specified in the topbar config, but is present in the $aData array, assign it to the config
        if (empty($config['sid']) && isset($aData['sid'])) $config['sid'] = $aData['sid'];
     
        return new self($config);
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
    protected function getSurveyTopbarData($sid) {
        $oSurvey = Survey::model()->findByPk($sid);
        $hasSurveyContentPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update');
        $hasSurveyActivationPermission = Permission::model()->hasSurveyPermission($sid, 'surveyactivation', 'update');
        $hasDeletePermission = Permission::model()->hasSurveyPermission($sid, 'survey', 'delete');
        $hasSurveyTranslatePermission = Permission::model()->hasSurveyPermission($sid, 'translations', 'read');
        $hasSurveyReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
        $hasSurveyTokensPermission = Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')
            || Permission::model()->hasSurveyPermission($sid, 'tokens', 'create');
        $hasResponsesCreatePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'create');
        $hasResponsesReadPermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'read');
        $hasResponsesStatisticsReadPermission = Permission::model()->hasSurveyPermission($sid, 'statistics', 'read');

        $isActive = $oSurvey->active == 'Y';
        $condition = array('sid' => $sid, 'parent_qid' => 0);
        $sumcount = Question::model()->countByAttributes($condition);
        $hasAdditionalLanguages = (count($oSurvey->additionalLanguages) > 0);
        $canactivate = $sumcount > 0 && $hasSurveyActivationPermission;
        $expired = $oSurvey->expires != '' && ($oSurvey->expires < dateShift(date("Y-m-d H:i:s"),
                    "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));
        $notstarted = ($oSurvey->startdate != '') && ($oSurvey->startdate > dateShift(date("Y-m-d H:i:s"),
                    "Y-m-d H:i", Yii::app()->getConfig('timeadjust')));

        if (!$isActive) {
            $context = gT("Preview survey");
            $contextbutton = 'preview_survey';
        } else {
            $context = gT("Execute survey");
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

        return array(
            'sid' => $sid,
            'oSurvey' => $oSurvey,
            'canactivate' => $canactivate,
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
            'hasResponsesCreatePermission' => $hasResponsesCreatePermission,
            'hasResponsesReadPermission' => $hasResponsesReadPermission,
            'hasSurveyActivationPermission' => $hasSurveyActivationPermission,
            'hasResponsesStatisticsReadPermission' => $hasResponsesStatisticsReadPermission,
            'extraToolsMenuItems' => $extraToolsMenuItems ?? [],
            'beforeSurveyBarRender' => $beforeSurveyBarRender ?? [],
            'showToolsMenu' => $showToolsMenu,
        );
    }

    /**
     * Magic function returns the value of the requested property, if and only if it is a
     * 'publically readble' property.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        if (in_array($property, $this::$publicReadableProperties) && property_exists(get_class($this), $property)) {
            return $this->$property;
        }
        throw new Exception("TopbarConfiguration has no property '$property'");
    }

}