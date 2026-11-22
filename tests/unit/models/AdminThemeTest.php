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
     * An uploaded theme with a dot or a numeric name can be chosen, a hidden directory can not.
     * The themes are created in a temporary upload directory, so existing uploaded themes are not touched.
     */
    public function testUploadedThemeNames()
    {
        $originalUploadDir = \Yii::app()->getConfig('uploaddir');
        $tempUploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ls-adminthemetest-' . uniqid();
        $themeRootDir = $tempUploadDir . DIRECTORY_SEPARATOR . 'admintheme';
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');
        $themeNames = ['Company.Blue', '123', '.hidden'];
        foreach ($themeNames as $themeName) {
            mkdir($themeRootDir . DIRECTORY_SEPARATOR . $themeName, 0777, true);
            file_put_contents($themeRootDir . DIRECTORY_SEPARATOR . $themeName . '/config.xml', $configXml);
        }
        \Yii::app()->setConfig('uploaddir', $tempUploadDir);
        try {
            $personalThemes = AdminTheme::getPersonalAdminThemeList();
            $this->assertArrayHasKey('Company.Blue', $personalThemes);
            // A numeric directory name is an integer key, it must not be renumbered
            $this->assertArrayHasKey(123, $personalThemes);
            $this->assertArrayNotHasKey('.hidden', $personalThemes);

            SettingsUser::setUserSetting('admintheme', 'Company.Blue', self::UID);
            $this->assertSame('Company.Blue', AdminTheme::getUserAdminThemeName());

            SettingsUser::setUserSetting('admintheme', '123', self::UID);
            $this->assertSame('123', AdminTheme::getUserAdminThemeName());

            SettingsUser::setUserSetting('admintheme', '.hidden', self::UID);
            $this->assertNull(AdminTheme::getUserAdminThemeName());
        } finally {
            \Yii::app()->setConfig('uploaddir', $originalUploadDir);
            foreach ($themeNames as $themeName) {
                unlink($themeRootDir . DIRECTORY_SEPARATOR . $themeName . '/config.xml');
                rmdir($themeRootDir . DIRECTORY_SEPARATOR . $themeName);
            }
            rmdir($themeRootDir);
            rmdir($tempUploadDir);
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

    /**
     * A theme without its own images uses the images of Sea Green, e.g. the colour palette of the charts.
     */
    public function testImagePathFallsBackToSeaGreen()
    {
        $styleDir = \Yii::app()->getConfig('styledir');
        $seaGreenPalette = $styleDir . DIRECTORY_SEPARATOR . 'Sea_Green' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'limesurvey.pal';
        $this->assertFileExists($seaGreenPalette);

        $theme = new AdminTheme();
        $theme->path = $styleDir . DIRECTORY_SEPARATOR . 'Sea_Green';
        $this->assertSame($seaGreenPalette, $theme->getImagePath('limesurvey.pal'));

        $theme->path = $styleDir . DIRECTORY_SEPARATOR . 'Sea_Green_High_Contrast';
        $this->assertFileDoesNotExist($theme->path . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'limesurvey.pal');
        $this->assertSame($seaGreenPalette, $theme->getImagePath('limesurvey.pal'));
    }

    /**
     * A theme with its own image uses it.
     */
    public function testImagePathOfThemeWithOwnImage()
    {
        $themeDir = \Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'admintheme_test_' . uniqid();
        $ownPalette = $themeDir . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'limesurvey.pal';
        mkdir($themeDir . DIRECTORY_SEPARATOR . 'images', 0777, true);
        file_put_contents($ownPalette, "0,0,0\n");
        try {
            $theme = new AdminTheme();
            $theme->path = $themeDir;
            $this->assertSame($ownPalette, $theme->getImagePath('limesurvey.pal'));
        } finally {
            unlink($ownPalette);
            rmdir($themeDir . DIRECTORY_SEPARATOR . 'images');
            rmdir($themeDir);
        }
    }
}
