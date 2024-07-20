<?php

namespace ls\tests;

class PassordRequirementTest extends TestBaseClass
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
     * Evalutes if a password matches requirements
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
        $this->assertRegExp('/[' . self::$set_chars . ']/', $password, $msg . " Password does not have lower chars.");
        
        // Check numbers
        if ($variation[0]) {
            $this->assertRegExp('/[' . self::$set_numeric_chars . ']/', $password, $msg . " Password does not have numbers.");
        } else {
            $this->assertNotRegExp('/[' . self::$set_numeric_chars . ']/', $password, $msg . " Password has numbers.");
        }

        // Check upper chars
        if ($variation[1]) {
            $this->assertRegExp('/[' . self::$set_uppercase_chars . ']/', $password, $msg . " Password does not have upper chars.");
        } else {
            $this->assertNotRegExp('/[' . self::$set_uppercase_chars . ']/', $password, $msg . " Password has upper chars.");
        }

        // Check non-alpha
        if ($variation[2]) {
            $this->assertRegExp('/[' . self::$set_nonAlpha_chars . ']/', $password, $msg . " Password does not have non-alpha chars.");
        } else {
            $this->assertNotRegExp('/[' . self::$set_nonAlpha_chars . ']/', $password, $msg . " Password has non-alpha chars.");
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
}
