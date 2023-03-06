<?php

/**
 * DemoDateSetting Plugin for LimeSurvey
 * @author : Denis Chenu
 * @copyright: LimeSurvey <https://community.limesurvey.org/>
 * @version:; 0.1.0
 * @licence MIT Licence
 */
class DemoDateSetting extends PluginBase
{
    /** @inheritdoc **/
    protected static $name = 'DemoDateSetting';
    /** @inheritdoc **/
    protected static $description = 'Plugin to show saveformat function in date setting.';
    /** @inheritdoc **/
    protected $storage = 'DbStorage';
    /** @inheritdoc **/
    protected $settings = [
        'checkDateDate' => array(
            'type' => 'date',
            'label' => 'A date only saved',
            'help' => 'Save format is set as Y-m-d, you have only the date when get the settings (2023-03-06 for example)',
            'saveformat' => "Y-m-d",
        ),
        'checkDateYear' => array(
            'type' => 'date',
            'label' => 'A year only saved',
            'saveformat' => "Y",
        ),
        'checkDateDefault' => array(
            'type' => 'date',
            'help' => 'Save format is set as Y, you have only the year when get the settings (2023 for example)',
            'label' => 'Another date no format set',
        ),
        'checkDateFalse' => array(
            'type' => 'date',
            'label' => 'A date saved as shown',
            'help' => 'Save format is set to false, you save the settings like it shown to the user. This happen too when you don\'t set format.',
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
                        'label' => $this->gT('A date only saved'),
                        'help' => $this->gT('Save format is set as Y-m-d, you have only the date when get the settings (2023-03-06 for example)'),
                        'saveformat' => "Y-m-d",
                        'current' => $this->get('checkSurveyDateDate', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateYear' => array(
                        'type' => 'date',
                        'label' => 'A year only saved',
                        'help' => $this->gT('Save format is set as Y, you have only the year when get the settings (2023 for example)'),
                        'saveformat' => "Y",
                        'current' => $this->get('checkSurveyDateYear', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateDefault' => array(
                        'type' => 'date',
                        'label' => $this->gT('Another date no format set'),
                        'help' => $this->gT('Save format is not set, you have the settings like it shown to the user when get it.'),
                        'current' => $this->get('checkSurveyDateDefault', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateFalse' => array(
                        'type' => 'date',
                        'label' => $this->gT('A date saved as shown'),
                        'help' => $this->gT('Save format is set to false, you have the settings like it shown to the user when get it.'),
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
        /**
         * If you don't use same settings in Survey and global : you have to set it before saving
         * then when save current saveformat can be used (we don't set it for Default)
         **/
        $this->settings = [
            'checkSurveyDateDate' => array(
                'type' => 'date',
                'saveformat' => "Y-m-d",
            ),
            'checkSurveyDateYear' => array(
                'type' => 'date',
                'saveformat' => "Y",
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
