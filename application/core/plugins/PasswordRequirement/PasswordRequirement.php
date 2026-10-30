<?php

/**
 * Core plugin for LimeSurvey : password requirement settings
 * @version 1.2.0
 */

class PasswordRequirement extends \LimeSurvey\PluginManager\PluginBase
{
     /**
     * Where to save plugin settings etc.
     * @var string
     */
    protected $storage = 'DbStorage';

    const DEFAULT_NEEDS_NUMBER = true;
    const DEFAULT_NEEDS_UPPERCASE = true;
    const DEFAULT_NEEDS_LOWERCASE = false;
    const DEFAULT_NEEDS_NON_ALPHANUMERIC = false;
    const DEFAULT_MINIMUM_SIZE = 12;
    /** No maximum length */
    const DEFAULT_MAXIMUM_SIZE = 0;
    const DEFAULT_SURVEY_SAVE_ACTIVE = false;
    const DEFAULT_SURVEY_SAVE_NEEDS_NUMBER = false;
    const DEFAULT_SURVEY_SAVE_NEEDS_UPPERCASE = false;
    const DEFAULT_SURVEY_SAVE_NEEDS_NON_ALPHANUMERIC = false;
    const DEFAULT_SURVEY_SAVE_MINIMUM_SIZE = 8;

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    /** @var array|null The administration password rules, read once per request */
    private $adminRules;

    protected $settings = [
        'adminPart' => array(
            'title' => 'Password requirements for administration login',
            'type' => 'separator',
        ),
        'needsNumber' => array(
            'label' => 'Require at least one digit',
            'type' => 'boolean',
            'default' => self::DEFAULT_NEEDS_NUMBER,
        ),
        'needsUppercase' => array(
            'label' => 'Require at least one uppercase character',
            'type' => 'boolean',
            'default' => self::DEFAULT_NEEDS_UPPERCASE,
        ),
        'needsLowercase' => array(
            'label' => 'Require at least one lowercase character',
            'type' => 'boolean',
            'default' => self::DEFAULT_NEEDS_LOWERCASE,
        ),
        'needsNonAlphanumeric' => array(
            'label' => 'Require at least one special character',
            'type' => 'boolean',
            'default' => self::DEFAULT_NEEDS_NON_ALPHANUMERIC,
        ),
        'minimumSize' => array(
            'label' => 'Minimum password length',
            'type' => 'int',
        ),
        'maximumSize' => array(
            'label' => 'Maximum password length',
            'type' => 'int',
        ),
        'surveyPart' => array(
            'title' => 'Password requirements for “Save and return later” feature',
            'type' => 'separator',
        ),
        'surveySaveActive' => array(
            'type' => 'boolean',
            'label' => 'Check password when saving survey',
            'default' => self::DEFAULT_SURVEY_SAVE_ACTIVE,
        ),
        'surveySaveNeedsNumber' => array(
            'label' => 'Require at least one digit',
            'type' => 'boolean',
            'default' => self::DEFAULT_SURVEY_SAVE_NEEDS_NUMBER,
        ),
        'surveySaveNeedsUppercase' => array(
            'label' => 'Require at least one uppercase character',
            'type' => 'boolean',
            'default' => self::DEFAULT_SURVEY_SAVE_NEEDS_UPPERCASE,
        ),
        'surveySaveNeedsNonAlphanumeric' => array(
            'label' => 'Require at least one special character',
            'type' => 'boolean',
            'default' => self::DEFAULT_SURVEY_SAVE_NEEDS_NON_ALPHANUMERIC,
        ),
        'surveySaveMinimumSize' => array(
            'label' => 'Minimum password length',
            'type' => 'int',
        ),
    ];
    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('checkPasswordRequirement');
        $this->subscribe('getPasswordRequirements');
        $this->subscribe('createRandomPassword');
        $this->subscribe('beforeDeactivate');

