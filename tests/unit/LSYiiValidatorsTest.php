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
     * Testing broken HTML.
     */
    public function testBrokenHtml()
    {
        $validator = new \LSYii_Validators();

        $this->assertSame('<strong>strong </strong>', $validator->xssFilter('<strong>strong <style>'), 'Unexpected filtered broken HTML tags.');
    }

    /**
     * Testing languageFilter with numeric characters (new behavior in updated code)
     */
    public function testLanguageFilterWithNumericCharacters()
    {
        $validator = new \LSYii_Validators();
        
        // Test that numbers are now allowed (new behavior)
        $this->assertSame('en123', $validator->languageFilter('en123'), 'Language filter should allow numbers');
        $this->assertSame('zh-Hans2', $validator->languageFilter('zh-Hans2'), 'Language filter should allow numbers with hyphens');
        $this->assertSame('test123-456', $validator->languageFilter('test123-456'), 'Language filter should allow multiple numbers');
        
        // Test edge cases with only numbers
        $this->assertSame('123', $validator->languageFilter('123'), 'Language filter should allow pure numeric strings');
        $this->assertSame('0', $validator->languageFilter('0'), 'Language filter should allow zero');
    }

    /**
     * Testing languageFilter with various invalid characters
     */
    public function testLanguageFilterRemovesInvalidCharacters()
    {
        $validator = new \LSYii_Validators();
        
        // Test removal of special characters
        $this->assertSame('en', $validator->languageFilter('en!'), 'Should remove exclamation marks');
        $this->assertSame('en', $validator->languageFilter('en@'), 'Should remove at symbols');
        $this->assertSame('en', $validator->languageFilter('en#'), 'Should remove hash symbols');
        $this->assertSame('en', $validator->languageFilter('en$'), 'Should remove dollar signs');
        $this->assertSame('en', $validator->languageFilter('en%'), 'Should remove percent signs');
        $this->assertSame('en', $validator->languageFilter('en^'), 'Should remove caret symbols');
        $this->assertSame('en', $validator->languageFilter('en&'), 'Should remove ampersands');
        $this->assertSame('en', $validator->languageFilter('en*'), 'Should remove asterisks');
        $this->assertSame('en', $validator->languageFilter('en('), 'Should remove opening parenthesis');
        $this->assertSame('en', $validator->languageFilter('en)'), 'Should remove closing parenthesis');
        $this->assertSame('en', $validator->languageFilter('en_'), 'Should remove underscores');
        $this->assertSame('en', $validator->languageFilter('en='), 'Should remove equals signs');
        $this->assertSame('en', $validator->languageFilter('en+'), 'Should remove plus signs');
        $this->assertSame('en', $validator->languageFilter('en['), 'Should remove opening brackets');
        $this->assertSame('en', $validator->languageFilter('en]'), 'Should remove closing brackets');
        $this->assertSame('en', $validator->languageFilter('en{'), 'Should remove opening braces');
        $this->assertSame('en', $validator->languageFilter('en}'), 'Should remove closing braces');
        $this->assertSame('en', $validator->languageFilter('en|'), 'Should remove pipe symbols');
        $this->assertSame('en', $validator->languageFilter('en\\'), 'Should remove backslashes');
        $this->assertSame('en', $validator->languageFilter('en:'), 'Should remove colons');
        $this->assertSame('en', $validator->languageFilter('en;'), 'Should remove semicolons');
        $this->assertSame('en', $validator->languageFilter('en"'), 'Should remove double quotes');
        $this->assertSame('en', $validator->languageFilter("en'"), 'Should remove single quotes');
        $this->assertSame('en', $validator->languageFilter('en<'), 'Should remove less than signs');
        $this->assertSame('en', $validator->languageFilter('en>'), 'Should remove greater than signs');
        $this->assertSame('en', $validator->languageFilter('en,'), 'Should remove commas');
        $this->assertSame('en', $validator->languageFilter('en.'), 'Should remove periods');
        $this->assertSame('en', $validator->languageFilter('en/'), 'Should remove forward slashes');
        $this->assertSame('en', $validator->languageFilter('en?'), 'Should remove question marks');
    }

    /**
     * Testing languageFilter with empty and null values
     */
    public function testLanguageFilterWithEmptyValues()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('', $validator->languageFilter(''), 'Empty string should return empty string');
        $this->assertSame('0', $validator->languageFilter(0), 'Zero should be converted to string "0"');
        $this->assertSame('', $validator->languageFilter(null), 'Null should return empty string');
        $this->assertSame('', $validator->languageFilter(false), 'False should return empty string');
    }

    /**
     * Testing languageFilter with whitespace
     */
    public function testLanguageFilterWithWhitespace()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en', $validator->languageFilter('en '), 'Should remove trailing spaces');
        $this->assertSame('en', $validator->languageFilter(' en'), 'Should remove leading spaces');
        $this->assertSame('en', $validator->languageFilter(' en '), 'Should remove leading and trailing spaces');
        $this->assertSame('enus', $validator->languageFilter('en us'), 'Should remove internal spaces');
        $this->assertSame('en', $validator->languageFilter("en\t"), 'Should remove tabs');
        $this->assertSame('en', $validator->languageFilter("en\n"), 'Should remove newlines');
        $this->assertSame('en', $validator->languageFilter("en\r"), 'Should remove carriage returns');
    }

    /**
     * Testing languageFilter with mixed case
     */
    public function testLanguageFilterPreservesCasing()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('EN', $validator->languageFilter('EN'), 'Should preserve uppercase');
        $this->assertSame('en', $validator->languageFilter('en'), 'Should preserve lowercase');
        $this->assertSame('En', $validator->languageFilter('En'), 'Should preserve mixed case');
        $this->assertSame('eN', $validator->languageFilter('eN'), 'Should preserve mixed case');
        $this->assertSame('en-US', $validator->languageFilter('en-US'), 'Should preserve case in compound codes');
    }

    /**
     * Testing languageFilter with valid language codes including numbers
     */
    public function testLanguageFilterWithValidCodesIncludingNumbers()
    {
        $validator = new \LSYii_Validators();
        
        // Standard language codes
        $this->assertSame('en', $validator->languageFilter('en'), 'Standard English code');
        $this->assertSame('fr', $validator->languageFilter('fr'), 'Standard French code');
        $this->assertSame('de', $validator->languageFilter('de'), 'Standard German code');
        $this->assertSame('es', $validator->languageFilter('es'), 'Standard Spanish code');
        $this->assertSame('it', $validator->languageFilter('it'), 'Standard Italian code');
        $this->assertSame('pt', $validator->languageFilter('pt'), 'Standard Portuguese code');
        $this->assertSame('ja', $validator->languageFilter('ja'), 'Standard Japanese code');
        $this->assertSame('ko', $validator->languageFilter('ko'), 'Standard Korean code');
        $this->assertSame('zh', $validator->languageFilter('zh'), 'Standard Chinese code');
        
        // Language codes with region
        $this->assertSame('en-US', $validator->languageFilter('en-US'), 'English US');
        $this->assertSame('en-GB', $validator->languageFilter('en-GB'), 'English GB');
        $this->assertSame('fr-FR', $validator->languageFilter('fr-FR'), 'French France');
        $this->assertSame('de-DE', $validator->languageFilter('de-DE'), 'German Germany');
        $this->assertSame('pt-BR', $validator->languageFilter('pt-BR'), 'Portuguese Brazil');
        $this->assertSame('zh-Hans', $validator->languageFilter('zh-Hans'), 'Chinese Simplified');
        $this->assertSame('zh-Hant', $validator->languageFilter('zh-Hant'), 'Chinese Traditional');
        
        // Custom codes with numbers (new behavior)
        $this->assertSame('custom1', $validator->languageFilter('custom1'), 'Custom code with number');
        $this->assertSame('lang2-region3', $validator->languageFilter('lang2-region3'), 'Custom code with numbers and hyphen');
    }

    /**
     * Testing languageFilter with XSS attempts
     */
    public function testLanguageFilterBlocksXSSAttempts()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('scriptalertXSSscript', $validator->languageFilter('<script>alert("XSS")</script>'), 'Should remove script tags');
        $this->assertSame('imgsrcx', $validator->languageFilter('<img src=x>'), 'Should remove img tags');
        $this->assertSame('javascriptalert1', $validator->languageFilter('javascript:alert(1)'), 'Should remove javascript protocol');
        $this->assertSame('onerroralert1', $validator->languageFilter('onerror=alert(1)'), 'Should remove event handlers');
    }

    /**
     * Testing multiLanguageFilter with numeric characters
     */
    public function testMultiLanguageFilterWithNumericCharacters()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en1 fr2', $validator->multiLanguageFilter('en1 fr2'), 'Should allow numbers in multi-language codes');
        $this->assertSame('lang1 lang2 lang3', $validator->multiLanguageFilter('lang1 lang2 lang3'), 'Should allow multiple numbered languages');
        $this->assertSame('en-US1 fr-FR2', $validator->multiLanguageFilter('en-US1 fr-FR2'), 'Should allow numbers in compound codes');
    }

    /**
     * Testing multiLanguageFilter removes invalid characters
     */
    public function testMultiLanguageFilterRemovesInvalidCharacters()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en fr', $validator->multiLanguageFilter('en! fr@'), 'Should remove special characters');
        $this->assertSame('en fr de', $validator->multiLanguageFilter('en# fr$ de%'), 'Should remove multiple special characters');
        $this->assertSame('en-US fr-FR', $validator->multiLanguageFilter('en-US! fr-FR@'), 'Should remove special characters from compound codes');
    }

    /**
     * Testing multiLanguageFilter with empty values
     */
    public function testMultiLanguageFilterWithEmptyValues()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('', $validator->multiLanguageFilter(''), 'Empty string should return empty string');
        $this->assertSame('0', $validator->multiLanguageFilter(0), 'Zero should be converted to string "0"');
        $this->assertSame('', $validator->multiLanguageFilter(null), 'Null should return empty string');
        $this->assertSame('', $validator->multiLanguageFilter(false), 'False should return empty string');
    }

    /**
     * Testing multiLanguageFilter with extra spaces
     */
    public function testMultiLanguageFilterHandlesExtraSpaces()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en fr', $validator->multiLanguageFilter('en  fr'), 'Should handle double spaces');
        $this->assertSame('en fr de', $validator->multiLanguageFilter('en   fr   de'), 'Should handle multiple spaces');
        $this->assertSame('en fr', $validator->multiLanguageFilter(' en fr '), 'Should trim leading and trailing spaces');
        $this->assertSame('en fr de', $validator->multiLanguageFilter('en fr de'), 'Should handle single spaces normally');
    }

    /**
     * Testing multiLanguageFilter with duplicate values
     */
    public function testMultiLanguageFilterWithDuplicates()
    {
        $validator = new \LSYii_Validators();
        
        // Note: The implementation doesn't remove duplicates, it processes all values
        // This test documents the actual behavior
        $result = $validator->multiLanguageFilter('en en fr');
        $this->assertStringContainsString('en', $result, 'Should process duplicates');
        $this->assertStringContainsString('fr', $result, 'Should process all values');
    }

    /**
     * Testing multiLanguageFilter with single language
     */
    public function testMultiLanguageFilterWithSingleLanguage()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en', $validator->multiLanguageFilter('en'), 'Should handle single language');
        $this->assertSame('en-US', $validator->multiLanguageFilter('en-US'), 'Should handle single compound language');
        $this->assertSame('custom1', $validator->multiLanguageFilter('custom1'), 'Should handle single custom language with number');
    }

    /**
     * Testing multiLanguageFilter with many languages
     */
    public function testMultiLanguageFilterWithManyLanguages()
    {
        $validator = new \LSYii_Validators();
        
        $input = 'en fr de es it pt ja ko zh ar ru';
        $result = $validator->multiLanguageFilter($input);
        $languages = explode(' ', $result);
        
        $this->assertCount(11, $languages, 'Should handle many languages');
        $this->assertContains('en', $languages, 'Should contain en');
        $this->assertContains('fr', $languages, 'Should contain fr');
        $this->assertContains('de', $languages, 'Should contain de');
        $this->assertContains('ru', $languages, 'Should contain ru');
    }

    /**
     * Testing multiLanguageFilter with mixed valid and invalid codes
     */
    public function testMultiLanguageFilterWithMixedValidInvalidCodes()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en fr de', $validator->multiLanguageFilter('en! fr@ de#'), 'Should filter invalid chars from each code');
        $this->assertSame('en-US fr-FR de-DE', $validator->multiLanguageFilter('en-US! fr-FR@ de-DE#'), 'Should filter invalid chars from compound codes');
    }

    /**
     * Testing that languageFilter works correctly when called directly
     */
    public function testLanguageFilterDirectCall()
    {
        $validator = new \LSYii_Validators();
        
        // Test the method can be called directly (not just through validation)
        $result1 = $validator->languageFilter('en-US');
        $result2 = $validator->languageFilter('fr123');
        $result3 = $validator->languageFilter('invalid@code!');
        
        $this->assertSame('en-US', $result1, 'Direct call should work for valid code');
        $this->assertSame('fr123', $result2, 'Direct call should work with numbers');
        $this->assertSame('invalidcode', $result3, 'Direct call should filter invalid characters');
    }

    /**
     * Testing that multiLanguageFilter works correctly when called directly
     */
    public function testMultiLanguageFilterDirectCall()
    {
        $validator = new \LSYii_Validators();
        
        // Test the method can be called directly (not just through validation)
        $result1 = $validator->multiLanguageFilter('en fr de');
        $result2 = $validator->multiLanguageFilter('en1 fr2 de3');
        $result3 = $validator->multiLanguageFilter('en! fr@ de#');
        
        $this->assertSame('en fr de', $result1, 'Direct call should work for valid codes');
        $this->assertSame('en1 fr2 de3', $result2, 'Direct call should work with numbers');
        $this->assertSame('en fr de', $result3, 'Direct call should filter invalid characters');
    }

    /**
     * Testing languageFilter with Unicode characters
     */
    public function testLanguageFilterWithUnicodeCharacters()
    {
        $validator = new \LSYii_Validators();
        
        // Unicode characters should be removed
        $this->assertSame('en', $validator->languageFilter('enñ'), 'Should remove Spanish n with tilde');
        $this->assertSame('fr', $validator->languageFilter('frá'), 'Should remove accented a');
        $this->assertSame('de', $validator->languageFilter('deü'), 'Should remove German umlaut');
        $this->assertSame('es', $validator->languageFilter('esñ'), 'Should remove special characters');
        $this->assertSame('', $validator->languageFilter('日本語'), 'Should remove Japanese characters');
        $this->assertSame('', $validator->languageFilter('中文'), 'Should remove Chinese characters');
        $this->assertSame('', $validator->languageFilter('한국어'), 'Should remove Korean characters');
        $this->assertSame('', $validator->languageFilter('العربية'), 'Should remove Arabic characters');
        $this->assertSame('', $validator->languageFilter('Русский'), 'Should remove Cyrillic characters');
    }

    /**
     * Testing multiLanguageFilter with Unicode characters
     */
    public function testMultiLanguageFilterWithUnicodeCharacters()
    {
        $validator = new \LSYii_Validators();
        
        $this->assertSame('en fr', $validator->multiLanguageFilter('enñ frá'), 'Should remove Unicode from multi-language string');
        $this->assertSame('de es it', $validator->multiLanguageFilter('deü esñ itò'), 'Should remove multiple Unicode characters');
    }

    /**
     * Testing languageFilter with very long strings
     */
    public function testLanguageFilterWithLongStrings()
    {
        $validator = new \LSYii_Validators();
        
        $longString = str_repeat('a', 1000);
        $result = $validator->languageFilter($longString);
        $this->assertSame($longString, $result, 'Should handle very long valid strings');
        
        $longInvalidString = str_repeat('!', 1000);
        $result2 = $validator->languageFilter($longInvalidString);
        $this->assertSame('', $result2, 'Should remove all characters from long invalid string');
        
        $mixedLongString = str_repeat('a!', 500);
        $result3 = $validator->languageFilter($mixedLongString);
        $this->assertSame(str_repeat('a', 500), $result3, 'Should filter invalid chars from long mixed string');
    }

    /**
     * Testing multiLanguageFilter with many languages
     */
    public function testMultiLanguageFilterWithManyLanguagesList()
    {
        $validator = new \LSYii_Validators();
        
        $manyLanguages = implode(' ', array_fill(0, 100, 'en'));
        $result = $validator->multiLanguageFilter($manyLanguages);
        $count = count(explode(' ', $result));
        $this->assertSame(100, $count, 'Should handle 100 language codes');
    }
}