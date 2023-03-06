<?php

/**
 * DateSetting Plugin for LimeSurvey
 * @author : Denis Chenu
 * @copyright: LimeSurvey <https://community.limesurvey.org/>
 * @version:; 0.1.0
 * @licence MIT Licence
 */
class DateSetting extends PluginBase
{
    /** @inheritdoc **/
    protected static $name = 'DateSetting';
    /** @inheritdoc **/
    protected static $description = 'Pluginto test HTML DB setting.';
    /** @inheritdoc **/
    protected $storage = 'DbStorage';
    /** @inheritdoc **/
    protected $settings = [
        'checkDateDate' => array(
            'type' => 'date',
            'label' => 'A date only saved',
            'saveformat' => "Y-m-d",
        ),
        'checkDateYear' => array(
            'type' => 'date',
            'label' => 'A year only saved',
            'saveformat' => "Y",
        ),
        'checkDateDefault' => array(
            'type' => 'date',
            'label' => 'Another date no format set',
        ),
        'checkDateFalse' => array(
            'type' => 'date',
            'label' => 'A date saved as shown',
            'saveformat' => false,
        ),
    ];

    /** @inheritdoc **/
    public function init()
    {
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
    }

    /**
     * Register the settings
     */
    public function beforeSurveySettings()
    {
        $oEvent = $this->event;
        $surveyId = $oEvent->get('survey');
        $oEvent->set(
            "surveysettings.{$this->id}",
            array(
                'name' => get_class($this),
                'settings' => array(
                    'checkSurveyDateDate' => array(
                        'type' => 'date',
                        'label' => 'A date only saved',
                        'saveformat' => "Y-m-d",
                        'current' => $this->get('checkSurveyDateDate', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateYear' => array(
                        'type' => 'date',
                        'label' => 'A year only saved',
                        'saveformat' => "Y",
                        'current' => $this->get('checkSurveyDateYear', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateDefault' => array(
                        'type' => 'date',
                        'label' => 'Another date no format set',
                        'current' => $this->get('checkSurveyDateDefault', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateFalse' => array(
                        'type' => 'date',
                        'label' => 'A date saved as shown',
                        'saveformat' => false,
                        'current' => $this->get('checkSurveyDateFalse', 'Survey', $surveyId, ''),
                    ),
                )
            )
        );
    }

    /** @inheritdoc **/
    public function newSurveySettings()
    {
        $event = $this->event;
        /* Since the settings are not the same for SurveySettings : it's necessary to set type and saveformat */
        $this->settings = [
            'checkSurveyDateDate' => array(
                'type' => 'date',
                'saveformat' => "Y-m-d",
            ),
            'checkSurveyDateYear' => array(
                'type' => 'date',
                'saveformat' => "Y",
            ),
            'checkSurveyDateDefault' => array(
                'type' => 'date',
            ),
            'checkSurveyDateFalse' => array(
                'type' => 'date',
                'saveformat' => false,
            ),
        ];
        foreach ($event->get('settings') as $name => $value) {
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }
}