        $this->subscribe('saveSurveyForm', 'validateSaveSurveyForm');
    }

    /**
     * The password rules for administration users are only set here, so the plugin can't be deactivated.
     * @return void
     */
    public function beforeDeactivate()
    {
        $this->getEvent()->set('success', false);
        $this->getEvent()->set('message', gT('This core plugin can not be disabled.'));
    }

    /**
     * @see event checkPasswordRequirement
     * Check an administration user password against the plugin settings
     * @return void
     */
    public function checkPasswordRequirement()
    {
        $oEvent = $this->getEvent();
        $password = $oEvent->get('password');
        $rules = $this->getAdminRules();
        $errors = $this->checkValidityOfPassword(
            $password,
            $rules['needsNumber'],
            $rules['needsUppercase'],
            $rules['needsNonAlphanumeric'],
            $rules['minimumSize'],
            $rules['needsLowercase'],
            $rules['maximumSize']
        );
        if ($errors) {
            $oEvent->set('passwordOk', false);
            $oEvent->set('passwordError', $errors[0]); // Previous system
            $oEvent->set('passwordErrors', $errors);
        }
    }

    /** @see event
     * get the current save action password and add errors if needed
     * @return void
     * */
    public function validateSaveSurveyForm()
    {
        if (!$this->get('surveySaveActive', null, null, self::DEFAULT_SURVEY_SAVE_ACTIVE)) {
            return;
        }
        $event = $this->getEvent();
        if ($event->get('state') != 'validate') {
            // Action only when validate
            return;
        }
        $saveData = $event->get('saveData');
        $aSaveErrors = $event->get('aSaveErrors');
        if (empty($saveData['clearpassword'])) {
            // No need to check password if empty : core disallow it
            return;
        }

        $password = $saveData['clearpassword'];
        $errors = $this->checkValidityOfPassword(
            $password,
            $this->get('surveySaveNeedsNumber', null, null, self::DEFAULT_SURVEY_SAVE_NEEDS_NUMBER),
            $this->get('surveySaveNeedsUppercase', null, null, self::DEFAULT_SURVEY_SAVE_NEEDS_UPPERCASE),
            $this->get('surveySaveNeedsNonAlphanumeric', null, null, self::DEFAULT_SURVEY_SAVE_NEEDS_NON_ALPHANUMERIC),
            $this->get('surveySaveMinimumSize', null, null, self::DEFAULT_SURVEY_SAVE_MINIMUM_SIZE)
        );
        if (empty($errors)) {
            return;
        }
        $event->append('aSaveErrors', $errors);
    }

    /**
     * @see event getPasswordRequirements
     * Add the administration password rules to the text shown below password fields
     * @return void
     */
    public function getPasswordRequirements()
    {
        $requirements = [];
        $rules = $this->getAdminRules();
        $minimumSize = $rules['minimumSize'];
        if ($minimumSize > 0) {
            $requirements[] = sprintf(gTPlural('At least %d character long.', 'At least %d characters long.', $minimumSize), $minimumSize);
        }
        $maximumSize = $rules['maximumSize'];
        if ($maximumSize > 0) {
            $requirements[] = sprintf(gTPlural('At most %d character long.', 'At most %d characters long.', $maximumSize), $maximumSize);
        }
        if ($rules['needsLowercase']) {
            $requirements[] = sprintf(gTPlural('At least %d lower case letter.', 'At least %d lower case letters.', 1), 1);
        }
        if ($rules['needsUppercase']) {
            $requirements[] = sprintf(gTPlural('At least %d upper case letter.', 'At least %d upper case letters.', 1), 1);
        }
        if ($rules['needsNumber']) {
            $requirements[] = sprintf(gTPlural('At least %d number.', 'At least %d numbers.', 1), 1);
        }
        if ($rules['needsNonAlphanumeric']) {
            $requirements[] = sprintf(gTPlural('At least %d special character.', 'At least %d special characters.', 1), 1);
        }
        $oEvent = $this->getEvent();
        $oEvent->set('requirements', array_merge((array) $oEvent->get('requirements'), $requirements));
    }

    /**
     * @inheritdoc
     * Nothing is saved when the maximum password length is below the shortest possible password.
     * The administration password rules are read again after saving.
     * @return false|void false if the settings were not saved
     */
    public function saveSettings($settings)
    {
        $rules = $this->getAdminRules();
        $maximumSize = array_key_exists('maximumSize', $settings) ? (int) $settings['maximumSize'] : $rules['maximumSize'];
        if ($maximumSize > 0) {
            $minimumSize = $rules['minimumSize'];
            if (array_key_exists('minimumSize', $settings)) {
                $minimumSize = ($settings['minimumSize'] === null || $settings['minimumSize'] === '') ? self::DEFAULT_MINIMUM_SIZE : (int) $settings['minimumSize'];
            }
            // Random passwords always have a lowercase character, and one character of each other required kind
            $requiredCharacters = 1;
            foreach (['needsUppercase', 'needsNumber', 'needsNonAlphanumeric'] as $setting) {
                $requiredCharacters += (int) (array_key_exists($setting, $settings) ? !empty($settings[$setting]) : $rules[$setting]);
            }
            $shortestSize = max($minimumSize, $requiredCharacters);
            if ($maximumSize < $shortestSize) {
                App()->setFlashMessage(
                    sprintf($this->gT('The settings were not saved: The maximum password length must be at least %d.'), $shortestSize),
                    'error'
                );
                return false;
            }
        }
        $this->adminRules = null;
        parent::saveSettings($settings);
    }

    /**
     * Get the password rules for administration users from the plugin settings
     * @return array with keys needsNumber, needsUppercase, needsLowercase, needsNonAlphanumeric (bool), minimumSize and maximumSize (int, 0 means no limit)
     */
    private function getAdminRules()
    {
        if ($this->adminRules === null) {
            $this->adminRules = [
                'needsNumber' => (bool) $this->get('needsNumber', null, null, self::DEFAULT_NEEDS_NUMBER),
                'needsUppercase' => (bool) $this->get('needsUppercase', null, null, self::DEFAULT_NEEDS_UPPERCASE),
                'needsLowercase' => (bool) $this->get('needsLowercase', null, null, self::DEFAULT_NEEDS_LOWERCASE),
                'needsNonAlphanumeric' => (bool) $this->get('needsNonAlphanumeric', null, null, self::DEFAULT_NEEDS_NON_ALPHANUMERIC),
                'minimumSize' => $this->getSize('minimumSize', self::DEFAULT_MINIMUM_SIZE),
                'maximumSize' => $this->getSize('maximumSize', self::DEFAULT_MAXIMUM_SIZE),
            ];
        }
        return $this->adminRules;
    }

    /**
     * Get a password length setting, a setting saved blank uses the default
     * @param string $setting the setting name
     * @param int $default
     * @return int
     */
    private function getSize($setting, $default)
    {
        $size = $this->get($setting, null, null, $default);
        if ($size === null || $size === '') {
            return $default;
        }
        return (int) $size;
    }

    /**
     * Check the validity of a password according to option
     * @param string $password
     * @param boolean $needsNumber
     * @param boolean $needsUppercase
     * @param boolean $needsNonAlphanumeric
     * @param int $minimumSize
     * @param boolean $needsLowercase
     * @param int $maximumSize 0 means no maximum length
     * @return null|array, null means no issue.
     */
    private function checkValidityOfPassword($password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $minimumSize = 8, $needsLowercase = false, $maximumSize = 0)
    {
        $errors = [];
        if ($needsNumber && preg_match('/\d/', $password) === 0) {
            $errors[] = gT('The password does require at least one digit');
        }
        if ($needsUppercase && strtolower($password) == $password) {
            $errors[] = gT('The password does require at least one uppercase character');
        }
        if ($needsLowercase && strtoupper($password) == $password) {
            $errors[] = gT('The password does require at least one lowercase character');
        }
        if ($needsNonAlphanumeric && ctype_alnum($password)) {
            $errors[] = gT('The password does require at least one special character');
        }
        if ($minimumSize && strlen($password) < $minimumSize) {
            $errors[] = sprintf(gT('The password does not reach the minimum length of %s characters'), $minimumSize);
        }
        if ($maximumSize && strlen($password) > $maximumSize) {
            $errors[] = sprintf(gT('The password exceeds the maximum length of %s characters'), $maximumSize);
        }
        if (empty($errors)) {
            return null;
        }
        return $errors;
    }

    /**
     * @see event createRandomPassword
     * Create a random password matching the administration password rules,
     * unless a plugin dispatched before already created one
     * @return void
     */
    public function createRandomPassword()
    {
        $oEvent = $this->getEvent();
        $password = $oEvent->get('password');
        if ($password !== null && $password !== '') {
            return;
        }
        $targetSize = $oEvent->get('targetSize', 8);

        $rules = $this->getAdminRules();
        $targetSize = max($targetSize, $rules['minimumSize']);
        if ($rules['maximumSize'] > 0) {
            $targetSize = min($targetSize, $rules['maximumSize']);
        }

        // Lowercase characters are always used, so needsLowercase needs nothing more
        $randomPassword = $this->getRandomString($targetSize, $rules['needsUppercase'], $rules['needsNumber'], $rules['needsNonAlphanumeric']);

        $oEvent->set('password', $randomPassword);
    }

      /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     *
     */
    public function getPluginSettings($getValues = true)
    {
        $settings = parent::getPluginSettings($getValues);
        $settings['adminPart']['title'] = $this->gT("Password requirements for administration login");
        $settings['needsNumber']['label'] = $this->gT("Require at least one digit");
        $settings['needsUppercase']['label'] = $this->gT("Require at least one uppercase character");
        $settings['needsLowercase']['label'] = $this->gT("Require at least one lowercase character");
        $settings['needsNonAlphanumeric']['label'] = $this->gT("Require at least one special character");
        $settings['minimumSize']['label'] = $this->gT("Minimum password length");
        $settings['minimumSize']['help'] = sprintf(gT('Default value will be %d if left blank'), self::DEFAULT_MINIMUM_SIZE);
        $settings['maximumSize']['label'] = $this->gT("Maximum password length");
        $settings['maximumSize']['help'] = $this->gT("No maximum length if left blank or 0");
        $settings['surveyPart']['title'] = $this->gT("Password requirements for “Save and return later” feature");
        $settings['surveySaveActive']['label'] = $this->gT("Check password when use “Save and return later” feature");
        $settings['surveySaveNeedsNumber']['label'] = $this->gT("Require at least one digit");
        $settings['surveySaveNeedsUppercase']['label'] = $this->gT("Require at least one uppercase character");
        $settings['surveySaveNeedsNonAlphanumeric']['label'] = $this->gT("Require at least one special character");
        $settings['surveySaveMinimumSize']['label'] = $this->gT("Minimum password length");
        $settings['surveySaveMinimumSize']['help'] = sprintf(gT('Default value will be %d if left blank'), self::DEFAULT_SURVEY_SAVE_MINIMUM_SIZE);
        return $settings;
    }

    private function getRandomString($length = 8, $uppercase = false, $numeric = false, $nonAlpha = false)
    {
        // Init
        $str = '';

        /**
         * For each required character set:
         * - Add one character to the output string.
         * - Add the char set to the general char pool.
         */

        // Lowercase (always on)
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $str .= static::pickRandomChar($chars);

        // Uppercase if applies
        // Add one character and also add the charset to the pool of available chars
        if ($uppercase) {
            $uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str .= static::pickRandomChar($uppercase_chars);
            $chars .= $uppercase_chars;
        }

        // Numeric if applies
        // Add one character and also add the charset to the pool of available chars
        if ($numeric) {
            $numeric_chars = '0123456789';
            $str .= static::pickRandomChar($numeric_chars);
            $chars .= $numeric_chars;
        }

        // NonAlpha if applies
        // Add one character and also add the charset to the pool of available chars
        if ($nonAlpha) {
            $nonAlpha_chars = '-=!@#$%&*_+,.?;:';
            $str .= static::pickRandomChar($nonAlpha_chars);
            $chars .= $nonAlpha_chars;
        }

        // Trim in case length is less than the already appended characters
        if (strlen($str) > $length) {
            $str = substr($str, 0, $length);
        }

        /**
         * Pick remaining characters from the general char pool
         */

        // Fill string from general char pool
        for ($i = strlen($str); $i < $length; $i++) {
            $str .= static::pickRandomChar($chars);
        }

        /**
         * Wrap up
         */

        // Shuffle, as to not have always to start with the loweracse, then uppercase, ...
        $str = str_shuffle($str);

        return $str;
    }

    /**
     * Returns a random number using random_int if available or mt_rand if not.
     * @param int $max The highest value to be returned
     * @param int $min The lowest value to be returned
     * @return int
     */
    private static function safeRandom($max, $min = 0)
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }
        return mt_rand($min, $max);
    }

    /**
     * Returns a random character from a string
     * @param string $chars Pool of characters from which to pick
     * @return string Picked character
     */
    private static function pickRandomChar($chars)
    {
        return $chars[static::safeRandom(strlen($chars) - 1)];
    }
}
