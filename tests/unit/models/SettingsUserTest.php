<?php

namespace ls\tests;

use SettingsUser;

/**
 * Regression test for bug #20710: adding a question failed on PostgreSQL because
 * SettingsUser::getUserSetting() bound an integer $entity_id (e.g. a survey id) as
 * PDO::PARAM_INT against the varchar `entity_id` column, which PostgreSQL refuses
 * to compare ("operator does not exist: character varying = integer"). MySQL silently
 * coerces the types, so the bug only surfaced on PostgreSQL.
 */
class SettingsUserTest extends TestBaseClass
{
    private const STG_NAME = 'unittest_setting_integer_entity_id';
    private const UID = 1;
    private const ENTITY = 'Survey';
    // Deliberately an int, mirroring QuestionAdministrationController's
    // SettingsUser::setUserSetting('last_question_gid', ..., 'Survey', $question->sid).
    private const ENTITY_ID = 918273;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        SettingsUser::deleteUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID);
    }

    public static function tearDownAfterClass(): void
    {
        SettingsUser::deleteUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID);
        parent::tearDownAfterClass();
    }

    /**
     * A setting created with an integer entity_id must be creatable and then found again by getUserSetting().
     */
    public function testSetAndGetUserSettingWithIntegerEntityId()
    {
        $this->assertNull(
            SettingsUser::getUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID),
            'Precondition failed: setting should not exist yet'
        );

        $this->assertTrue(
            SettingsUser::setUserSetting(self::STG_NAME, 'somevalue', self::UID, self::ENTITY, self::ENTITY_ID)
        );

        $setting = SettingsUser::getUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID);
        $this->assertNotNull($setting);
        $this->assertSame('somevalue', $setting->stg_value);
        $this->assertSame((string) self::ENTITY_ID, $setting->entity_id);
    }

    /**
     * A second setUserSetting() call for the same integer entity_id must update the existing
     * row instead of failing to find it and creating a duplicate.
     */
    public function testUpdateUserSettingWithIntegerEntityId()
    {
        SettingsUser::setUserSetting(self::STG_NAME, 'firstvalue', self::UID, self::ENTITY, self::ENTITY_ID);

        // A second call must find and update the existing row rather than creating a duplicate,
        // which requires the entity_id lookup condition to work correctly.
        $this->assertTrue(
            SettingsUser::setUserSetting(self::STG_NAME, 'secondvalue', self::UID, self::ENTITY, self::ENTITY_ID)
        );

        $setting = SettingsUser::getUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID);
        $this->assertSame('secondvalue', $setting->stg_value);
    }

    /**
     * A setting stored with an integer entity_id must be deletable via the same lookup.
     */
    public function testDeleteUserSettingWithIntegerEntityId()
    {
        SettingsUser::setUserSetting(self::STG_NAME, 'somevalue', self::UID, self::ENTITY, self::ENTITY_ID);

        $this->assertTrue(
            SettingsUser::deleteUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID)
        );
        $this->assertNull(
            SettingsUser::getUserSetting(self::STG_NAME, self::UID, self::ENTITY, self::ENTITY_ID)
        );
    }
}
