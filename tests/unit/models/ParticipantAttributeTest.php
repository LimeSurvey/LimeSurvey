<?php

namespace ls\tests;

class ParticipantAttributeTest extends BaseModelTestCase
{
    protected $modelClassName = \ParticipantAttribute::class;

    /** @var string A valid participant ID */
    private const PARTICIPANT_ID = '0a1b2c3d-4e5f-4071-8293-a4b5c6d7e8f9';

    /** @var string A participant ID that contains characters with a special meaning in SQL and must be treated as plain data */
    private const QUOTED_PARTICIPANT_ID = "o'brien-id";

    public function setUp(): void
    {
        \Yii::app()->db->createCommand()->truncateTable('{{participant_attribute}}');
    }

    /**
     * The first call inserts the row, the second one updates it in place.
     */
    public function testUpdateParticipantAttributeValueInsertsThenUpdates()
    {
        $model = \ParticipantAttribute::model();
        $model->updateParticipantAttributeValue(['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => 7, 'value' => 'first']);
        $model->updateParticipantAttributeValue(['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => '7', 'value' => 'second']);

        $rows = \ParticipantAttribute::model()->findAll();
        $this->assertCount(1, $rows);
        $this->assertSame(self::PARTICIPANT_ID, $rows[0]->participant_id);
        $this->assertSame(7, (int) $rows[0]->attribute_id);
        $this->assertSame('second', $rows[0]->value);
    }

    /**
     * Special characters in the participant ID only ever match their own row.
     */
    public function testUpdateParticipantAttributeValueTreatsIdsAsData()
    {
        $model = \ParticipantAttribute::model();
        $model->updateParticipantAttributeValue(['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => 7, 'value' => 'untouched']);
        $model->updateParticipantAttributeValue(['participant_id' => self::QUOTED_PARTICIPANT_ID, 'attribute_id' => 7, 'value' => 'first']);
        $model->updateParticipantAttributeValue(['participant_id' => self::QUOTED_PARTICIPANT_ID, 'attribute_id' => 7, 'value' => 'second']);

        $this->assertCount(2, \ParticipantAttribute::model()->findAll());
        $this->assertSame('untouched', \ParticipantAttribute::model()->findByAttributes(['participant_id' => self::PARTICIPANT_ID])->value);
        $this->assertSame('second', \ParticipantAttribute::model()->findByAttributes(['participant_id' => self::QUOTED_PARTICIPANT_ID])->value);
    }

    /**
     * An attribute ID that is not a plain positive integer is refused and nothing is saved.
     *
     * The callers decide by the ID as given whether the value is encrypted, so an ID like "7 trailing" must not be
     * cast to 7: The value would be saved to attribute 7 without encryption.
     *
     * @dataProvider invalidAttributeIdProvider
     * @param mixed $attributeId The attribute ID to save a value for
     * @return void
     */
    public function testUpdateParticipantAttributeValueRefusesInvalidAttributeId($attributeId)
    {
        $refused = false;
        try {
            \ParticipantAttribute::model()->updateParticipantAttributeValue(
                ['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => $attributeId, 'value' => 'first']
            );
        } catch (\InvalidArgumentException $exception) {
            $refused = true;
        }

        $this->assertTrue($refused, 'The attribute ID was not refused');
        $this->assertCount(0, \ParticipantAttribute::model()->findAll());
    }

    /**
     * Attribute IDs that are not a plain positive integer.
     *
     * @return array<string, array{0: mixed}> One attribute ID per case
     */
    public function invalidAttributeIdProvider()
    {
        return [
            'trailing text' => ['7 trailing'],
            'leading space' => [' 7'],
            'trailing line break' => ["7\n"],
            'leading zeros' => ['007'],
            'plus sign' => ['+7'],
            'negative' => ['-7'],
            'zero' => ['0'],
            'integer zero' => [0],
            'decimal' => ['7.0'],
            'float' => [7.5],
            'too big for an integer' => ['99999999999999999999'],
            'text' => ['seven'],
            'empty' => [''],
            'null' => [null],
        ];
    }
}
