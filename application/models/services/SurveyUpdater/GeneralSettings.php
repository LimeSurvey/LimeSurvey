<?php

namespace LimeSurvey\Models\Services\SurveyUpdater;

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
 * Survey Updater Service GeneralSettings
 *
 * Service class for survey language setting updating.
 *
 * Dependencies are injected to enable mocking.
 */
class GeneralSettings
{
    private ?Permission $modelPermission = null;
    private ?Survey $modelSurvey = null;
    private ?LSYii_Application $yiiApp = null;
    private ?PluginManager $yiiPluginManager = null;

    const FIELD_TYPE_YN = 'yersno';
    const FIELD_TYPE_DATETIME = 'dateime';
    const FIELD_TYPE_GAKEY = 'gakey';
    const FIELD_TYPE_USE_CAPTCHA = 'use_captcha';

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
    private function updateGeneralSettings(Survey $survey, array $input, array $fields)
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
     * Get Database Fields
     *
     * @param Survey $survey
     * @return array
     */
    private function getFields(Survey $survey)
    {
        $surveyNotActive = $survey->active != 'Y';

        return [
            'owner_id' => [
                'canUpdate' => isset($this->yiiApp->session) && (
                    $survey->owner_id == $this->yiiApp->session['loginID']
                    || $this->modelPermission->hasGlobalPermission(
                    'superadmin',
                    'read'
                    )
                )
            ],
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
            'usecaptcha' => ['type' => static::FIELD_TYPE_USE_CAPTCHA, 'compositeInput' => true],
             // 'usecaptcha_surveyaccess' => [], // used on input to calculate 'usecaptcha'
             // 'usecaptcha_registration' => [], // used on input to calculate 'usecaptcha'
             // 'usecaptcha_saveandload' => [], // used on input to calculate 'usecaptcha'
            'emailresponseto' => [],
            'emailnotificationto' => [],
            'googleanalyticsapikeysetting' => [],
            'googleanalyticsapikey' => [],
            'googleanalyticsstyle' => [],
            'tokenlength' => [],
            'adminemail' => [],
            'bounce_email' => [],
            'gsid' => ['default' => 1],


            // from Database::actionUpdateSurveyLocaleSettingsGeneralSettings()
            'language' => [],
            'additional_languages' => [],
            'admin' => [],
            'adminemail' => [],
            'bounce_email' => [],
            'gsid' => [],
            'format' => [],
            'template' => []
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

        $fieldOpts = !empty($fieldOpts)
            ? $fieldOpts
            : [];

        // Composite inputs always have to be processed
        // - even if the field itself is not provided
        $isCompositeInput = (
            isset($fieldOpts['compositeInput'])
            && $fieldOpts['compositeInput']
        );

        if (
            !isset($input[$field])
            && !$isCompositeInput
        ) {
            return $meta;
        }

        $type = !empty($fieldOpts['type'])
            ? $fieldOpts['type']
            : null;
        $initValue = $survey->{$field} ?? '';
        $default = !empty($fieldOpts['default'])
            ? $fieldOpts['default']
            : $initValue;

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
            case static::FIELD_TYPE_USE_CAPTCHA:
                $value = $this->calculateUseCaptchaOption(
                    $input['usecaptcha_surveyaccess'] ?? null,
                    $input['usecaptcha_registration'] ?? null,
                    $input['usecaptcha_saveandload'] ?? null
                );
                if (is_null($value)) {
                    $value = $default;
                }
            break;
            case 'int':
                $value = (int) $value;
            break;
            case null:
            default:
                $value = is_null($value)
                    ? $default
                    : $value;
            break;
        }

        switch ($field) {
            case 'tokenlength':
                $value = (int) (
                    (
                        ($value  < 5 || $value  > 36)
                        && $value != -1
                    )
                    ? 15
                    : $value
                );
            break;
            case 'additional_languages':
                if (is_array($value)) {
                    if (
                        (
                            $index = array_search(
                            $survey->language,
                            $value
                            )
                        ) !== false
                    ) {
                        unset($value[$index]);
                    }
                    $value = implode(
                        ' ',
                        $value
                    );
                }
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

    /**
     * Transcribe from 3 checkboxes to 1 char for captcha usages
     * Uses variables from $_POST and transferred Surveyobject
     *
     * 'A' = All three captcha enabled
     * 'B' = All but save and load
     * 'C' = All but registration
     * 'D' = All but survey access
     * 'X' = Only survey access
     * 'R' = Only registration
     * 'S' = Only save and load
     *
     * 'E' = All inherited
     * 'F' = Inherited save and load + survey access + registration
     * 'G' = Inherited survey access + registration + save and load
     * 'H' = Inherited registration + save and load + survey access
     * 'I' = Inherited save and load + inherited survey access + registration
     * 'J' = Inherited survey access + inherited registration + save and load
     * 'K' = Inherited registration + inherited save and load + survey access
     *
     * 'L' = Inherited survey access + save and load
     * 'M' = Inherited survey access + registration
     * 'O' = Inherited registration + survey access
     * '1' = Inherited survey access + inherited registration
     * '2' = Inherited survey access + inherited save and load
     * '3' = Inherited registration + inherited save and load
     * '4' = Inherited survey access
     * '5' = Inherited save and load
     * '6' = Inherited registration
     *
     * 'N' = None
     *
     * @return string One character that corresponds to captcha usage
     * @todo Should really be saved as three fields in the database!
     * @todo Copied from Survey:::saveTranscribeCaptchaOptions() replace uses of original copy.
     */
    private function calculateUseCaptchaOption($surveyaccess, $registration, $saveandload)
    {
        if ($surveyaccess === null && $registration === null && $saveandload === null) {
            return null;
        }

        if ($surveyaccess == 'I' && $registration == 'I' && $saveandload == 'I') {
            return 'E';
        } elseif ($surveyaccess == 'Y' && $registration == 'Y' && $saveandload == 'I') {
            return 'F';
        } elseif ($surveyaccess == 'I' && $registration == 'Y' && $saveandload == 'Y') {
            return 'G';
        } elseif ($surveyaccess == 'Y' && $registration == 'I' && $saveandload == 'Y') {
            return 'H';
        } elseif ($surveyaccess == 'I' && $registration == 'Y' && $saveandload == 'I') {
            return 'I';
        } elseif ($surveyaccess == 'I' && $registration == 'I' && $saveandload == 'Y') {
            return 'J';
        } elseif ($surveyaccess == 'Y' && $registration == 'I' && $saveandload == 'I') {
            return 'K';
        } elseif ($surveyaccess == 'I' && $saveandload == 'Y') {
            return 'L';
        } elseif ($surveyaccess == 'I' && $registration == 'Y') {
            return 'M';
        } elseif ($registration == 'I' && $surveyaccess == 'Y') {
            return 'O';
        } elseif ($registration == 'I' && $saveandload == 'Y') {
            return 'P';
        } elseif ($saveandload == 'I' && $surveyaccess == 'Y') {
            return 'T';
        } elseif ($saveandload == 'I' && $registration == 'Y') {
            return 'U';
        } elseif ($surveyaccess == 'I' && $registration == 'I') {
            return '1';
        } elseif ($surveyaccess == 'I' && $saveandload == 'I') {
            return '2';
        } elseif ($registration == 'I' && $saveandload == 'I') {
            return '3';
        } elseif ($surveyaccess == 'I') {
            return '4';
        } elseif ($saveandload == 'I') {
            return '5';
        } elseif ($registration == 'I') {
            return '6';
        } elseif ($surveyaccess == 'Y' && $registration == 'Y' && $saveandload == 'Y') {
            return 'A';
        } elseif ($surveyaccess == 'Y' && $registration == 'Y') {
            return 'B';
        } elseif ($surveyaccess == 'Y' && $saveandload == 'Y') {
            return 'C';
        } elseif ($registration == 'Y' && $saveandload == 'Y') {
            return 'D';
        } elseif ($surveyaccess == 'Y') {
            return 'X';
        } elseif ($registration == 'Y') {
            return 'R';
        } elseif ($saveandload == 'Y') {
            return 'S';
        }

        return 'N';
    }
}
