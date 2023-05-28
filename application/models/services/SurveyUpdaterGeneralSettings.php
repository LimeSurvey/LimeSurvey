<?php

namespace LimeSurvey\Models\Services;

use Survey;
use Permission;
use LSYii_Application;
use PluginEvent;
use Date_Time_Converter;
use LimeSurvey\PluginManager\PluginManager;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Service SurveyUpdaterGeneralSettings
 *
 * Service class for survey language setting updating.
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyUpdaterGeneralSettings
{
    private ?Permission $modelPermission = null;
    private ?Survey $modelSurvey = null;
    private ?LSYii_Application $yiiApp = null;
    private ?PluginManager $yiiPluginManager = null;

    const FIELD_TYPE_YN = 'yersno';
    const FIELD_TYPE_DATETIME = 'dateime';
    const FIELD_TYPE_GAKEY = 'gakey';

    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey,
        LSYii_Application $yiiApp,
        PluginManager $yiiPluginManager
    )
    {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
        $this->yiiApp = $yiiApp;
        $this->yiiPluginManager = $yiiPluginManager;
    }

    /**
     * Update
     *
     * @param int $surveyId
     * @param array $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return boolean
     */
    public function update($surveyId, $input)
    {
        $input = is_array($input) && !empty($input)
            ? $input
            : [];

        $hasPermission = $this->modelPermission
            ->hasSurveyPermission(
                $surveyId,
                'surveysettings',
                'update'
            );
        if ($hasPermission == false) {
            throw new ExceptionPermissionDenied(
                'Permission denied'
            );
        }

        $survey = $this->modelSurvey->findByPk(
            $surveyId
        );
        if (!$survey) {
            throw new ExceptionNotFound;
        }

        $fields = $this->getFields($survey);
        return $this->updateGeneralSettings(
            $survey,
            $input,
            $fields
        );
    }

    /**
     * Update General Settings
     *
     * @param Survey $survey
     * @param array $input
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return array
     */
    public function updateGeneralSettings(Survey $survey, array $input, array $fields)
    {
        $input = is_array($input) && !empty($input)
            ? $input
            : [];

        $this->dispatchPluginEventNewSurveySettings(
            $survey,
            isset($input['plugin']) ? $input['plugin'] : []
        );

        $input = $this->filterInput($input);

        $meta = ['updateFields' => []];

        foreach ($fields as $field => $fieldOpts) {
            $meta = $this->setField(
                $field,
                $input,
                $survey,
                $meta,
                $fieldOpts
            );
        }

        if (!empty($meta['updateFields'])) {
            $this->dispatchPluginEventBeforeSurveySettingsSave(
                $survey
            );
            if (!$survey->save()) {
                throw new ExceptionPersistError(
                    sprintf(
                        'Failed saving general settings for survey #%s',
                        $survey->sid
                        )
                );
            }
        }

        return $meta;
    }

    /**
     * Get Fields
     *
     * @param Survey $survey
     * @return array
     */
    private function getFields(Survey $survey)
    {
        $surveyNotActive = $survey->active != 'Y';

        return [
            'owner_id' => [],
            'admin' => [],
            'format' => [],
            'expires' =>  ['type' => static::FIELD_TYPE_DATETIME],
            'startdate' => ['type' => static::FIELD_TYPE_DATETIME],
            'template' => [],
            'assessments' => ['type' => static::FIELD_TYPE_YN],
            'anonymized' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'savetimings' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'datestamp' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'ipaddr' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'ipanonymize' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'refurl' => [
                'type' => static::FIELD_TYPE_YN,
                'canUpdate' => $surveyNotActive
            ],
            'publicgraphs' => ['type' => static::FIELD_TYPE_YN],
            'usecookie' => ['type' => static::FIELD_TYPE_YN],
            'allowregister' => ['type' => static::FIELD_TYPE_YN],
            'allowsave' => ['type' => static::FIELD_TYPE_YN],
            'navigationdelay' => [],
            'printanswers' => ['type' => static::FIELD_TYPE_YN],
            'publicstatistics' => ['type' => static::FIELD_TYPE_YN],
            'autoredirect' => ['type' => static::FIELD_TYPE_YN],
            'showxquestions' => ['type' => static::FIELD_TYPE_YN],
            'showgroupinfo' => [],
            'showqnumcode' => [],
            'shownoanswer' => ['type' => static::FIELD_TYPE_YN],
            'showwelcome' => ['type' => static::FIELD_TYPE_YN],
            'showsurveypolicynotice' => ['default' => 0],
            'allowprev' => ['type' => static::FIELD_TYPE_YN],
            'questionindex' => [],
            'nokeyboard' => ['type' => static::FIELD_TYPE_YN],
            'showprogress' => ['type' => static::FIELD_TYPE_YN],
            'listpublic' => ['type' => static::FIELD_TYPE_YN],
            'htmlemail' => ['type' => static::FIELD_TYPE_YN],
            'sendconfirmation' => ['type' => static::FIELD_TYPE_YN],
            'tokenanswerspersistence' => ['type' => static::FIELD_TYPE_YN],
            'alloweditaftercompletion' => ['type' => static::FIELD_TYPE_YN],
            'emailresponseto' => [],
            'emailnotificationto' => [],
            'googleanalyticsapikeysetting' => [],
            'googleanalyticsapikey' => [],
            'googleanalyticsstyle' => [],
            'tokenlength' => [],
            'adminemail' => [],
            'bounce_email' => [],
            'gsid' => ['default' => 1],
            'usecaptcha_surveyaccess' => ['type' => static::FIELD_TYPE_YN],
            'usecaptcha_registration' => ['type' => static::FIELD_TYPE_YN],
            'usecaptcha_saveandload' => ['type' => static::FIELD_TYPE_YN],
        ];
    }

    /**
     * Set Field
     *
     * @param string $field
     * @param array $input
     * @param Survey $survey
     * @param array $meta
     * @param ?array $fieldOpts
     * @return void
     */
    private function setField($field, $input, Survey $survey, $meta, $fieldOpts = null)
    {
        $meta = is_array($meta) ? $meta : [
            'updateFields' => []
        ];
        $meta['updateFields'] = is_array($meta['updateFields'])
            ? $meta['updateFields']
            : [];

        if (!isset($input[$field])) {
            return $meta;
        }

        $fieldOpts = !empty($fieldOpts)
            ? $fieldOpts
            : [];
        $type = !empty($fieldOpts['type'])
            ? $fieldOpts['type']
            : null;
        $default = !empty($fieldOpts['default'])
            ? $fieldOpts['default']
            : '';

        if (
            isset($fieldOpts['canUpdate'])
            && !$fieldOpts['canUpdate']
        ) {
            return $meta;
        }

        $value = $input[$field];
        switch ($type) {
            case static::FIELD_TYPE_DATETIME:
                $value = !empty($value)
                    ? $this->formatDateTimeInput($value)
                    : $default;
            break;
            case static::FIELD_TYPE_YN:
                if (!in_array($value, ['Y', 'N', 'I'])) {
                    $value = ((int) $value === 1) ? 'Y' : 'N';
                }
            break;
            case static::FIELD_TYPE_GAKEY:
                if ($survey->googleanalyticsapikeysetting == 'G') {
                    $value  = "9999useGlobal9999";
                } elseif ($survey->googleanalyticsapikeysetting == 'N') {
                    $value = '';
                }
            break;
            case 'int':
                $value = (int) $value;
            break;
            case null:
            default:
                $value = !empty($value)
                    ? $value
                    : $default;
            break;
        }

        $survey->{$field} = $value;

        if (!in_array(
        $field,
        $meta['updateFields']
        )) {
            $meta['updateFields'][] = $field;
        }

        return $meta;
    }

    /**
     * Filter Input
     *
     * @param array $surveyId
     * @return array
     */
    private function filterInput($input)
    {
        return array_map(function ($value) {
            return is_string($value)
                ? trim($value)
                : $value;
        }, $input);
    }

    /**
     * Dispatch plugin event new survey settings
     *
     * @param int $surveyId
     * @param array $pluginSettings
     * @return void
     */
    private function dispatchPluginEventNewSurveySettings($surveyId, $pluginSettings)
    {
        $pluginSettings = is_array($pluginSettings) && !empty($pluginSettings)
            ? $pluginSettings
            : [];
        foreach ($pluginSettings as $plugin => $settings) {
            $settingsEvent = new PluginEvent('newSurveySettings');
            $settingsEvent->set('settings', $settings);
            $settingsEvent->set('survey', $surveyId);
            $this->yiiPluginManager
                ->dispatchEvent($settingsEvent, $plugin);
        }
    }

    /**
     * Dispatch plugin event before survey settings save
     *
     * @param int $surveyId
     * @param array $pluginSettings
     * @return void
     */
    private function dispatchPluginEventBeforeSurveySettingsSave(Survey $survey)
    {
        $event = new PluginEvent('beforeSurveySettingsSave');
        $event->set('modifiedSurvey', $survey);
        $this->yiiPluginManager->dispatchEvent($event);
    }

    /**
     * Format date time input
     *
     * Converts date time string from user local format to internal database format.
     *
     * @param string $inputDateTimeString
     * @return string
     */
    private function formatDateTimeInput($inputDateTimeString)
    {
        $this->yiiApp->loadHelper('surveytranslator');
        $this->yiiApp->loadLibrary('Date_Time_Converter');
        $dateFormat =  isset($this->yiiApp->session)
            && !empty($this->yiiApp->session['dateformat'])
            ? $this->yiiApp->session['dateformat']
            : 1;
        $formatData = getDateFormatData($dateFormat);
        $dateTimeObj = new Date_Time_Converter(
            $inputDateTimeString,
            $formatData['phpdate'] . ' H:i'
        );
        return $dateTimeObj->convert('Y-m-d H:i:s');
    }
}
