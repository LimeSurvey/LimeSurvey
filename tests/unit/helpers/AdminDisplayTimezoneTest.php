<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;
use SettingsUser;

/**
 * Tests for getAdminDisplayTimezone(), getUTCOfDate() and getDateOfUTC() in application/helpers/common_helper.php.
 */
class AdminDisplayTimezoneTest extends TestBaseClass
{
    private const UID = 1;

    /** @var string|null Original global displayTimezone config value */
    private $originalDisplayTimezone;

    /** @var string|null Original personal displayTimezone setting of the test user, null if not set */
    private $originalUserTimezone;

    /** @var mixed Original user id of the web user */
    private $originalUserId;

    /**
     * Loads the common helper.
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Remembers the original timezone settings and uses the test user as current user.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->originalDisplayTimezone = \Yii::app()->getConfig('displayTimezone');
        $setting = SettingsUser::getUserSetting('displayTimezone', self::UID);
        $this->originalUserTimezone = $setting ? $setting->stg_value : null;
        $this->originalUserId = \Yii::app()->user->getId();
        \Yii::app()->user->setId(self::UID);
    }

    /**
     * Restores the original timezone settings and current user.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->setConfig('displayTimezone', $this->originalDisplayTimezone);
        if ($this->originalUserTimezone === null) {
            SettingsUser::deleteUserSetting('displayTimezone', self::UID);
        } else {
            SettingsUser::setUserSetting('displayTimezone', $this->originalUserTimezone, self::UID);
        }
        \Yii::app()->user->setId($this->originalUserId);
        parent::tearDown();
    }

    /**
     * An empty personal timezone means "use the global setting", so dates must be shifted with the global timezone.
     * @return void
     */
    public function testEmptyPersonalTimezoneFallsBackToGlobalTimezone(): void
    {
        \Yii::app()->setConfig('displayTimezone', 'America/Toronto');
        SettingsUser::setUserSetting('displayTimezone', '', self::UID);

        $this->assertSame('America/Toronto', getAdminDisplayTimezone());
        // America/Toronto is UTC-5 in winter
        $this->assertSame('2026-01-15 17:00:00', getUTCOfDate('2026-01-15 12:00:00'));
        $this->assertSame('2026-01-15 12:00:00', getDateOfUTC('2026-01-15 17:00:00'));
    }

    /**
     * A personal timezone takes precedence over the global timezone.
     * @return void
     */
    public function testPersonalTimezoneOverridesGlobalTimezone(): void
    {
        \Yii::app()->setConfig('displayTimezone', 'America/Toronto');
        SettingsUser::setUserSetting('displayTimezone', 'Europe/Berlin', self::UID);

        $this->assertSame('Europe/Berlin', getAdminDisplayTimezone());
        // Europe/Berlin is UTC+1 in winter
        $this->assertSame('2026-01-15 11:00:00', getUTCOfDate('2026-01-15 12:00:00'));
        $this->assertSame('2026-01-15 12:00:00', getDateOfUTC('2026-01-15 11:00:00'));
    }

    /**
     * Without any timezone configured, dates are kept as they are (UTC).
     * @return void
     */
    public function testNoTimezoneConfiguredKeepsDate(): void
    {
        \Yii::app()->setConfig('displayTimezone', '');
        SettingsUser::deleteUserSetting('displayTimezone', self::UID);

        $this->assertSame('2026-01-15 12:00:00', getUTCOfDate('2026-01-15 12:00:00'));
        $this->assertSame('2026-01-15 12:00:00', getDateOfUTC('2026-01-15 12:00:00'));
    }

    /**
     * A personal timezone that is not a valid identifier is ignored, the same way LSYii_Controller ignores it.
     * @return void
     */
    public function testInvalidPersonalTimezoneFallsBackToGlobalTimezone(): void
    {
        \Yii::app()->setConfig('displayTimezone', 'America/Toronto');
        SettingsUser::setUserSetting('displayTimezone', 'US/Eastern', self::UID);

        $this->assertSame('America/Toronto', getAdminDisplayTimezone());
    }
}
