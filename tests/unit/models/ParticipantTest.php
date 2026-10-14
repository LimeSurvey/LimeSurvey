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
}
