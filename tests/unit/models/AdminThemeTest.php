<?php

namespace ls\tests;

use AdminTheme;
use SettingsUser;

/**
 * Tests the personal admin theme setting of a user (issue #19895).
 */
class AdminThemeTest extends TestBaseClass
{
    private const UID = 1;

    /** @var string|null The user's admin theme setting before the test, restored afterwards */
    private $originalSetting;

    /**
     * Logs in the test user and remembers their admin theme setting.
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::app()->user->setId(self::UID);
        $this->originalSetting = SettingsUser::getUserSettingValue('admintheme', self::UID);
    }

    /**
     * Restores the user's admin theme setting.
     */
    public function tearDown(): void
    {
        \Yii::app()->user->setId(self::UID);
        if ($this->originalSetting === null) {
            SettingsUser::deleteUserSetting('admintheme', self::UID);
        } else {
            SettingsUser::setUserSetting('admintheme', $this->originalSetting, self::UID);
        }
        parent::tearDown();
    }

    /**
     * A standard admin theme chosen by the user is used.
     */
    public function testUserChoiceIsUsed()
    {
        SettingsUser::setUserSetting('admintheme', 'Sea_Green_High_Contrast', self::UID);
        $this->assertSame('Sea_Green_High_Contrast', AdminTheme::getUserAdminThemeName());
    }

    /**
     * An empty setting means "use the global setting".
     */
    public function testEmptySettingUsesGlobalTheme()
    {
        SettingsUser::setUserSetting('admintheme', '', self::UID);
        $this->assertNull(AdminTheme::getUserAdminThemeName());
    }

    /**
     * A theme that does not exist (anymore) is ignored.
     */
    public function testMissingThemeIsIgnored()
    {
        SettingsUser::setUserSetting('admintheme', 'Black_Pearl', self::UID);
        $this->assertNull(AdminTheme::getUserAdminThemeName());
    }

    /**
     * Users can write any of their own settings, so a path in the setting must not be followed.
     */
    public function testPathIsIgnored()
    {
        SettingsUser::setUserSetting('admintheme', '../admin/Sea_Green', self::UID);
        $this->assertNull(AdminTheme::getUserAdminThemeName());
    }

    /**
     * The theme tokens of the standard admin themes are found, with a version for cache busting.
     */
    public function testThemeTokensUrls()
    {
        $urls = AdminTheme::getThemeTokensUrls();
        foreach (AdminTheme::STANDARD_ADMIN_THEMES as $themeName) {
            $this->assertArrayHasKey($themeName, $urls);
            $this->assertMatchesRegularExpression(
                '#/' . $themeName . '/css/theme_tokens\.css\?v=\d+$#',
                $urls[$themeName]
            );
        }
    }

    /**
     * Without a logged in user there is no personal setting.
     */
    public function testGuestHasNoUserTheme()
    {
        SettingsUser::setUserSetting('admintheme', 'Sea_Green_High_Contrast', self::UID);
        \Yii::app()->user->setId(null);
        $this->assertNull(AdminTheme::getUserAdminThemeName());
    }
}
