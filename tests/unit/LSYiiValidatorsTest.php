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
            "{html_entity_decode('&amp;')}" => "&#123;html_entity_decode('&amp;')&#125;",   // Used as a function in an expression, so the expression is disabled.
            // The purifier decodes &#123;/&#125; to real braces, which join() could rebuild into a new expression, so it is disabled.
            "{join(\"&#123;\",'html_entity_decode(\"&amp;amp;\")',\"&#125;\")}" => "&#123;join(\"&#123;\",'html_entity_decode(\"&amp;amp;\")',\"&#125;\")&#125;",
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

        // Testing languageCodeFilter.
        $survey = \Survey::model()->insertNewSurvey(array('language' => 'ko')); // Set language to Korean.

        $this->assertSame('ko', $survey->language, 'The language filter did not return a correctly filtered language string.');

        $survey->language = 'de-easy';
        $survey->save();

        $this->assertSame('de-easy', $survey->language, 'The language filter did not return a correctly filtered language string.');

        $survey->language = 'enǵ';
        $survey->save();

        $this->assertSame('en', $survey->language, 'The language filter did not return a correctly filtered language string.');

        // Testing multiLanguageCodeFilter.
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
     * Testing that the functions which decode entities disable the whole expression, but only as exact function names.
     */
    public function testXssFilterDisablesDecodingFunctions()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            "{htmlspecialchars_decode('&lt;b&gt;')}" => "&#123;htmlspecialchars_decode('&lt;b&gt;')&#125;",
            "{quoted_printable_decode('=3Cb=3E')}" => "&#123;quoted_printable_decode('=3Cb=3E')&#125;",
            "{HTML_Entity_Decode('&lt;b&gt;')}" => "&#123;HTML_Entity_Decode('&lt;b&gt;')&#125;",
            // Not a function name : kept as is, and not collapsed into html_entity_decode
            "{htmlhtml_entity_decode_entity_decode('&lt;b&gt;')}" => "{htmlhtml_entity_decode_entity_decode('&lt;b&gt;')}",
        );

        foreach ($cases as $string => $expected) {
            $filtered = $validator->xssFilter($string);
            $this->assertSame($expected, $filtered, 'Unexpected disabled decoding function. Case: ' . $string);
            $this->assertStringNotContainsString('<b', $this->renderExpressions($filtered), 'Markup created at render time. Case: ' . $string);
        }
    }

    /**
     * Testing that sprintf is kept with a safe literal format, and disabled otherwise.
     */
    public function testXssFilterSprintf()
    {
        $validator = new \LSYii_Validators();

        $safeCases = array(
            "{sprintf('%01.2f', 3.14159)}",
            '{sprintf("%s items", 3)}',
            "{sprintf('%1\$s-%2\$05d', 'a', 7)}",
            "{sprintf('%\\'*10s', 'x')}",
            "{sprintf('100%%')}",
        );
        foreach ($safeCases as $string) {
            $this->assertSame($string, $validator->xssFilter($string), 'Safe sprintf was changed. Case: ' . $string);
            $this->assertCount(0, $validator->getExpressionErrors(), 'Safe sprintf reported an error. Case: ' . $string);
        }

        $unsafeCases = array(
            "{sprintf('%c', 60)}b{sprintf('%c', 62)}" => "&#123;sprintf('%c', 60)&#125;b&#123;sprintf('%c', 62)&#125;",
            "{sprintf('%1\$c', 60)}" => "&#123;sprintf('%1\$c', 60)&#125;",
            "{sprintf('%lc', 60)}" => "&#123;sprintf('%lc', 60)&#125;",
            "{sprintf(\"%'cc\", 60)}" => "&#123;sprintf(\"%'cc\", 60)&#125;",
            "{sprintf(sprintf('%s%s', '%', 'c'), 60)}" => "&#123;sprintf(sprintf('%s%s', '%', 'c'), 60)&#125;",
            "{sprintf(join('%', 'c'), 60)}" => "&#123;sprintf(join('%', 'c'), 60)&#125;",
            "{sprintf('%' + 'c', 60)}" => "&#123;sprintf('%' + 'c', 60)&#125;",
        );
        foreach ($unsafeCases as $string => $expected) {
            $filtered = $validator->xssFilter($string);
            $this->assertSame($expected, $filtered, 'Unsafe sprintf was not disabled. Case: ' . $string);
            $this->assertStringNotContainsString('<', $this->renderExpressions($filtered), 'Markup created at render time. Case: ' . $string);
        }
    }

    /**
     * Testing the sprintf format check.
     */
    public function testIsSafeSprintfFormat()
    {
        foreach (array('', 'text', '%s', '%d', '%05.2f', '%-10s', '%+d', "%'*10s", '%1$s %2$s', '100%%', '%x', '%e') as $format) {
            $this->assertTrue(\LSYii_Validators::isSafeSprintfFormat($format), 'Format should be safe: ' . $format);
        }
        foreach (array('%c', '%5c', '%-c', '%1$c', '%lc', "%'cc", "%'%c", '%*c', '%', 'a%', '%%%c', '%y') as $format) {
            $this->assertFalse(\LSYii_Validators::isSafeSprintfFormat($format), 'Format should be unsafe: ' . $format);
        }
    }

    /**
     * Testing that safe expressions and escaped quotes are kept unchanged.
     */
    public function testXssFilterKeepsSafeExpressions()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            "{'it\\'s'}",
            '{"a \\"quoted\\" word"}',
            "{if(1, 'it\\'s', \"no\")}",
            '{if(1, "Hello " + NAME, "")}',
            "{sprintf('%01.2f', 3.14159)}",
            "{regexMatch('/^[0-9]{5}$/', '12345')}",
            '{regexMatch ( "/^a{2,3}$/" , "aa")}',
        );
        foreach ($cases as $string) {
            $this->assertSame($string, $validator->xssFilter($string), 'A safe expression was changed. Case: ' . $string);
            $this->assertCount(0, $validator->getExpressionErrors(), 'A safe expression reported an error. Case: ' . $string);
        }
        // The regexMatch pattern still works after filtering
        $this->assertSame('1', $this->renderExpressions($validator->xssFilter("{regexMatch('/^[0-9]{5}$/', '12345')}")));
    }

    /**
     * Testing that an expression built inside a quoted string is disabled, so it can not be evaluated again.
     */
    public function testXssFilterDisablesExpressionsInStrings()
    {
        $validator = new \LSYii_Validators();

        // A disabled expression has all its braces encoded, so it renders as inert text (unchanged) at any level
        $cases = array(
            '{if(1, "Hello {NAME}", "")}' => '&#123;if(1, "Hello &#123;NAME&#125;", "")&#125;',
            "{join('{', '1+1', '}')}" => "&#123;join('&#123;', '1+1', '&#125;')&#125;",
        );
        foreach ($cases as $string => $expected) {
            $filtered = $validator->xssFilter($string);
            $this->assertSame($expected, $filtered, 'Expression in string not disabled. Case: ' . $string);
            $this->assertSame($expected, $this->renderExpressions($filtered), 'Disabled expression was still evaluated. Case: ' . $string);
        }
    }

    /**
     * Testing the errors collected when an expression is unsafe.
     */
    public function testXssFilterExpressionErrors()
    {
        $validator = new \LSYii_Validators();

        // String to filter => number of expected errors
        $cases = array(
            '{if(1, "Hello {NAME}", "")}' => 1,
            "{sprintf('%c', 60)}" => 1,
            "{htmlspecialchars_decode('&lt;b&gt;')}" => 1,
            "{sprintf('%c', 60)}{sprintf('%c', 62)}" => 1, // Same error only once
            "{htmlspecialchars_decode(sprintf('%c', 60))}" => 2,
            // Nothing unsafe
            "{sprintf('%01.2f', 3.14159)}" => 0,
            "{regexMatch('/^[0-9]{5}$/', '12345')}" => 0,
            '{if(1, "Hello " + NAME, "")}' => 0,
            // A curly brace inside a string is unsafe, even split over several arguments
            "{join('{', '1+1', '}')}" => 1,
            '{"a { b"}' => 1,
            // A brace outside an expression, or entities, are not an expression built in a string
            "<p>Text {NAME}</p>" => 0,
            "<p>&#123;1+1&#125;</p>" => 0,
        );
        foreach ($cases as $string => $expectedCount) {
            $validator->xssFilter($string);
            $this->assertCount($expectedCount, $validator->getExpressionErrors(), 'Unexpected expression errors. Case: ' . $string);
        }

        // Filtering again what was stored must not report errors
        $validator->xssFilter($validator->xssFilter('{if(1, "Hello {NAME}", "")}'));
        $this->assertCount(0, $validator->getExpressionErrors(), 'Filtering the stored (disabled) value again should not report errors.');
    }

    /**
     * Testing that unsafe expressions are kept when not neutralizing (e.g. to grandfather unchanged content).
     */
    public function testXssFilterKeepUnsafeWithoutNeutralize()
    {
        $validator = new \LSYii_Validators();

        $string = "{sprintf('%c', 60)}";
        $this->assertSame($string, $validator->xssFilter($string, false), 'Unsafe expression should be kept when not neutralizing.');
        $this->assertCount(1, $validator->getExpressionErrors(), 'The error should still be reported when not neutralizing.');
    }

    /**
     * Testing that disabling an expression outside a refusing context is collected for bulk callers (import).
     */
    public function testDisabledExpressionNotices()
    {
        $validator = new \LSYii_Validators();

        \LSYii_Validators::clearDisabledExpressionNotices();
        $validator->xssFilter("{sprintf('%c', 60)}"); // neutralize (default), not refusing
        $this->assertNotEmpty(\LSYii_Validators::getDisabledExpressionNotices(), 'A disabled expression should be collected.');

        \LSYii_Validators::clearDisabledExpressionNotices();
        $this->assertEmpty(\LSYii_Validators::getDisabledExpressionNotices(), 'Notices should be cleared.');

        // A safe expression does not add a notice
        $validator->xssFilter("{sprintf('%s', 'x')}");
        $this->assertEmpty(\LSYii_Validators::getDisabledExpressionNotices(), 'A safe expression should not be collected.');

        // Refusing editors do not add bulk notices (they show a validation error instead)
        \LSYii_Validators::refuseChangedExpressionsDuring(function () use ($validator) {
            $validator->xssFilter("{sprintf('%c', 60)}");
        });
        $this->assertEmpty(\LSYii_Validators::getDisabledExpressionNotices(), 'A refusing editor should not add bulk notices.');
    }

    /**
     * Testing that a modified unsafe expression is refused only when enabled, and unchanged content is grandfathered.
     */
    public function testRefuseChangedExpressions()
    {
        // A form model is not an ActiveRecord, so every attribute counts as modified
        $model = new class extends \CFormModel {
            public $text;
        };
        $validator = new \LSYii_Validators();
        $validator->xssfilter = true;
        $validator->attributes = array('text');

        // Not enabled : the value is disabled but not refused
        $model->text = '{sprintf("%c", 60)}';
        $validator->validate($model);
        $this->assertFalse($model->hasErrors(), 'The value should not be refused when refusing is not enabled.');
        $this->assertSame('&#123;sprintf("%c", 60)&#125;', $model->text, 'The value should be disabled.');

        // Enabled : the value is refused
        $model->clearErrors();
        $model->text = '{sprintf("%c", 60)}';
        \LSYii_Validators::refuseChangedExpressionsDuring(function () use ($validator, $model) {
            $validator->validate($model);
        });
        $this->assertTrue($model->hasErrors('text'), 'The value should be refused when refusing is enabled.');
        $this->assertFalse(\LSYii_Validators::$refuseChangedExpressions, 'Refusing should be disabled again after the callback.');

        // Enabled but safe : not refused
        $model->clearErrors();
        $model->text = '{if(1, "Hello " + NAME, "")}';
        \LSYii_Validators::refuseChangedExpressionsDuring(function () use ($validator, $model) {
            $validator->validate($model);
        });
        $this->assertFalse($model->hasErrors(), 'A safe expression should not be refused.');
    }

    /**
     * Testing that stored unsafe content is grandfathered while unchanged, but refused once the field is modified.
     */
    public function testGrandfatherUnchangedContent()
    {
        \Yii::app()->session['loginID'] = 1;
        $survey = \Survey::model()->insertNewSurvey(array('language' => 'en'));
        $sid = $survey->sid;

        // Act as a regular (XSS filtered) user so the expression filter applies
        $newPassword = createPassword();
        $userName = \Yii::app()->securityManager->generateRandomString(8);
        $userId = (int) \User::insertUser($userName, $newPassword, 'Filtered User', 1, $userName . '@example.org');
        $filterXssTmp = \Yii::app()->getConfig('filterxsshtml');
        \Yii::app()->setConfig('filterxsshtml', true);
        \Yii::app()->session['loginID'] = $userId;

        $sls = \SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $sid, 'surveyls_language' => 'en'));
        if ($sls === null) {
            $sls = new \SurveyLanguageSetting();
            $sls->surveyls_survey_id = $sid;
            $sls->surveyls_language = 'en';
            $sls->surveyls_title = 'Title';
            $sls->save();
        }

        // Store a legacy unsafe value directly in the database, bypassing the filter
        $legacy = '{sprintf("%c",60)}';
        \Yii::app()->db->createCommand()->update(
            \SurveyLanguageSetting::model()->tableName(),
            array('surveyls_welcometext' => $legacy),
            'surveyls_survey_id = :sid AND surveyls_language = :lang',
            array(':sid' => $sid, ':lang' => 'en')
        );

        // Case A : change another field, leave the legacy welcome text untouched -> save allowed, legacy kept
        $slsA = \SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $sid, 'surveyls_language' => 'en'));
        $slsA->surveyls_title = 'A new title';
        $savedA = \LSYii_Validators::refuseChangedExpressionsDuring(function () use ($slsA) {
            return $slsA->save();
        });
        $this->assertTrue($savedA, 'Saving an unrelated field should be allowed: ' . json_encode($slsA->getErrors()));
        $this->assertSame($legacy, $slsA->surveyls_welcometext, 'Unchanged legacy content should be grandfathered.');

        // Case B : modify the legacy welcome text so it stays unsafe -> save refused
        $slsB = \SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $sid, 'surveyls_language' => 'en'));
        $slsB->surveyls_welcometext = $legacy . ' more';
        $savedB = \LSYii_Validators::refuseChangedExpressionsDuring(function () use ($slsB) {
            return $slsB->save();
        });
        $this->assertFalse($savedB, 'Modifying a field to keep an unsafe expression should be refused.');
        $this->assertTrue($slsB->hasErrors('surveyls_welcometext'), 'The refused field should carry the error.');

        // Restore environment
        \Yii::app()->setConfig('filterxsshtml', $filterXssTmp);
        \Yii::app()->session['loginID'] = 1;
        $survey->delete(true);
        $user = \User::model()->findByPk($userId);
        if ($user !== null) {
            $user->delete();
        }
    }

    /**
     * Render the expressions of a string with ExpressionManager, with the 3 recursion levels used when showing a survey.
     *
     * @param string $string
     * @return string
     */
    private function renderExpressions($string)
    {
        $expressionManager = new \ExpressionManager();
        return $expressionManager->sProcessStringContainingExpressions($string, 0, 3);
    }

    /**
     * Testing broken HTML.
     */
    public function testBrokenHtml()
    {
        $validator = new \LSYii_Validators();

        $this->assertSame('<strong>strong </strong>', $validator->xssFilter('<strong>strong <style>'), 'Unexpected filtered broken HTML tags.');
    }
}
