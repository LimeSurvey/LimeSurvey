<?php

namespace ls\tests;

/**
 * Test LSYii_Validators class.
 */
class LSYiiValidatorsTest extends TestBaseClass
{
    private static $cases = array();

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$cases['specialChars'] = array(
            array(
                'string'  => '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;',
                'decoded' => '<script>alert("XSS");</script>'
            ),
            array(
                'string'  => 'one%20%26%20two',
                'decoded' => 'one & two'
            ),
            array(
                'string'  => '&#60;script&#62;alert(1);&#60;/script&#62;',
                'decoded' => '<script>alert(1);</script>'
            ),
            array(
                'string'  => '<p>Espa&#241;ol.</p>',
                'decoded' => '<p>Español.</p>'
            ),
        );

        self::$cases['unsafe'] = array(
            'jav&#x09;ascript:alert(\'XSS\');',
            'javascript:alert(\'XSS\');',
            'JavaSCRIPT:Alert(\'XSS\');',
            "jav&#x09;ascript:alert('XSS');",
            "jav&#x0A;ascript:alert('XSS');",
            "jav&#x0D;ascript:alert('XSS');",
            "java\0script:alert('XSS');",

        );

        self::$cases['safe'] = array(
            'http://example.com',
            'https://example.com',
        );
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * Test filtering of html_entity_decode.
     */
    public function testHtmlEntityDecodeFilter()
    {
        // First, we define the cases to test. Array keys are the strings to filter, and values are the expected result
        $cases = [
            "html_entity_decode('&amp;')" => "html_entity_decode('&amp;')", // Not an expression, so it shouldn't be changed.
            "{html_entity_decode('&amp;')}" => "{('&amp;')}",   // Used as a function in an expression, so it should be removed.
            "{join(\"&#123;\",'html_entity_decode(\"&amp;amp;\")',\"&#125;\")}" => "{join(\"{\",'html_entity_decode(\"&amp;amp;\")',\"}\")}",   // Inside a function but as a string, so it's not removed.
        ];

        $validator = new \LSYii_Validators();

        // Test each case
        foreach ($cases as $string => $expected) {
            $actual = $validator->xssFilter($string);
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * Testing that URL encoded characters and html entities are decoded correctly.
     */
    public function testTreatSpecialChars()
    {
        foreach (self::$cases['specialChars'] as $key => $case) {
            $this->assertSame($case['decoded'], \LSYii_Validators::treatSpecialChars($case['string']), 'Unexpected filtered string. Case key: ' . $key);
        }
    }

    /**
     * Testing that unsafe schemes are detected.
     */
    public function testHasUnsafeScheme()
    {
        foreach (self::$cases['unsafe'] as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $cleanUrl = \LSYii_Validators::removeInvisibleChars($url);
            $this->assertTrue(\LSYii_Validators::hasUnsafeScheme($cleanUrl), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually safe.');
        }

        foreach (self::$cases['safe'] as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $cleanUrl = \LSYii_Validators::removeInvisibleChars($url);
            $this->assertFalse(\LSYii_Validators::hasUnsafeScheme($cleanUrl), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually unsafe.');
        }
    }

    /**
     * Testing that XSS potentially dangerous urls are detected.
     */
    public function testIsXssUrl()
    {
        foreach (self::$cases['unsafe'] as $key => $case) {
            $this->assertTrue(\LSYii_Validators::isXssUrl($case), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually safe.');
        }

        foreach (self::$cases['safe'] as $key => $case) {
            $this->assertFalse(\LSYii_Validators::isXssUrl($case), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually unsafe.');
        }
    }

    /**
     * Testing that invisible characters are removed.
     */
    public function testRemoveInvisibleChars()
    {
        foreach (self::$cases['unsafe'] as $case) {
            $string = \LSYii_Validators::treatSpecialChars($case);
            $result = \LSYii_Validators::removeInvisibleChars($string);
            $this->assertEqualsIgnoringCase('javascript:alert(\'XSS\');', $result, 'Unexpected result, apparently not all invisible chars were removed.');
        }
    }

    /**
     * Testing that the xssfilter attribute will always be
     * false for super admin users even if filterxsshtml is
     * changed.
     */
    public function testXssFilterAttributeForSuperAdmin()
    {
        //Create super admin
        $userName = \Yii::app()->securityManager->generateRandomString(8);

        $userData = array(
            'full_name'  => $userName,
            'users_name' => $userName,
            'email'      => $userName . '@example.org'
        );

        $permissions = array(
            'superadmin' => array(
                'create' => true,
                'read'   => true,
                'update' => true,
                'delete' => true,
                'import' => true,
                'export' => true,
            )
        );

        $user = self::createUserWithPermissions($userData, $permissions);

        //Login as super admin.
        \Yii::app()->session['loginID'] = $user->uid;
        $superAdminValidator = new \LSYii_Validators();

        //Save config state in order to restore it later.
        $filterXssTmp = \Yii::app()->getConfig('filterxsshtml');
        \Yii::app()->setConfig('filterxsshtml', true);

        $this->assertFalse($superAdminValidator->xssfilter, 'The xssfilter attribute should be false for super admins.');

        //Changing filterxsshtml.
        \Yii::app()->setConfig('filterxsshtml', false);
        $newSuperAdminValidator = new \LSYii_Validators();

        $this->assertFalse(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml was just changed to false.');
        $this->assertFalse($newSuperAdminValidator->xssfilter, 'The xssfilter attribute should be false for super admins.');

        //Returning to original values.
        \Yii::app()->setConfig('filterxsshtml', $filterXssTmp);
        \Yii::app()->session['loginID'] = null;

        //Delete user.
        $user->delete();
    }

    /**
     * Testing that the xssfilter attribute varies
     * for regular users depending on filterxsshtml.
     */
    public function testXssFilterAttributeForRegularUsers()
    {
        //Create user.
        $newPassword = createPassword();
        $userName = \Yii::app()->securityManager->generateRandomString(8);
        $userId = \User::insertUser($userName, $newPassword, 'John Doe', 1, $userName . '@example.org');

        //Mocking regular user login.
        \Yii::app()->session['loginID'] = $userId;
        $regularUserValidator = new \LSYii_Validators();

        //Save config state in order to restore it later.
        $filterXssTmp = \Yii::app()->getConfig('filterxsshtml');
        \Yii::app()->setConfig('filterxsshtml', true);

        $this->assertTrue($regularUserValidator->xssfilter, 'The xssfilter attribute should be true for regular users.');

        //Changing filterxsshtml.
        \Yii::app()->setConfig('filterxsshtml', false);
        $newRegularUserValidator = new \LSYii_Validators();

        $this->assertFalse(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml was just changed to false.');
        $this->assertFalse($newRegularUserValidator->xssfilter, 'The xssfilter attribute should be false for regular users with filterxsshtml set to false.');

        //Returning to original values.
        \Yii::app()->setConfig('filterxsshtml', $filterXssTmp);
        \Yii::app()->session['loginID'] = null;

        //Delete user.
        $user = \User::model()->findByPk($userId);
        $user->delete();
    }

    /**
     * Testing that any script or dangerous HTML is removed.
     */
    public function testXssFilterApplied()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            array(
                'string'   => '<script>alert(`Test`)</script>',
                'expected' => ''
            ),
            array(
                'string'   => '{join(\'html_entity_decode("\', \'<script>alert("Test")</script>")\')}',
                'expected' => '{join(\'html_entity_decode("\', \'")\')}'
            ),
            array(
                'string'   => '<title>html_entity_decode("<script>alert("Test")</script>")</title>',
                'expected' => 'html_entity_decode("")'
            ),
            array(
                'string'   => '{join(\'html_entity_decode("\', \'<s\', \'cript>alert("Test")</script>")\')}',
                'expected' => '{join(\'html_entity_decode("\', \'<s></s>\', \'cript&gt;alert("Test")")\')}'
            ),
            array(
                'string'   => '{join(\'html_entity_decode("\', \'<\', \'script>alert("Test")<\', \'/script>")\')',
                'expected' => '{join(\'html_entity_decode("\', \'&lt;\', \'script&gt;alert("Test")&lt;\', \'/script&gt;")\')'
            ),
            array(
                'string'   => '<title>html_entity_decode("<script>alert("Test")</script>123456")</title>',
                'expected' => 'html_entity_decode("123456")'
            ),
            array(
                'string'   => '{join(trim(" < "),"script",">",\'alert("Test")\',trim(" < "),"/script",">")}',
                'expected' => '{join(trim(" &lt; "),"script","&gt;",\'alert("Test")\',trim(" &lt; "),"/script","&gt;")}'
            ),
            array(
                'string'   => '{join("<s", "cript>alert("Test")</script>")}',
                'expected' => '{join("<s></s>", "cript&gt;alert("Test")")}'
            )
        );

        foreach ($cases as $key => $case) {
            $this->assertSame($case['expected'], $validator->xssFilter($case['string']), 'Unexpected filtered dangerous string. Case key: ' . $key);
        }
    }

    /**
     * Testing that safe HTML tags are not removed.
     */
    public function testSafeHtml()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            '<h1>Header</h1>',
            '<p>Paragraph</p>',
            '<strong>Text</strong>',
            '<span>Some text</span>',
        );

        foreach ($cases as $case) {
            $this->assertSame($case, $validator->xssFilter($case), 'Unexpected filtered safe HTML tags.');
        }
    }

    /**
     * Testing the language filters
     * through the Survey model.
     */
    public function testLanguageFilters()
    {
        \Yii::app()->session['loginID'] = 1;

        // Testing languageFilter.
        $survey = \Survey::model()->insertNewSurvey(array('language' => 'ko')); // Set language to Korean.

        $this->assertSame('ko', $survey->language, 'The language filter did not return a correctly filtered language string.');

        $survey->language = 'de-easy';
        $survey->save();

        $this->assertSame('de-easy', $survey->language, 'The language filter did not return a correctly filtered language string.');

        $survey->language = 'enǵ';
        $survey->save();

        $this->assertSame('en', $survey->language, 'The language filter did not return a correctly filtered language string.');

        // Testing multiLanguageFilter.
        $survey->additional_languages = 'es';
        $survey->save();

        $this->assertSame('es', $survey->additional_languages, 'The multi language filter did not return a correctly filtered string.');

        $survey->additional_languages = 'esñ frá';
        $survey->save();

        $this->assertSame('es fr', $survey->additional_languages, 'The multi language filter did not return a correctly filtered string.');

        $survey->additional_languages = 'esñ frá it';
        $survey->save();

        $this->assertSame('es fr it', $survey->additional_languages, 'The multi language filter did not return a correctly filtered string.');

        $survey->additional_languages = 'de-informal de-easy es';
        $survey->save();

        $this->assertSame('de-informal de-easy es', $survey->additional_languages, 'The multi language filter did not return a correctly filtered string.');

        $survey->delete(true);
    }

    /**
     * Test languageFilter with numeric characters (new in 7.0)
     */
    public function testLanguageFilterWithNumbers()
    {
        $validator = new \LSYii_Validators();

        // Test that numbers are now allowed in language codes
        $this->assertSame('zh-Hans', $validator->languageFilter('zh-Hans'), 'Language filter should preserve valid language code.');
        $this->assertSame('sr-Latn', $validator->languageFilter('sr-Latn'), 'Language filter should preserve valid language code with region.');
        
        // Test numeric characters are allowed
        $this->assertSame('test123', $validator->languageFilter('test123'), 'Language filter should allow numeric characters.');
        $this->assertSame('en-US2', $validator->languageFilter('en-US2'), 'Language filter should allow numeric characters in language codes.');
        
        // Test special characters are still filtered
        $this->assertSame('en', $validator->languageFilter('en@#$'), 'Language filter should remove special characters.');
        $this->assertSame('fr-FR', $validator->languageFilter('fr_FR'), 'Language filter should remove underscores.');
        
        // Test empty values
        $this->assertSame('', $validator->languageFilter(''), 'Language filter should return empty string for empty input.');
        $this->assertSame('0', $validator->languageFilter(0), 'Language filter should handle zero.');
        $this->assertSame('', $validator->languageFilter(null), 'Language filter should handle null.');
        
        // Test edge cases
        $this->assertSame('123-456-789', $validator->languageFilter('123-456-789'), 'Language filter should allow numbers and hyphens.');
        $this->assertSame('a1b2c3', $validator->languageFilter('a1!b2@c3#'), 'Language filter should filter special chars but keep numbers.');
    }

    /**
     * Test multiLanguageFilter with numeric characters
     */
    public function testMultiLanguageFilterWithNumbers()
    {
        $validator = new \LSYii_Validators();

        // Test multiple language codes with numbers
        $this->assertSame('en-US de-DE2 fr-FR', $validator->multiLanguageFilter('en-US de-DE2 fr-FR'), 'Multi language filter should preserve numeric characters.');
        
        // Test filtering special characters from multiple codes
        $this->assertSame('en de fr', $validator->multiLanguageFilter('en@# de$% fr^&'), 'Multi language filter should remove special characters from all codes.');
        
        // Test with mixed valid and invalid characters
        $this->assertSame('zh-Hans sr-Latn', $validator->multiLanguageFilter('zh-Hans sr_Latn'), 'Multi language filter should filter underscores but preserve valid codes.');
        
        // Test empty and whitespace handling
        $this->assertSame('', $validator->multiLanguageFilter(''), 'Multi language filter should handle empty string.');
        $this->assertSame('', $validator->multiLanguageFilter('   '), 'Multi language filter should handle whitespace only.');
        $this->assertSame('en fr', $validator->multiLanguageFilter('  en   fr  '), 'Multi language filter should handle extra whitespace.');
        
        // Test with null and non-string values
        $this->assertSame('', $validator->multiLanguageFilter(null), 'Multi language filter should handle null.');
        $this->assertSame('0', $validator->multiLanguageFilter(0), 'Multi language filter should handle zero.');
        
        // Test complex scenarios
        $this->assertSame('en-US1 de-informal2 zh-Hans3', $validator->multiLanguageFilter('en-US1 de_informal2 zh-Hans3'), 'Multi language filter should handle complex codes with numbers.');
    }

    /**
     * Test languageFilter consistency with sanitize_languagecode helper
     */
    public function testLanguageFilterConsistencyWithSanitizeHelper()
    {
        $validator = new \LSYii_Validators();
        
        // Load the helper
        Yii::import('application.helpers.sanitize_helper', true);
        
        // Test that both methods produce consistent results
        $testCases = [
            'en-US',
            'de-DE2',
            'zh-Hans',
            'fr@#$',
            'test_123',
            'a1-b2-c3',
        ];
        
        foreach ($testCases as $testCase) {
            $validatorResult = $validator->languageFilter($testCase);
            $helperResult = sanitize_languagecode($testCase);
            $this->assertSame($helperResult, $validatorResult, "languageFilter and sanitize_languagecode should produce same result for: $testCase");
        }
    }

    /**
     * Test edge cases for languageFilter
     */
    public function testLanguageFilterEdgeCases()
    {
        $validator = new \LSYii_Validators();
        
        // Test with only special characters
        $this->assertSame('', $validator->languageFilter('!@#$%^&*()'), 'Language filter should return empty for only special chars.');
        
        // Test with mixed case
        $this->assertSame('EN-us', $validator->languageFilter('EN-us'), 'Language filter should preserve case.');
        $this->assertSame('MixedCase123', $validator->languageFilter('MixedCase123!@#'), 'Language filter should preserve mixed case and numbers.');
        
        // Test with long strings
        $longString = str_repeat('a', 100) . '123';
        $this->assertSame($longString, $validator->languageFilter($longString), 'Language filter should handle long strings.');
        
        // Test with Unicode characters that should be filtered
        $this->assertSame('test', $validator->languageFilter('tëst'), 'Language filter should remove accented characters.');
    }

    /**
     * Test multiLanguageFilter edge cases
     */
    public function testMultiLanguageFilterEdgeCases()
    {
        $validator = new \LSYii_Validators();
        
        // Test with single language code
        $this->assertSame('en', $validator->multiLanguageFilter('en'), 'Multi language filter should handle single code.');
        
        // Test with many spaces between codes
        $this->assertSame('en de fr', $validator->multiLanguageFilter('en     de     fr'), 'Multi language filter should normalize spaces.');
        
        // Test filtering results in empty codes
        $result = $validator->multiLanguageFilter('!@# $%^ &*(');
        $this->assertTrue(strlen(trim($result)) === 0, 'Multi language filter should handle all-invalid input.');
        
        // Test with duplicate codes (should be preserved as multiLanguageFilter doesn't deduplicate)
        $this->assertSame('en en de', $validator->multiLanguageFilter('en en de'), 'Multi language filter should preserve duplicates.');
    }
}