<?php

namespace ls\tests\unit\helpers\remotecontrol;

use ls\tests\TestBaseClass;

class ImportTest extends TestBaseClass
{
    /**
     * Test importing a survey that has an alias.
     */
    public function testCopyASurveyWithAnAlias(): void
    {
        $file = self::$surveysFolder . '/limesurvey_survey_358685_Copy_survey_with_short_url_test.lss';
        
        // Clean up any existing surveys with this alias from previous runs
        \Yii::app()->session['loginID'] = 1;
        $existingSurveys = \SurveyLanguageSetting::model()->findAll(
            'surveyls_alias = ?',
            array('short-url-test')
        );
        foreach ($existingSurveys as $surveyLang) {
            $survey = \Survey::model()->findByPk($surveyLang->surveyls_survey_id);
            if ($survey) {
                $survey->delete();
            }
        }

        try {
            //Import survey
            $result = XMLImportSurvey($file);
            $survey = \Survey::model()->findByPk($result['newsid']);

            //Get alias
            $alias = $survey->getAliasForLanguage();

            $this->assertEmpty($result['importwarnings']);
            $this->assertSame($alias, 'short-url-test');
        } finally {
            //Delete survey
            if (isset($survey) && $survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }

    /**
     * Test copying a survey from another that was previously imported
     * and that has an alias.
     * The new survey will not have an alias.
     */
    public function testCopyASurveyFromOneWithAnAlias(): void
    {
        $file = self::$surveysFolder . '/limesurvey_survey_358685_Copy_survey_with_short_url_test.lss';

        //Import survey
        $result = importSurveyFile($file, false);
        $survey = \Survey::model()->findByPk($result['newsid']);

        //Copy survey
        $copyResult = XMLImportSurvey($file);
        $copySurvey = \Survey::model()->findByPk($copyResult['newsid']);

        //Get aliases
        $alias = $survey->getAliasForLanguage();
        $copyAlias = $copySurvey->getAliasForLanguage();

        $this->assertEmpty($result['importwarnings']);
        $this->assertSame($alias, 'short-url-test');

        $this->assertNull($copyAlias);
        $this->assertSame($copyResult['importwarnings'][0], 'The survey alias for &#039;English&#039; has been cleared because it was already in use by another survey.');

        //Delete surveys
        \Yii::app()->session['loginID'] = 1;
        $survey->delete();
        $copySurvey->delete();
    }
}
