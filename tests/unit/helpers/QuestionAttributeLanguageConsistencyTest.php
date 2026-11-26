<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use Yii;

/**
 * Tests that translated question attributes follow the survey languages (see #20178):
 * They are copied from the base language when a language is added, removed with the language and skipped on import
 * when the survey does not use their language.
 */
class QuestionAttributeLanguageConsistencyTest extends TestBaseClass
{
    /**
     * Imports the English only survey, its file still has empty German and French question attributes.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Yii::import('application.helpers.admin.import_helper', true);

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_20178_stale_attribute_languages.lss');
    }

    /**
     * Returns the value of a question attribute in a language, null if there is no row.
     *
     * @param integer $qid
     * @param string $attribute
     * @param string $language
     * @return string|null
     */
    private function getAttributeValue($qid, $attribute, $language)
    {
        $value = Yii::app()->db->createCommand()
            ->select('value')
            ->from('{{question_attributes}}')
            ->where('qid = :qid AND attribute = :attribute AND language = :language', [
                ':qid' => $qid,
                ':attribute' => $attribute,
                ':language' => $language,
            ])
            ->queryScalar();
        return $value === false ? null : (string) $value;
    }

    /**
     * Returns the number of question attribute rows of a question in a language.
     *
     * @param integer $qid
     * @param string $language
     * @return integer
     */
    private function countAttributes($qid, $language)
    {
        return (int) Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{question_attributes}}')
            ->where('qid = :qid AND language = :language', [':qid' => $qid, ':language' => $language])
            ->queryScalar();
    }

    /**
     * Returns the number of question attribute rows of a question without a language.
     *
     * @param integer $qid
     * @return integer
     */
    private function countAttributesWithoutLanguage($qid)
    {
        return (int) Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{question_attributes}}')
            ->where("qid = :qid AND (language IS NULL OR language = '')", [':qid' => $qid])
            ->queryScalar();
    }

    /**
     * Adding a language copies the translated attributes of the base language, removing it deletes them again.
     */
    public function testAttributesFollowSurveyLanguages()
    {
        $qid = $this->getAllSurveyQuestions()['Q00']->qid;
        $attributesWithoutLanguage = $this->countAttributesWithoutLanguage($qid);
        $this->assertGreaterThan(0, $attributesWithoutLanguage);

        // The import skips the attributes in languages the survey does not use
        $this->assertSame(0, $this->countAttributes($qid, 'de'));
        $this->assertSame(0, $this->countAttributes($qid, 'fr'));
        $this->assertSame('Not the standard Other label', $this->getAttributeValue($qid, 'other_replace_text', 'en'));

        // Adding German copies the English values
        self::$testSurvey->additional_languages = 'de';
        $this->assertTrue(self::$testSurvey->save());
        fixLanguageConsistency(self::$surveyId, 'de');
        $this->assertSame($this->countAttributes($qid, 'en'), $this->countAttributes($qid, 'de'));
        $this->assertSame('Not the standard Other label', $this->getAttributeValue($qid, 'other_replace_text', 'de'));

        // A German value that was emptied on purpose is not overwritten later on
        Yii::app()->db->createCommand()->update(
            '{{question_attributes}}',
            ['value' => ''],
            "qid = :qid AND attribute = 'other_replace_text' AND language = 'de'",
            [':qid' => $qid]
        );
        fixLanguageConsistency(self::$surveyId, 'de');
        $this->assertSame('', $this->getAttributeValue($qid, 'other_replace_text', 'de'));

        // Removing German deletes its attributes, but keeps the English and untranslated ones
        $englishAttributes = $this->countAttributes($qid, 'en');
        self::$testSurvey->additional_languages = '';
        $this->assertTrue(self::$testSurvey->save());
        cleanLanguagesFromSurvey(self::$surveyId, '');
        $this->assertSame(0, $this->countAttributes($qid, 'de'));
        $this->assertSame($englishAttributes, $this->countAttributes($qid, 'en'));
        $this->assertSame($attributesWithoutLanguage, $this->countAttributesWithoutLanguage($qid));
    }

    /**
     * The group import skips the translated attributes in languages the survey does not use.
     */
    public function testGroupImportSkipsAttributesInOtherLanguages()
    {
        $results = XMLImportGroup(
            self::$dataFolder . '/file_upload/limesurvey_group_20178_stale_attribute_languages.lsg',
            self::$surveyId,
            false
        );
        $this->assertArrayNotHasKey('fatalerror', $results);

        $qid = (int) Yii::app()->db->createCommand()
            ->select('qid')
            ->from('{{questions}}')
            ->where('gid = :gid AND parent_qid = 0', [':gid' => $results['newgid']])
            ->queryScalar();
        $this->assertGreaterThan(0, $qid);
        $this->assertSame(0, $this->countAttributes($qid, 'de'));
        $this->assertSame(0, $this->countAttributes($qid, 'fr'));
        $this->assertSame('Not the standard Other label', $this->getAttributeValue($qid, 'other_replace_text', 'en'));
        $this->assertGreaterThan(0, $this->countAttributesWithoutLanguage($qid));
    }
}
