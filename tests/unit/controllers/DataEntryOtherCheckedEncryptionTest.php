<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * Responses imported from a table without the tick column of multiple choice Other:
 * the tick is set from the text (DataEntry::fillOtherCheckedFromText()).
 * With hardened encryption, each response gets its own encrypted value (#19645).
 */
class DataEntryOtherCheckedEncryptionTest extends TestBaseClass
{
    /** @var string */
    private static $otherColumn;

    /** @var string */
    private static $checkedColumn;

    /** @var int[] ids of the responses with Other text */
    private static $withTextIds = [];

    /** @var int id of the response without Other text */
    private static $withoutTextId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_213203_other_checked.lss');
        $question = \Question::model()->findByAttributes(['sid' => self::$surveyId, 'title' => 'q1']);
        \Question::model()->updateByPk($question->qid, ['encrypted' => 'Y']);
        self::$otherColumn = "Q{$question->qid}_Cother";
        self::$checkedColumn = "Q{$question->qid}_Cotherchecked";

        $activator = new \SurveyActivator(\Survey::model()->findByPk(self::$surveyId));
        $activator->activate();
        \Yii::app()->db->schema->refresh();

        $db = \Yii::app()->db;
        foreach (['first', 'second', 'third', ''] as $text) {
            $db->createCommand()->insert("{{responses_" . self::$surveyId . "}}", [
                'startlanguage' => 'en',
                self::$otherColumn => $text === '' ? '' : \LSActiveRecord::encryptSingle($text, 'H'),
            ]);
            $id = (int) getLastInsertID("{{responses_" . self::$surveyId . "}}");
            if ($text === '') {
                self::$withoutTextId = $id;
            } else {
                self::$withTextIds[] = $id;
            }
        }
        \Yii::import('application.controllers.admin.DataEntry', true);
    }

    /**
     * Run DataEntry::fillOtherCheckedFromText() on the test responses, the tick column empty before
     * @param string[] $encryptedAttributes
     * @param string $encryptionMethod
     * @return array<int, string|null> tick value by response id
     */
    private function fillOtherChecked(array $encryptedAttributes, string $encryptionMethod): array
    {
        $db = \Yii::app()->db;
        $table = "{{responses_" . self::$surveyId . "}}";
        $db->createCommand()->update($table, [self::$checkedColumn => '']);

        $controller = new \DataEntry(new DummyController('dummyid'), 'dataentry');
        $method = new \ReflectionMethod(\DataEntry::class, 'fillOtherCheckedFromText');
        $allIds = array_merge(self::$withTextIds, [self::$withoutTextId]);
        $method->invoke(
            $controller,
            self::$surveyId,
            $db->schema->getTable($table, true),
            [],
            $allIds,
            $encryptedAttributes,
            $encryptionMethod
        );

        $rows = $db->createCommand()->select(['id', self::$checkedColumn])->from($table)->queryAll();
        return array_column($rows, self::$checkedColumn, 'id');
    }

    /** Hardened : each response gets its own encrypted tick. */
    public function testHardenedGivesADistinctEncryptedValueToEachResponse(): void
    {
        $ticks = $this->fillOtherChecked([self::$otherColumn, self::$checkedColumn], 'H');

        $withText = array_map(function ($id) use ($ticks) {
            return $ticks[$id];
        }, self::$withTextIds);
        foreach ($withText as $tick) {
            $this->assertNotSame('Y', $tick, 'The tick must be encrypted.');
            $this->assertSame('Y', \LSActiveRecord::decryptSingle($tick, 'H'));
        }
        $this->assertCount(count($withText), array_unique($withText), 'Each response must get its own encrypted value.');
        $this->assertEmpty($ticks[self::$withoutTextId], 'Without Other text, Other is not ticked.');
    }

    /** Basic : the same encrypted tick for all responses (one query). */
    public function testBasicGivesTheSameEncryptedValue(): void
    {
        $ticks = $this->fillOtherChecked([self::$otherColumn, self::$checkedColumn], 'B');

        foreach (self::$withTextIds as $id) {
            $this->assertSame(\LSActiveRecord::encryptSingle('Y', 'B'), $ticks[$id]);
        }
        $this->assertEmpty($ticks[self::$withoutTextId]);
    }

    /** Not encrypted : the tick is Y. */
    public function testNotEncryptedTickIsY(): void
    {
        $ticks = $this->fillOtherChecked([], 'H');

        foreach (self::$withTextIds as $id) {
            $this->assertSame('Y', $ticks[$id]);
        }
        $this->assertEmpty($ticks[self::$withoutTextId]);
    }
}
