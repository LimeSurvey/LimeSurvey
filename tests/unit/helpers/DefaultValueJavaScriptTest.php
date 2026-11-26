<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Default values are written to the LEMvarNameAttr JavaScript array of Expression Manager.
 *
 * @see https://bugs.limesurvey.org/view.php?id=20272
 */
class DefaultValueJavaScriptTest extends TestBaseClass
{
    /**
     * Import the survey (one long free text question Q00) and add a default value
     * with single quotes, double quotes and a line feed.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_489_default.lss';
        self::importSurvey($surveyFile);

        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q00']);
        $defaultValue = new \DefaultValue();
        $defaultValue->qid = $question->qid;
        $defaultValue->sqid = 0;
        $defaultValue->scale_id = 0;
        $defaultValue->save();
        $defaultValueL10n = new \DefaultValueL10n();
        $defaultValueL10n->dvid = $defaultValue->dvid;
        $defaultValueL10n->language = 'en';
        $defaultValueL10n->defaultvalue = self::getDefaultValue();
        $defaultValueL10n->save();
    }

    /**
     * The default value used by the test
     *
     * @return string
     */
    private static function getDefaultValue()
    {
        return "{date('Y')}\nl'any \"2026\"";
    }

    /**
     * A single quote in a default value must not end the JavaScript string of the
     * 'default' attribute: the generated attribute must be a JSON string literal.
     */
    public function testDefaultValueIsJavaScriptStringLiteral()
    {
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'Q00']);

        \LimeExpressionManager::SetSurveyId(self::$surveyId);
        $LEM = \LimeExpressionManager::singleton();
        $LEM->setVariableAndTokenMappingsForExpressionManager(self::$surveyId, true);
        $varNameAttr = (new \ReflectionProperty($LEM, 'varNameAttr'))->getValue($LEM);

        $jsVarName = 'javaQ' . $question->qid;
        $this->assertArrayHasKey($jsVarName, $varNameAttr);
        $this->assertMatchesRegularExpression('/\'default\':("(?:[^"\\\\]|\\\\.)*"),\'rowdivid\'/', $varNameAttr[$jsVarName]);
        preg_match('/\'default\':("(?:[^"\\\\]|\\\\.)*"),\'rowdivid\'/', $varNameAttr[$jsVarName], $matches);
        $this->assertStringNotContainsString("'", $matches[1], 'Single quotes in the default value are escaped');
        $this->assertSame(self::getDefaultValue(), json_decode($matches[1]), 'The default value is kept unchanged');
    }
}
