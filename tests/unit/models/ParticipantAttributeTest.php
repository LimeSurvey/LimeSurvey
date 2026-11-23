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
     * A non-numeric attribute ID never reaches the database as SQL.
     */
    public function testUpdateParticipantAttributeValueCastsAttributeId()
    {
        $model = \ParticipantAttribute::model();
        $model->updateParticipantAttributeValue(['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => '7 trailing', 'value' => 'first']);
        $model->updateParticipantAttributeValue(['participant_id' => self::PARTICIPANT_ID, 'attribute_id' => '7 trailing', 'value' => 'second']);

        $rows = \ParticipantAttribute::model()->findAll();
        $this->assertCount(1, $rows);
        $this->assertSame(7, (int) $rows[0]->attribute_id);
        $this->assertSame('second', $rows[0]->value);
    }
}
