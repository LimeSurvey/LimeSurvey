<?php

namespace ls\tests;


class ParticipantTest extends BaseModelTestCase
{
    protected $modelClassName = \Participant::class;

    /**
     * Invalid email addresses must fail validation, so they can't be copied into survey participant lists later.
     *
     * @return void
     */
    public function testInvalidEmailFailsValidation()
    {
        $participant = new \Participant();
        $participant->email = 'test@example.org;';
        $this->assertFalse($participant->validate(['email']));

        $participant->email = 'not-an-email';
        $this->assertFalse($participant->validate(['email']));
    }

    /**
     * Empty, single and multiple valid email addresses must pass validation, like in survey participant lists.
     *
     * @return void
     */
    public function testValidEmailPassesValidation()
    {
        $participant = new \Participant();
        foreach (['', 'test@example.org', ' test@example.org ', 'test1@example.org;test2@example.org'] as $email) {
            $participant->email = $email;
            $this->assertTrue($participant->validate(['email']), "Email '$email' should be valid");
        }
    }

    /**
     * Searching by a partial email must still be possible.
     *
     * @return void
     */
    public function testPartialEmailAllowedInSearchScenario()
    {
        $participant = new \Participant('search');
        $participant->email = 'example';
        $this->assertTrue($participant->validate(['email']));
    }

    /**
     * Text attribute values in the participant list must be shown as entered, not interpreted as HTML.
     *
     * @return void
     */
    public function testExtraAttributeColumnShowsValueAsPlainText()
    {
        \Yii::import('application.helpers.surveytranslator_helper', true);

        $attributeName = new \ParticipantAttributeName();
        $attributeName->attribute_type = 'TB';
        $attributeName->defaultname = 'plaintextcheck';
        $attributeName->visible = 'TRUE';
        $attributeName->encrypted = 'N';
        $attributeName->core_attribute = 'N';
        $this->assertTrue($attributeName->save());

        $participant = new \Participant();
        $participant->participant_id = $participant->genUuid();
        $participant->firstname = 'plaintextcheck';
        $participant->owner_uid = 1;
        $participant->created_by = 1;
        $participant->blacklisted = 'N';
        $this->assertTrue($participant->save());

        $value = '<b>bold</b> & "quoted"';
        $attribute = new \ParticipantAttribute();
        $attribute->participant_id = $participant->participant_id;
        $attribute->attribute_id = $attributeName->attribute_id;
        $attribute->value = $value;

        try {
            $this->assertTrue($attribute->save());
            $columnId = 'ea_' . $attributeName->attribute_id;
            $columns = array_column($participant->getColumns(), null, 'id');
            $this->assertArrayHasKey($columnId, $columns);

            $cell = \Yii::app()->format->format(
                $participant->getParticipantAttribute($columnId),
                $columns[$columnId]['type']
            );
            $this->assertStringContainsString(\CHtml::encode($value), $cell);
            $this->assertStringNotContainsString('<b>', $cell);
        } finally {
            \ParticipantAttribute::model()->deleteAllByAttributes(['participant_id' => $participant->participant_id]);
            $participant->delete();
            $attributeName->delete();
        }
    }
}
