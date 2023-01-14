<?php

namespace ls\tests;

class PassordRequirementTest extends TestBaseClass
{

    protected static $plugin;

    protected static $settings = [];

    private static $pluginName = 'PasswordRequirement';
   
    /**
     * @inheritdoc
     * Activate needed plugins
     * Import survey in tests/surveys/.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Get a handle to the plugin being testes
        self::$plugin = App()->getPluginManager()->loadPlugin(self::$pluginName, $plugin->id);
        
        // Activate it, if not already
        self::installAndActivatePlugin(self::$pluginName);
    }

    private function createRandomPassword($needsNumber, $needsUppercase, $needsNonAlphanumeric, $length)
    {
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'createRandomPassword', [
            'needsNumber' => $needsNumber,
            'needsUppercase' => $needsUppercase,
            'needsNonAlphanumeric' => $needsNonAlphanumeric,
            'targetSize' => $length,
        ]);
        
        return $oEvent->get('password');
    }

    private function checkPassword($password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $length)
    {
        $oEvent = $this->dispatchPluginEvent(self::$pluginName, 'checkPasswordRequirement', [
            'password' => $password,
            'needsNumber' => $needsNumber,
            'needsUppercase' => $needsUppercase,
            'needsNonAlphanumeric' => $needsNonAlphanumeric,
            'minimumSize' => $length,
        ]);

        return $oEvent->get('passwordErrors');
    }

    public function testGetRandomString()
    {
        // Init character sets
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $numeric_chars = '0123456789';
        $uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonAlpha_chars = '-=!@#$%&*_+,.?;:';

        /**
         * No Errors Expected
         */
        $okVariations = [
            // [$needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
            [false, false, false, 8, 'Created random password, with no requirements.'],
            [true, false, false, 12, 'Created random password, only number required.'],
            [false, true, false, 12, 'Created random password, only upper required.'],
            [true, false, true, 12, 'Created random password, only non alpha-required.'],
            [true, true, false, 12, 'Created random password, only number and upper required.'],
            [true, true, true, 12, 'Created random password, only number, upper required and non-alpha.'],
        ];

        foreach ($okVariations as $variation) {
            $msg = array_pop($variation);
            $password = call_user_func([$this, 'createRandomPassword'], $variation);
            
            // Check password generated
            $this->assertNotEmpty($password, $msg . " Password is empty.");

            // Check length
            $passLen = strlen($password);
            $expLen = $variation[3];
            $this->assertTrue($passLen == $expLen, $msg . " Password has wrong length. Has {$passLen}, while {$expLen} expected.");
    
            // Check lower characters
            $this->assertStringContainsString($password, $chars, $msg . " Password does not have lower chars.");
            
            // Check numbers
            if ($variation[1]) {
                $this->assertStringContainsString($password, $numeric_chars, $msg . " Password does not have numbers.");
            } else {
                $this->assertStringNotContainsString($password, $numeric_chars, $msg . " Password has numbers.");
            }

            // Check upper chars
            if ($variation[1]) {
                $this->assertStringContainsString($password, $uppercase_chars, $msg . " Password does not have upper chars.");
            } else {
                $this->assertStringNotContainsString($password, $uppercase_chars, $msg . " Password has upper chars.");
            }

            // Check non-alpha
            if ($variation[2]) {
                $this->assertStringContainsString($password, $nonAlpha_chars, $msg . " Password does not have non-alpha chars.");
            } else {
                $this->assertStringNotContainsString($password, $nonAlpha_chars, $msg . " Password has non-alpha chars.");
            }
        }
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
            $errors = call_user_func([$this, 'checkPassword'], $variation);
            $this->assertEmpty($errors, $msg);
        }

        /**
         * Errors Expected
         */
        $hasAllCharsPassword = '123abcABC@#$';
        $okVariations = [
            // [$password, $needsNumber, $needsUppercase, $needsNonAlphanumeric, $length, $msg]
            ['123abcABC', true, true, true, 12, 'Tested password with wrong length, passed OK while it should have not'],
            ['123ABCABC@#$', true, true, true, 12, 'Tested password with no lower, passed OK while it should have not'],
            ['abcabcABC@#$', true, true, true, 12, 'Tested password with no number, passed OK while it should have not'],
            ['123abcabc@#$', true, true, true, 12, 'Tested password with no upper, passed OK while it should have not'],
            ['123abcABCabc', true, true, true, 12, 'Tested password with no non-alpha, passed OK while it should have not'],
        ];

        foreach ($okVariations as $variation) {
            $msg = array_pop($variation);
            $errors = call_user_func([$this, 'checkPassword'], $variation);
            $this->assertNotEmpty($errors, $msg);
        }
    }
}
