<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Tests the copy of the translatable question attributes to each survey language in DB update 148.
 */
class UpgradeQuestionAttributes148Test extends TestBaseClass
{
    /**
     * Imports the test survey and loads the DB update helper.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.update.updatedb_helper', true);
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_14467_later_question_relevance.lss');
    }

    /**
     * Translatable attributes without language are copied to each survey language, also with an apostrophe in the
     * value. Other attributes are kept as they are.
     *
     * @return void
     */
    public function testTranslatableAttributesAreCopiedToEachLanguage()
    {
        $db = \Yii::app()->getDb();
        $surveyId = self::$surveyId;
        $qid = (int) $db->createCommand('SELECT MIN(qid) FROM {{questions}} WHERE sid = :sid AND parent_qid = 0')
            ->queryScalar([':sid' => $surveyId]);
        $baseLanguage = $db->createCommand('SELECT language FROM {{surveys}} WHERE sid = :sid')
            ->queryScalar([':sid' => $surveyId]);
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->update('{{surveys}}', ['additional_languages' => ' de  fr '], 'sid = :sid', [':sid' => $surveyId]);
            $db->createCommand()->delete('{{question_attributes}}', 'qid = :qid', [':qid' => $qid]);
            $db->createCommand()->insert('{{question_attributes}}', ['qid' => $qid, 'attribute' => 'prefix', 'value' => "l'an", 'language' => null]);
            $db->createCommand()->insert('{{question_attributes}}', ['qid' => $qid, 'attribute' => 'hidden', 'value' => '1', 'language' => null]);

            upgradeQuestionAttributes148();

            $rows = $db->createCommand('SELECT attribute, value, language FROM {{question_attributes}} WHERE qid = :qid ORDER BY attribute, language')
                ->queryAll(true, [':qid' => $qid]);
            $expectedLanguages = [$baseLanguage, 'de', 'fr'];
            sort($expectedLanguages);
            $expected = [['attribute' => 'hidden', 'value' => '1', 'language' => null]];
            foreach ($expectedLanguages as $language) {
                $expected[] = ['attribute' => 'prefix', 'value' => "l'an", 'language' => $language];
            }
            $this->assertSame($expected, $rows);
        } finally {
            $transaction->rollback();
        }
    }
}
