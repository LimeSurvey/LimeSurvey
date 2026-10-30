<?php

namespace ls\tests;

class PasswordRequirementTest extends TestBaseClass
{

    private static $plugin;
    private static $pluginName = 'PasswordRequirement';
    private static $pwdGlobalMinLength = null;

    // Character sets
    private static $set_chars = "abcdefghijklmnopqrstuvwxyz";
    private static $set_numeric_chars = '0123456789';
    private static $set_uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private static $set_nonAlpha_chars = '-=!@#$%&*_+,.?;:';

    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Get a handle to the plugin being testes
        self::$plugin = App()->getPluginManager()->loadPlugin(self::$pluginName);
        
        // Activate it, if not already
        self::installAndActivatePlugin(self::$pluginName);

        // Get min length
        $pluginsettings = self::$plugin->getPluginSettings();
        self::$pwdGlobalMinLength = $pluginsettings['minimumSize']['current'] ?? \PasswordRequirement::DEFAULT_MINIMUM_SIZE;
    }

    private function createRandomPassword($needsNumber, $needsUppercase, $needsNonAlphanumeric, $length)
    {
        self::$plugin->saveSettings([
            'needsNumber' => $needsNumber,
            'needsUppercase' => $needsUppercase,
            'needsNonAlphanumeric' => $needsNonAlphanumeric,
        ]);
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'createRandomPassword', [
            'targetSize' => $length,
        ]);
        
        return $oEvent->get('password');
    }

    private function checkPassword($password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $length)
    {
        self::$plugin->saveSettings([
            'needsNumber' => $needsNumber,
            'needsUppercase' => $needsUppercase,
            'needsNonAlphanumeric' => $needsNonAlphanumeric,
            'minimumSize' => $length,
        ]);        
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'checkPasswordRequirement', [
            'password' => $password,
        ]);

        return $oEvent->get('passwordErrors');
    }

    /**
     * Evaluates if a password matches requirements
     *
     * @param string $password Password to be evaluated
     * @param array $variation Requirement Details [$needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
     */
    protected function evalPasswordReqs($password, $variation)
    {
        // Get msg
        $msg = end($variation);

        // Check password generated
        $this->assertNotEmpty($password, $msg . " Password is empty.");

        // Check length
        $passLen = strlen($password);
        $expLen = $variation[3];
        $this->assertTrue($passLen == $expLen, $msg . " Password has wrong length. Has {$passLen}, while {$expLen} expected.");

        // Check lower characters
        $this->assertMatchesRegularExpression('/[' . self::$set_chars . ']/', $password, $msg . " Password does not have lower chars.");
        
        // Check numbers
        if ($variation[0]) {
            $this->assertMatchesRegularExpression('/[' . self::$set_numeric_chars . ']/', $password, $msg . " Password does not have numbers.");
        } else {
            $this->assertDoesNotMatchRegularExpression('/[' . self::$set_numeric_chars . ']/', $password, $msg . " Password has numbers.");
        }

        // Check upper chars
        if ($variation[1]) {
            $this->assertMatchesRegularExpression('/[' . self::$set_uppercase_chars . ']/', $password, $msg . " Password does not have upper chars.");
        } else {
            $this->assertDoesNotMatchRegularExpression('/[' . self::$set_uppercase_chars . ']/', $password, $msg . " Password has upper chars.");
        }

        // Check non-alpha
        if ($variation[2]) {
            $this->assertMatchesRegularExpression('/[' . self::$set_nonAlpha_chars . ']/', $password, $msg . " Password does not have non-alpha chars.");
        } else {
            $this->assertDoesNotMatchRegularExpression('/[' . self::$set_nonAlpha_chars . ']/', $password, $msg . " Password has non-alpha chars.");
        }
    }

    public function testGetRandomString()
    {
        // Init length
        $pwdLengthOk = self::$pwdGlobalMinLength;

        /**
         * No Errors Expected
         */
        $okVariations = [
            // [$needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
            [false, false, false, $pwdLengthOk, 'Created random password, with no requirements.'],
            [false, false, false, $pwdLengthOk + 1, 'Created random password, larger chars.'],
            [true, false, false, $pwdLengthOk, 'Created random password, only number required.'],
            [false, true, false, $pwdLengthOk, 'Created random password, only upper required.'],
            [true, false, true, $pwdLengthOk, 'Created random password, only non alpha-required.'],
            [true, true, false, $pwdLengthOk, 'Created random password, only number and upper required.'],
            [true, true, true, $pwdLengthOk, 'Created random password, only number, upper required and non-alpha.'],
        ];

        foreach ($okVariations as $variation) {
            $createVariation = array_slice($variation, 0, count($variation) - 1);
            $password = call_user_func_array([$this, 'createRandomPassword'], $createVariation);
                        
            $this->evalPasswordReqs($password, $variation);
        }

        /**
         * Special Cases.
         * No errors expected
         */
        
         // Asking for a pwd length shorter than global password length setting.
         // Password shall be generated with the global min
         $variation = [false, false, false, $pwdLengthOk - 1, 'Created random password, shorter length than global.'];
         $createVariation = array_slice($variation, 0, count($variation) - 1);
         $password = call_user_func_array([$this, 'createRandomPassword'], $createVariation);
         // Set expected length different than requested length
         $variation[3] = self::$pwdGlobalMinLength;
         $this->evalPasswordReqs($password, $variation);
    }

    public function testCheckValidityOfPassword()
    {
        /**
         * No Errors Expected
         */
        $hasAllCharsPassword = '123abcABC@#$';
        $okVariations = [
            // [$password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
            [$hasAllCharsPassword, false, false, false, 12, 'Tested with no requirements, password check failed while it should have been OK.'],
            [$hasAllCharsPassword, true, false, false, 12, 'Tested number required, password check failed while it should have been OK.'],
            [$hasAllCharsPassword, false, true, false, 12, 'Tested upper required, password check failed while it should have been OK.'],
            [$hasAllCharsPassword, true, false, true, 12, 'Tested non alpha-required, password check failed while it should have been OK.'],
            [$hasAllCharsPassword, true, true, false, 12, 'Tested number and upper required, password check failed while it should have been OK.'],
            [$hasAllCharsPassword, true, true, true, 12, 'Tested number, upper required and non-alpha, password check failed while it should have been OK.'],
        ];

        foreach ($okVariations as $variation) {
            $msg = array_pop($variation);
            $errors = call_user_func_array([$this, 'checkPassword'], $variation);
            $this->assertEmpty($errors, $msg);
        }

        /**
         * Errors Expected
         */
        $hasAllCharsPassword = '123abcABC@#$';
        $okVariations = [
            // [$password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
            ['123abcABC', true, true, true, 12, 'Tested password with wrong length, passed OK while it should have not'],
            // The following line is not commented as lowers are not required by random. They are generated, but not required.
            // ['123ABCABC@#$', true, true, true, 12, 'Tested password with no lower, passed OK while it should have not'],
            ['abcabcABC@#$', true, true, true, 12, 'Tested password with no number, passed OK while it should have not'],
            ['123abcabc@#$', true, true, true, 12, 'Tested password with no upper, passed OK while it should have not'],
            ['123abcABCabc', true, true, true, 12, 'Tested password with no non-alpha, passed OK while it should have not'],
        ];

        foreach ($okVariations as $variation) {
            $msg = array_pop($variation);
            $errors = call_user_func_array([$this, 'checkPassword'], $variation);
            $this->assertNotEmpty($errors, $msg);
        }
    }

    /**
     * The plugin settings are the only admin password rules, lowering them must be possible.
     */
    public function testLoweredRequirementsApplyToUsers()
    {
        $this->saveAdminSettings(false, false, false, 4);
        try {
            $user = new \User();
            $this->assertSame('', $user->checkPasswordStrength('passwird'), 'Password matching the lowered plugin settings was refused.');
            $this->assertNotSame('', $user->checkPasswordStrength('abc'), 'Password shorter than the plugin minimum was accepted.');

            $helpText = $user->getPasswordHelpText();
            $this->assertStringContainsString('At least 4 characters long.', $helpText);
            $this->assertStringNotContainsString('upper case', $helpText);
            $this->assertStringNotContainsString('number', $helpText);
        } finally {
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * A minimum password length saved blank uses the default length.
     */
    public function testBlankMinimumSizeUsesDefault()
    {
        $this->saveAdminSettings(false, false, false, '');
        try {
            $user = new \User();
            $shortPassword = str_repeat('a', \PasswordRequirement::DEFAULT_MINIMUM_SIZE - 1);
            $this->assertNotSame('', $user->checkPasswordStrength($shortPassword), 'Password shorter than the default minimum was accepted.');
            $this->assertSame('', $user->checkPasswordStrength($shortPassword . 'a'));
        } finally {
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * The lowercase and maximum length rules are checked, shown in the help text and used for random passwords.
     */
    public function testLowercaseAndMaximumSize()
    {
        $this->saveAdminSettings(false, false, false, 4);
        self::$plugin->saveSettings(['needsLowercase' => '1', 'maximumSize' => 6]);
        try {
            $user = new \User();
            $this->assertSame('', $user->checkPasswordStrength('abcdef'));
            $this->assertNotSame('', $user->checkPasswordStrength('ABCDEF'), 'Password without lowercase character was accepted.');
            $this->assertNotSame('', $user->checkPasswordStrength('abcdefg'), 'Password longer than the maximum was accepted.');

            $helpText = $user->getPasswordHelpText();
            $this->assertStringContainsString('At most 6 characters long.', $helpText);
            $this->assertStringContainsString('At least 1 lower case letter.', $helpText);

            $randomPassword = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword(10);
            $this->assertSame('', $user->checkPasswordStrength($randomPassword), 'Random password does not match the rules.');
        } finally {
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * A maximum password length below the shortest possible password is not saved.
     */
    public function testMaximumSizeBelowMinimumIsRefused()
    {
        $this->saveAdminSettings(true, true, false, 8);
        try {
            $this->assertFalse(self::$plugin->saveSettings(['maximumSize' => 7]));
            $this->assertFalse(self::$plugin->saveSettings(['minimumSize' => 0, 'maximumSize' => 2]), 'Maximum below the required characters was saved.');
            $this->assertNull(self::$plugin->saveSettings(['minimumSize' => 0, 'maximumSize' => 3]));
            $this->assertFalse(self::$plugin->saveSettings(['minimumSize' => 4]), 'Minimum above the saved maximum was saved.');
            $settings = self::$plugin->getPluginSettings();
            $this->assertEquals(3, $settings['maximumSize']['current']);
            $this->assertEquals(0, $settings['minimumSize']['current']);
        } finally {
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * An empty password is refused, even when the plugin settings allow any password.
     */
    public function testEmptyPasswordIsRefused()
    {
        $this->saveAdminSettings(false, false, false, 0);
        try {
            $user = new \User();
            $this->assertNotSame('', $user->checkPasswordStrength(''));
            $this->assertSame('', $user->checkPasswordStrength('a'));
        } finally {
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * DB update 721 keeps the former core rules where they are stricter than the plugin settings.
     */
    public function testUpdateCopiesFormerCoreRules()
    {
        $formerRules = App()->getConfig('passwordValidationRules');
        try {
            // Without passwordValidationRules in config.php, the former config-defaults.php rules applied
            $this->saveAdminSettings(false, false, false, 0);
            App()->setConfig('passwordValidationRules', null);
            (new \LimeSurvey\Helpers\Update\Update_721(App()->db, []))->up();
            $settings = self::$plugin->getPluginSettings();
            $this->assertEquals(8, $settings['minimumSize']['current']);
            $this->assertEquals(1, $settings['needsUppercase']['current']);
            $this->assertEquals(1, $settings['needsNumber']['current']);
            $this->assertEmpty($settings['needsNonAlphanumeric']['current']);

            $this->saveAdminSettings(false, false, false, '');
            App()->setConfig('passwordValidationRules', ['min' => 16, 'max' => 64, 'lower' => 1, 'symbol' => 1]);
            (new \LimeSurvey\Helpers\Update\Update_721(App()->db, []))->up();
            $settings = self::$plugin->getPluginSettings();
            $this->assertEquals(16, $settings['minimumSize']['current']);
            $this->assertEquals(64, $settings['maximumSize']['current']);
            $this->assertEquals(1, $settings['needsLowercase']['current']);
            $this->assertEquals(1, $settings['needsNonAlphanumeric']['current']);
            $this->assertEmpty($settings['needsUppercase']['current']);
        } finally {
            App()->setConfig('passwordValidationRules', $formerRules);
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * DB update 721 replaces the settings of an inactive plugin by the former core rules, the only ones that applied.
     */
    public function testUpdateReplacesSettingsOfInactivePlugin()
    {
        $formerRules = App()->getConfig('passwordValidationRules');
        try {
            $this->saveAdminSettings(false, false, true, '');
            \Plugin::model()->updateAll(['active' => 0], 'name = :name', [':name' => self::$pluginName]);
            App()->setConfig('passwordValidationRules', null);
            (new \LimeSurvey\Helpers\Update\Update_721(App()->db, []))->up();
            $settings = self::$plugin->getPluginSettings();
            $this->assertEquals(8, $settings['minimumSize']['current']);
            $this->assertEquals(1, $settings['needsUppercase']['current']);
            $this->assertEquals(1, $settings['needsNumber']['current']);
            $this->assertEmpty($settings['needsNonAlphanumeric']['current']);
            $this->assertEmpty($settings['needsLowercase']['current']);
            $this->assertEmpty($settings['maximumSize']['current']);
            $this->assertEquals(1, \Plugin::model()->findByAttributes(['name' => self::$pluginName])->active);
        } finally {
            App()->setConfig('passwordValidationRules', $formerRules);
            self::installAndActivatePlugin(self::$pluginName);
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * DB update 721 drops a maximum password length that no password could match.
     */
    public function testUpdateDropsImpossibleMaximumSize()
    {
        $formerRules = App()->getConfig('passwordValidationRules');
        try {
            // Active plugin with the default minimum of 12, the former maximum of 10 is below it
            $this->saveAdminSettings(true, true, false, '');
            App()->setConfig('passwordValidationRules', ['min' => 8, 'max' => 10]);
            (new \LimeSurvey\Helpers\Update\Update_721(App()->db, []))->up();
            $settings = self::$plugin->getPluginSettings();
            $this->assertEmpty($settings['maximumSize']['current']);

            // Inactive plugin, the former rules themselves conflict
            \Plugin::model()->updateAll(['active' => 0], 'name = :name', [':name' => self::$pluginName]);
            App()->setConfig('passwordValidationRules', ['min' => 16, 'max' => 10]);
            (new \LimeSurvey\Helpers\Update\Update_721(App()->db, []))->up();
            $settings = self::$plugin->getPluginSettings();
            $this->assertEquals(16, $settings['minimumSize']['current']);
            $this->assertEmpty($settings['maximumSize']['current']);
        } finally {
            App()->setConfig('passwordValidationRules', $formerRules);
            self::installAndActivatePlugin(self::$pluginName);
            $this->restoreDefaultAdminSettings();
        }
    }

    /**
     * A random password already created by another plugin is kept.
     */
    public function testRandomPasswordOfOtherPluginIsKept()
    {
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'createRandomPassword', [
            'targetSize' => 8,
            'password' => 'fromOtherPlugin',
        ]);
        $this->assertSame('fromOtherPlugin', $oEvent->get('password'));
    }

    /**
     * The plugin can't be deactivated.
     */
    public function testCanNotBeDeactivated()
    {
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'beforeDeactivate', []);
        $this->assertFalse($oEvent->get('success', true));
    }

    /**
     * Without the plugin, core checks a minimum length and creates long enough random passwords.
     */
    public function testFallbackWithoutPlugin()
    {
        $pluginManager = App()->getPluginManager();
        $events = ['checkPasswordRequirement', 'getPasswordRequirements', 'createRandomPassword'];
        foreach ($events as $event) {
            $pluginManager->unsubscribe(self::$plugin, $event);
        }
        try {
            $minLength = \LimeSurvey\Models\Services\PasswordManagement::FALLBACK_MIN_PASSWORD_LENGTH;
            $user = new \User();
            $this->assertNotSame('', $user->checkPasswordStrength(str_repeat('a', $minLength - 1)));
            $this->assertSame('', $user->checkPasswordStrength(str_repeat('a', $minLength)));
            $this->assertStringContainsString("At least {$minLength} characters long.", $user->getPasswordHelpText());
            $randomPassword = \LimeSurvey\Models\Services\PasswordManagement::getRandomPassword();
            $this->assertGreaterThanOrEqual($minLength, strlen((string) $randomPassword));
        } finally {
            foreach ($events as $event) {
                $pluginManager->subscribe(self::$plugin, $event);
            }
        }
    }

    /**
     * Save the plugin settings for administration passwords
     * @param bool $needsNumber
     * @param bool $needsUppercase
     * @param bool $needsNonAlphanumeric
     * @param int|string $minimumSize
     * @return void
     */
    private function saveAdminSettings($needsNumber, $needsUppercase, $needsNonAlphanumeric, $minimumSize)
    {
        self::$plugin->saveSettings([
            'needsNumber' => $needsNumber,
            'needsUppercase' => $needsUppercase,
            'needsNonAlphanumeric' => $needsNonAlphanumeric,
            'minimumSize' => $minimumSize,
        ]);
    }

    /**
     * Restore the default plugin settings for administration passwords
     * @return void
     */
    private function restoreDefaultAdminSettings()
    {
        self::$plugin->saveSettings([
            'needsNumber' => \PasswordRequirement::DEFAULT_NEEDS_NUMBER,
            'needsUppercase' => \PasswordRequirement::DEFAULT_NEEDS_UPPERCASE,
            'needsLowercase' => \PasswordRequirement::DEFAULT_NEEDS_LOWERCASE,
            'needsNonAlphanumeric' => \PasswordRequirement::DEFAULT_NEEDS_NON_ALPHANUMERIC,
            'minimumSize' => \PasswordRequirement::DEFAULT_MINIMUM_SIZE,
            'maximumSize' => '',
        ]);
    }
}
