<?php

/**
 * DemoDateSetting Plugin for LimeSurvey
 * @author Denis Chenu
 * @copyright LimeSurvey <https://community.limesurvey.org/>
 * @version 0.2.0
 * @license MIT License
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
            'saveformat' => "Y-m-d",
        ),
        'checkDateYear' => array(
            'type' => 'date',
            'saveformat' => "Y",
        ),
        'checkDateFalse' => array(
            'type' => 'date',
            'saveformat' => false,
        ),
        'checkDateDefault' => array(
            'type' => 'date',
        ),
    ];

    /** @inheritdoc **/
    public function init()
    {
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
    }

    /** Event to register the survey settings */
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
                        'label' => $this->gT('Save date only'),
                        'help' => sprintf(
                            $this->gT("Save format is set to 'Y-m-d', you'll get only the date when getting the settings (currently '%s')."),
                            "<code>" . $this->get('checkSurveyDateDate', 'Survey', $surveyId, '') . "</code>"
                        ),
                        'saveformat' => "Y-m-d",
                        'current' => $this->get('checkSurveyDateDate', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateYear' => array(
                        'type' => 'date',
                        'label' => $this->gT('Save year only'),
                        'help' => sprintf(
                            $this->gT("Save format is set to 'Y', you'll get only the year when getting the settings (currently '%s')."),
                            "<code>" . $this->get('checkSurveyDateYear', 'Survey', $surveyId, '') . "</code>"
                        ),
                        'saveformat' => "Y",
                        'current' => $this->get('checkSurveyDateYear', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateFalse' => array(
                        'type' => 'date',
                        'label' => $this->gT('Save as shown'),
                        'help' => sprintf(
                            $this->gT("Save format is set to false, the setting is saved as shown to the user (currently '%s')."),
                            "<code>" . $this->get('checkSurveyDateFalse', 'Survey', $surveyId, '') . "</code>"
                        ),
                        'saveformat' => false,
                        'current' => $this->get('checkSurveyDateFalse', 'Survey', $surveyId, ''),
                    ),
                    'checkSurveyDateDefault' => array(
                        'type' => 'date',
                        'label' => $this->gT('Save default format'),
                        'help' =>  sprintf(
                            $this->gT("Save format is not set, the setting is saved as shown to the user (currently '%s')."),
                            "<code>" . $this->get('checkSurveyDateDefault', 'Survey', $surveyId, '') . "</code>"
                        ),
                        'current' => $this->get('checkSurveyDateDefault', 'Survey', $surveyId, ''),
                    ),
                )
            )
        );
    }

    /** Event to save the survey settings  **/
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

    /**
     * @inheritdoc
     * Update language strings and help
     **/
    public function getPluginSettings($getValues = true)
    {
        $pluginSettings = parent::getPluginSettings($getValues);
        if (!$getValues) {
            return $pluginSettings;
        }
        $pluginSettings['checkDateDate']['label'] = $this->gT("Save date only");
        $pluginSettings['checkDateDate']['help'] = sprintf(
            $this->gT("Save format is set to 'Y-m-d', you'll get only the date when getting the settings (currently '%s')."),
            '<code>' . strval($pluginSettings['checkDateDate']['current']) . "</code>"
        );
        $pluginSettings['checkDateYear']['label'] = $this->gT("Save year only");
        $pluginSettings['checkDateYear']['help'] = sprintf(
            $this->gT("Save format is set to 'Y', you'll get only the year when getting the settings (currently '%s')."),
            '<code>' . strval($pluginSettings['checkDateYear']['current']) . "</code>"
        );
        $pluginSettings['checkDateDefault']['label'] = $this->gT("Save as shown");
        $pluginSettings['checkDateDefault']['help'] = sprintf(
            $this->gT("Save format is set to false, the setting is saved as shown to the user (currently '%s')."),
            '<code>' . strval($pluginSettings['checkDateDefault']['current']) . "</code>"
        );
        $pluginSettings['checkDateFalse']['label'] = $this->gT("Save default format");
        $pluginSettings['checkDateFalse']['help'] = sprintf(
            $this->gT("Save format is not set, the setting is saved as shown to the user (currently '%s')."),
            '<code>' . strval($pluginSettings['checkDateFalse']['current']) . "</code>"
        );
        return $pluginSettings;
    }
}
