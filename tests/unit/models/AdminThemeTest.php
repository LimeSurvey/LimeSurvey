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
     * The theme tokens URL is the one of the user's admin theme, with a version for cache busting.
     */
    public function testThemeTokensUrlOfUserTheme()
    {
        SettingsUser::setUserSetting('admintheme', 'Sea_Green_High_Contrast', self::UID);
        $this->assertMatchesRegularExpression(
            '#/Sea_Green_High_Contrast/css/theme_tokens\.css\?v=\d+$#',
            AdminTheme::getThemeTokensUrl()
        );
    }

    /**
     * Without a personal choice, the theme tokens URL is the one of the global admin theme.
     */
    public function testThemeTokensUrlOfGlobalTheme()
    {
        SettingsUser::setUserSetting('admintheme', '', self::UID);
        $this->assertMatchesRegularExpression(
            '#/' . \Yii::app()->getConfig('admintheme') . '/css/theme_tokens\.css\?v=\d+$#',
            AdminTheme::getThemeTokensUrl()
        );
    }

    /**
     * If users cannot choose their own admin theme, their choice is ignored.
     */
    public function testPersonalThemeNotAllowed()
    {
        SettingsUser::setUserSetting('admintheme', 'Sea_Green_High_Contrast', self::UID);
        $allowed = \Yii::app()->getConfig('allowpersonaladmintheme');
        \Yii::app()->setConfig('allowpersonaladmintheme', false);
        try {
            $this->assertNull(AdminTheme::getUserAdminThemeName());
        } finally {
            \Yii::app()->setConfig('allowpersonaladmintheme', $allowed);
        }
    }

    /**
     * An uploaded theme with a dot in its name can be chosen, a hidden directory can not.
     */
    public function testUploadedThemeNames()
    {
        $uploadDir = \Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'admintheme';
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');
        $themeDirs = [];
        foreach (['Company.Blue', '.hidden'] as $themeName) {
            $themeDirs[$themeName] = $uploadDir . DIRECTORY_SEPARATOR . $themeName;
            @mkdir($themeDirs[$themeName], 0777, true);
            file_put_contents($themeDirs[$themeName] . '/config.xml', $configXml);
        }
        try {
            $personalThemes = AdminTheme::getPersonalAdminThemeList();
            $this->assertArrayHasKey('Company.Blue', $personalThemes);
            $this->assertArrayNotHasKey('.hidden', $personalThemes);

            SettingsUser::setUserSetting('admintheme', 'Company.Blue', self::UID);
            $this->assertSame('Company.Blue', AdminTheme::getUserAdminThemeName());

            SettingsUser::setUserSetting('admintheme', '.hidden', self::UID);
            $this->assertNull(AdminTheme::getUserAdminThemeName());
        } finally {
            foreach ($themeDirs as $themeDir) {
                unlink($themeDir . '/config.xml');
                rmdir($themeDir);
            }
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
