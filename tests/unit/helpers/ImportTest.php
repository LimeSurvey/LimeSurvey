<?php

namespace ls\tests\unit\helpers;

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

    /**
     * Test that values from the imported file which are echoed back inside
     * import warning messages are HTML-encoded, so they cannot be used to
     * inject markup into the import summary page (see #20697).
     */
    public function testImportWarningValuesAreHtmlEncoded(): void
    {
        $file = self::$surveysFolder . '/limesurvey_survey_900697_xss_import_warning_test.lss';

        \Yii::app()->session['loginID'] = 1;

        try {
            $result = importSurveyFile($file, false);
            $survey = \Survey::model()->findByPk($result['newsid']);

            $warning = null;
            foreach ($result['importwarnings'] as $importWarning) {
                if (strpos($importWarning, 'xss_test_marker') !== false) {
                    $warning = $importWarning;
                    break;
                }
            }

            $this->assertNotNull($warning, 'Expected a warning about the unrecognized survey setting.');
            $this->assertStringNotContainsString('<b>', $warning);
            $this->assertStringNotContainsString('"quoted"', $warning);
            $this->assertStringContainsString('&lt;b&gt;', $warning);
            $this->assertStringContainsString('&quot;quoted&quot;', $warning);
            $this->assertStringContainsString('&amp;', $warning);
        } finally {
            if (isset($survey) && $survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }

    /**
     * Data provider for testImportRespectsTranslateLinksOption.
     *
     * Each data set uses its own target survey id: createFieldMap() caches per survey id,
     * so reusing an id within the same process would pick up the previous import's field map.
     *
     * @return array<string,array{bool,int}>
     */
    public function translateLinksProvider(): array
    {
        return [
            'links translated' => [true, 918701],
            'links not translated' => [false, 918702],
        ];
    }

    /**
     * Test that links to the old survey upload folder in group descriptions are only
     * translated on import when the "convert resource links" option is set (see #18700).
     *
     * @dataProvider translateLinksProvider
     * @param bool $translateLinks Value of the "convert resource links" import option
     * @param int $desiredSurveyId Survey id to import to, must differ from the id in the file
     * @return void
     */
    public function testImportRespectsTranslateLinksOption(bool $translateLinks, int $desiredSurveyId): void
    {
        $oldLink = '/upload/surveys/373616/images/test.png';
        $xml = file_get_contents(self::$surveysFolder . '/limesurvey_survey_373616_copySurvey.lss');
        $xml = str_replace(
            '<description/>',
            '<description><![CDATA[<img src="' . $oldLink . '" />]]></description>',
            $xml
        );

        \Yii::app()->session['loginID'] = 1;

        try {
            // Import under a different survey id, otherwise link translation would be a no-op.
            $result = XMLImportSurvey('', $xml, null, $desiredSurveyId, $translateLinks);
            $survey = \Survey::model()->findByPk($result['newsid']);
            $this->assertNotNull($survey);
            $this->assertNotEquals(373616, $survey->sid);

            $groupL10n = \QuestionGroupL10n::model()->find(
                'gid IN (SELECT gid FROM {{groups}} WHERE sid = :sid)',
                [':sid' => $survey->sid]
            );
            $this->assertNotNull($groupL10n);

            if ($translateLinks) {
                $this->assertStringContainsString('/upload/surveys/' . $survey->sid . '/', $groupL10n->description);
            } else {
                $this->assertStringContainsString($oldLink, $groupL10n->description);
            }
        } finally {
            if (isset($survey) && $survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }

    /**
     * Test that importing a question only imports the languages that the question file
     * and the survey have in common, so no orphaned translations are left in the database (see #16908).
     *
     * @return void
     */
    public function testImportQuestionOnlyImportsSurveyLanguages(): void
    {
        \Yii::app()->session['loginID'] = 1;
        \Survey::model()->resetCache();

        try {
            // Target survey uses English (base) and German, the question file has English, German and French
            $result = importSurveyFile(self::$surveysFolder . '/limesurvey_survey_import_question_test.lss', false);
            $survey = \Survey::model()->findByPk($result['newsid']);
            $survey->additional_languages = 'de';
            $this->assertTrue($survey->save());

            $questionResult = XMLImportQuestion(
                self::$surveysFolder . '/limesurvey_question_16908_three_languages.lsq',
                $survey->sid,
                $survey->groups[0]->gid,
                ['autorename' => true, 'translinkfields' => true]
            );
            $this->assertArrayNotHasKey('fatalerror', $questionResult);
            $qid = $questionResult['newqid'];
            $subQuestion = \Question::model()->findByAttributes(['parent_qid' => $qid]);
            $this->assertNotNull($subQuestion);
            $answer = \Answer::model()->findByAttributes(['qid' => $qid]);
            $this->assertNotNull($answer);
            $defaultValue = \DefaultValue::model()->findByAttributes(['qid' => $qid]);
            $this->assertNotNull($defaultValue);

            $languagesOf = function (array $models): array {
                $languages = array_map(function ($model) {
                    return $model->language;
                }, $models);
                sort($languages);
                return $languages;
            };
            $expected = ['de', 'en'];

            $this->assertSame($expected, $languagesOf(\QuestionL10n::model()->findAllByAttributes(['qid' => $qid])), 'Question texts');
            $this->assertSame($expected, $languagesOf(\QuestionL10n::model()->findAllByAttributes(['qid' => $subQuestion->qid])), 'Subquestion texts');
            $this->assertSame($expected, $languagesOf(\AnswerL10n::model()->findAllByAttributes(['aid' => $answer->aid])), 'Answer texts');
            $this->assertSame($expected, $languagesOf(\DefaultValueL10n::model()->findAllByAttributes(['dvid' => $defaultValue->dvid])), 'Default values');
            // QuestionAttribute::findAll() indexes the result by attribute name, so query the languages directly
            $attributeLanguages = \Yii::app()->db->createCommand()
                ->select('language')
                ->from('{{question_attributes}}')
                ->where('qid = :qid AND attribute = :attribute', [':qid' => $qid, ':attribute' => 'em_validation_q_tip'])
                ->order('language')
                ->queryColumn();
            $this->assertSame($expected, $attributeLanguages, 'Translated question attributes');
            // Attributes without a language are still imported
            $this->assertNotNull(\QuestionAttribute::model()->findByAttributes(['qid' => $qid, 'attribute' => 'hidden']));
        } finally {
            if (isset($survey) && $survey) {
                \Yii::app()->session['loginID'] = 1;
                $survey->delete();
            }
        }
    }
}
