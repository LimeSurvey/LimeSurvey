<?php

namespace LimeSurvey\Models\Services;

use Survey;

class SurveyUseCaptcha
{
    /**
     * Code/Mapping for useCaptcha
     */
    const USE_CAPTCHA = ['A', 'B', 'C', 'D', 'X', 'R', 'S', 'N', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M', 'O', 'P', 'T', 'U', '1', '2', '3', '4', '5', '6'];

    /**
     * Code/Mapping for useCaptcha in survey access
     */
    const SURVEY_ACCESS_YES = ['A', 'B', 'C', 'X', 'F', 'H', 'K', 'O', 'T'];
    const SURVEY_ACCESS_NO = ['P', 'U', '3', '5', '6', 'D', 'R', 'S', 'N'];
    const SURVEY_ACCESS_INHERIT = ['E', 'G', 'I', 'J', 'L', 'M', '1', '2', '4'];

    /**
     * Code/Mapping for useCaptcha in registration
     */
    const REGISTRATION_YES = ['A', 'B', 'D', 'R', 'F', 'G', 'I', 'M', 'U'];
    const REGISTRATION_NO = ['L', 'T', '2', '4', '5', 'C', 'X', 'S', 'N'];
    const REGISTRATION_INHERIT = ['E', 'H', 'J', 'K', 'O', 'P', '1', '3', '6'];

    /**
     * Code/Mapping for useCaptcha in save and load
     */
    const SAVE_LOAD_YES = ['A', 'C', 'D', 'S', 'G', 'H', 'J', 'L', 'P'];
    const SAVE_LOAD_NO = ['M', 'O', '1', '4', '6', 'B', 'X', 'R', 'N'];
    const SAVE_LOAD_INHERIT = ['E', 'F', 'I', 'K', 'T', 'U', '2', '3', '5'];


    /** @var \Survey */
    private $survey;

    public function __construct($surveyId = null, Survey $survey = null)
    {
        if ($survey === null) {
            $this->survey = Survey::model()->findByPk($surveyId);
        } else {
            $this->survey = $survey;
        }
    }

    /**
     * Saves the new value for survey access in db.
     *
     * @param $value bool survey access
     * @param $mode string mode of conversion can be 'surveyAccess', 'registration' or 'saveAndLoad'
     *
     * @return string
     */
    public function convertSurveyAccessToUseCaptcha(bool $value, string $mode)
    {

        //get other two values from survey
        $accessRegistrationSaveload = $this->convertUseCaptchaFromDB($this->survey->usecaptcha);
        $convertedValue = $value ? 'Y' : 'N';

        //overwrite value
        switch ($mode) {
            case'surveyAccess':
                $accessRegistrationSaveload['surveyAccess'] = $convertedValue;
                break;
            case'registration':
                $accessRegistrationSaveload['registration'] = $convertedValue;
                break;
            case'saveAndLoad':
                $accessRegistrationSaveload['saveAndLoad'] = $convertedValue;
                break;
            default:
                throw new \Exception("Invalid mode: {$mode}");
        }

        $newCaptchaValue = $this->convertUseCaptchaForDB(
            $accessRegistrationSaveload['surveyAccess'],
            $accessRegistrationSaveload['registration'],
            $accessRegistrationSaveload['saveAndLoad']
        );

        $this->survey->usecaptcha = $newCaptchaValue;
        $this->survey->save();

        return $newCaptchaValue;
    }

    /**
     * Convert from 3 values to 1 char for captcha usages
     *
     *  'A' = All three captcha enabled
     *  'B' = All but save and load
     *  'C' = All but registration
     *  'D' = All but survey access
     *  'X' = Only survey access
     *  'R' = Only registration
     *  'S' = Only save and load
     *
     *  'E' = All inherited
     *  'F' = Inherited save and load + survey access + registration
     *  'G' = Inherited survey access + registration + save and load
     *  'H' = Inherited registration + save and load + survey access
     *  'I' = Inherited save and load + inherited survey access + registration
     *  'J' = Inherited survey access + inherited registration + save and load
     *  'K' = Inherited registration + inherited save and load + survey access
     *
     *  'L' = Inherited survey access + save and load
     *  'M' = Inherited survey access + registration
     *  'O' = Inherited registration + survey access
     *  '1' = Inherited survey access + inherited registration
     *  '2' = Inherited survey access + inherited save and load
     *  '3' = Inherited registration + inherited save and load
     *  '4' = Inherited survey access
     *  '5' = Inherited save and load
     *  '6' = Inherited registration
     *
     *  'N' = None
     * @param $surveyAccess
     * @param $registration
     * @param $saveAndLoad
     * @return string One character that corresponds to captcha usage
     */
    public function convertUseCaptchaForDB($surveyAccess, $registration, $saveAndLoad)
    {
        if ($surveyAccess == 'I' && $registration == 'I' && $saveAndLoad == 'I') {
            return 'E';
        } elseif ($surveyAccess == 'Y' && $registration == 'Y' && $saveAndLoad == 'I') {
            return 'F';
        } elseif ($surveyAccess == 'I' && $registration == 'Y' && $saveAndLoad == 'Y') {
            return 'G';
        } elseif ($surveyAccess == 'Y' && $registration == 'I' && $saveAndLoad == 'Y') {
            return 'H';
        } elseif ($surveyAccess == 'I' && $registration == 'Y' && $saveAndLoad == 'I') {
            return 'I';
        } elseif ($surveyAccess == 'I' && $registration == 'I' && $saveAndLoad == 'Y') {
            return 'J';
        } elseif ($surveyAccess == 'Y' && $registration == 'I' && $saveAndLoad == 'I') {
            return 'K';
        } elseif ($surveyAccess == 'I' && $saveAndLoad == 'Y') {
            return 'L';
        } elseif ($surveyAccess == 'I' && $registration == 'Y') {
            return 'M';
        } elseif ($registration == 'I' && $surveyAccess == 'Y') {
            return 'O';
        } elseif ($registration == 'I' && $saveAndLoad == 'Y') {
            return 'P';
        } elseif ($saveAndLoad == 'I' && $surveyAccess == 'Y') {
            return 'T';
        } elseif ($saveAndLoad == 'I' && $registration == 'Y') {
            return 'U';
        } elseif ($surveyAccess == 'I' && $registration == 'I') {
            return '1';
        } elseif ($surveyAccess == 'I' && $saveAndLoad == 'I') {
            return '2';
        } elseif ($registration == 'I' && $saveAndLoad == 'I') {
            return '3';
        } elseif ($surveyAccess == 'I') {
            return '4';
        } elseif ($saveAndLoad == 'I') {
            return '5';
        } elseif ($registration == 'I') {
            return '6';
        } elseif ($surveyAccess == 'Y' && $registration == 'Y' && $saveAndLoad == 'Y') {
            return 'A';
        } elseif ($surveyAccess == 'Y' && $registration == 'Y') {
            return 'B';
        } elseif ($surveyAccess == 'Y' && $saveAndLoad == 'Y') {
            return 'C';
        } elseif ($registration == 'Y' && $saveAndLoad == 'Y') {
            return 'D';
        } elseif ($surveyAccess == 'Y') {
            return 'X';
        } elseif ($registration == 'Y') {
            return 'R';
        } elseif ($saveAndLoad == 'Y') {
            return 'S';
        }

        return 'N';
    }

    /**
     * Converts useCaptcha from db to the real values Y/N for all three types (access, registration,saveAndLoad).
     *
     * @param string $useCaptcha
     * @param bool $showInherited if set to true, then 'I' should be included. Otherwise, only Y/N should be returned as result.
     * @return array has the following structure
     *               ['surveyAccess']
     *               ['registration']
     *               ['saveAndLoad']
     */
    public function convertUseCaptchaFromDB($useCaptcha, $showInherited = true)
    {

        //------------------------------- SURVEY ACCESS ----------------------------------------------------
        $captchaValues['surveyAccess'] = (in_array($useCaptcha, SurveyUseCaptcha::SURVEY_ACCESS_YES))
            ? 'Y'
            : ((in_array($useCaptcha, SurveyUseCaptcha::SURVEY_ACCESS_INHERIT)) ? ('I') : ('N'));

        if (!$showInherited && $captchaValues['surveyAccess'] === 'I') {
            $surveyGroupSettings = \SurveysGroupsettings::getInstance($this->survey->gsid);
            $captchaValues['surveyAccess'] = in_array($surveyGroupSettings->usecaptcha,
                SurveyUseCaptcha::SURVEY_ACCESS_YES) ? 'Y' : 'N';
        }

        //---------------------------------REGISTRATION -----------------------------------------------------
        $captchaValues['registration'] = (in_array($useCaptcha, SurveyUseCaptcha::REGISTRATION_YES))
            ? 'Y'
            : ((in_array($useCaptcha, SurveyUseCaptcha::REGISTRATION_INHERIT)) ? ('I') : ('N'));

        if (!$showInherited && $captchaValues['registration'] === 'I') {
            $surveyGroupSettings = \SurveysGroupsettings::getInstance($this->survey->gsid);
            $captchaValues['registration'] = in_array($surveyGroupSettings->usecaptcha,
                SurveyUseCaptcha::REGISTRATION_YES) ? 'Y' : 'N';
        }

        //-------------------------------SAVE AND LOAD ------------------------------------------------------
        $captchaValues['saveAndLoad'] = (in_array($useCaptcha, SurveyUseCaptcha::SAVE_LOAD_YES))
            ? 'Y'
            : ((in_array($useCaptcha, SurveyUseCaptcha::SAVE_LOAD_INHERIT)) ? ('I') : ('N'));

        if (!$showInherited && $captchaValues['saveAndLoad'] === 'I') {
            $surveyGroupSettings = \SurveysGroupsettings::getInstance($this->survey->gsid);
            $captchaValues['saveAndLoad'] = in_array($surveyGroupSettings->usecaptcha,
                SurveyUseCaptcha::SAVE_LOAD_YES) ? 'Y' : 'N';
        }

        return $captchaValues;
    }

    public function reCalculateUseCaptcha($data)
    {
        //first get the three values from original usecaptcha
        $useCaptchaValues = $this->convertUseCaptchaFromDB($this->survey->usecaptcha);

        if (isset($data['useCaptchaAccess'])) {
            $useCaptchaValues['surveyAccess'] = $data['useCaptchaAccess'];
        }
        if (isset($data['useCaptchaRegistration'])) {
            $useCaptchaValues['registration'] = $data['useCaptchaRegistration'];
        }
        if (isset($data['useCaptchaSaveLoad'])) {
            $useCaptchaValues['saveAndLoad'] = $data['useCaptchaSaveLoad'];
        }

        return $this->convertUseCaptchaForDB(
            $useCaptchaValues['surveyAccess'],
            $useCaptchaValues['registration'],
            $useCaptchaValues['saveAndLoad']
        );
    }
}
