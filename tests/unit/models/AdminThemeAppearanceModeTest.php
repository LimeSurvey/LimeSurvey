<?php

namespace ls\tests;

use AdminTheme;
use SettingsUser;

/**
 * Tests the appearance mode (light, dark, system) of the admin interface.
 */
class AdminThemeAppearanceModeTest extends TestBaseClass
{
    private const UID = 1;

    /** @var string|null The user's appearance mode setting before the test, restored afterwards */
    private $originalSetting;

    /** @var mixed The default appearance mode before the test, restored afterwards */
    private $originalDefault;

    /**
     * Logs in the test user and remembers the appearance mode settings.
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::app()->user->setId(self::UID);
        $this->originalSetting = SettingsUser::getUserSettingValue('appearancemode', self::UID);
        $this->originalDefault = \Yii::app()->getConfig('defaultappearancemode');
    }

    /**
     * Restores the appearance mode settings.
     */
    public function tearDown(): void
    {
        \Yii::app()->user->setId(self::UID);
        if ($this->originalSetting === null) {
            SettingsUser::deleteUserSetting('appearancemode', self::UID);
        } else {
            SettingsUser::setUserSetting('appearancemode', $this->originalSetting, self::UID);
        }
        \Yii::app()->setConfig('defaultappearancemode', $this->originalDefault);
        parent::tearDown();
    }

    /**
     * The appearance mode chosen by the user wins over the default.
     */
    public function testUserChoiceWinsOverDefault()
    {
        \Yii::app()->setConfig('defaultappearancemode', 'light');
        SettingsUser::setUserSetting('appearancemode', 'dark', self::UID);
        $this->assertSame('dark', AdminTheme::getUserAppearanceMode());
        $this->assertSame('dark', AdminTheme::getAppearanceMode());
    }

    /**
     * An empty setting means "use the global setting".
     */
    public function testEmptySettingUsesDefault()
    {
        \Yii::app()->setConfig('defaultappearancemode', 'system');
        SettingsUser::setUserSetting('appearancemode', '', self::UID);
        $this->assertNull(AdminTheme::getUserAppearanceMode());
        $this->assertSame('system', AdminTheme::getAppearanceMode());
    }

    /**
     * Users can write any of their own settings, so an unknown appearance mode is ignored.
     */
    public function testUnknownUserSettingIsIgnored()
    {
        \Yii::app()->setConfig('defaultappearancemode', 'dark');
        SettingsUser::setUserSetting('appearancemode', 'sepia', self::UID);
        $this->assertNull(AdminTheme::getUserAppearanceMode());
        $this->assertSame('dark', AdminTheme::getAppearanceMode());
    }

    /**
     * An unknown default appearance mode (e.g. a typo in config.php) falls back to "light".
     */
    public function testUnknownDefaultFallsBackToLight()
    {
        \Yii::app()->setConfig('defaultappearancemode', 'night');
        SettingsUser::setUserSetting('appearancemode', '', self::UID);
        $this->assertSame('light', AdminTheme::getDefaultAppearanceMode());
        $this->assertSame('light', AdminTheme::getAppearanceMode());
    }

    /**
     * Without a logged in user (e.g. on the login page) the default is used.
     */
    public function testGuestGetsDefault()
    {
        \Yii::app()->setConfig('defaultappearancemode', 'system');
        SettingsUser::setUserSetting('appearancemode', 'dark', self::UID);
        \Yii::app()->user->setId(null);
        $this->assertNull(AdminTheme::getUserAppearanceMode());
        $this->assertSame('system', AdminTheme::getAppearanceMode());
    }

    /**
     * Every appearance mode has a name for the settings.
     */
    public function testEveryAppearanceModeHasAName()
    {
        $this->assertSame(AdminTheme::APPEARANCE_MODES, array_keys(AdminTheme::getAppearanceModeList()));
    }

    /**
     * The standard admin themes have a dark mode, and its CSS files exist for both text directions.
     */
    public function testStandardThemesHaveDarkMode()
    {
        foreach (AdminTheme::STANDARD_ADMIN_THEMES as $themeName) {
            $themeDir = \Yii::app()->getConfig('styledir') . DIRECTORY_SEPARATOR . $themeName;
            $theme = new AdminTheme();
            $theme->config = $this->loadThemeConfig(file_get_contents($themeDir . '/config.xml'));
            $this->assertTrue($theme->hasDarkMode(), $themeName . ' has no dark mode');

            $darkCssFiles = [
                $theme->config->files->dark->css->filename,
                $theme->config->files->rtl->dark->css->filename,
            ];
            foreach ($darkCssFiles as $darkCssFile) {
                $this->assertFileExists($themeDir . '/css/' . $darkCssFile);
            }
        }
    }

    /**
     * A theme without dark mode CSS files is always shown in the light mode.
     */
    public function testThemeWithoutDarkModeStaysLight()
    {
        SettingsUser::setUserSetting('appearancemode', 'dark', self::UID);
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');

        $theme = new AdminTheme();
        $theme->config = $this->loadThemeConfig($configXml);
        $this->assertSame('dark', $theme->getActiveAppearanceMode());

        $theme->config = $this->loadThemeConfig(preg_replace('#<dark>.*?</dark>#s', '', $configXml));
        $this->assertFalse($theme->hasDarkMode());
        $this->assertSame('light', $theme->getActiveAppearanceMode());
    }

    /**
     * A theme with dark CSS for left-to-right languages only has no dark mode in a right-to-left language,
     * because the right-to-left CSS is loaded there.
     */
    public function testDarkModeFollowsTextDirection()
    {
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');
        $theme = new AdminTheme();
        // Remove the dark CSS inside the rtl node only
        $theme->config = $this->loadThemeConfig(
            preg_replace('#(<rtl>.*?)<dark>.*?</dark>(.*?</rtl>)#s', '$1$2', $configXml)
        );
        $this->assertObjectNotHasProperty('dark', $theme->config->files->rtl);
        $originalLanguage = \Yii::app()->language;
        try {
            \Yii::app()->setLanguage('en');
            $this->assertTrue($theme->hasDarkMode());
            \Yii::app()->setLanguage('ar');
            $this->assertFalse($theme->hasDarkMode());

            $theme->config = $this->loadThemeConfig($configXml);
            $this->assertTrue($theme->hasDarkMode());
        } finally {
            \Yii::app()->setLanguage($originalLanguage);
        }
    }

    /**
     * The React editor gets the appearance mode of the user, or "light" if the user's admin theme has no dark mode.
     * The theme without dark mode is created in a temporary upload directory.
     */
    public function testUserThemeAppearanceMode()
    {
        $originalThemeSetting = SettingsUser::getUserSettingValue('admintheme', self::UID);
        $originalTheme = \Yii::app()->getConfig('admintheme');
        $originalUploadDir = \Yii::app()->getConfig('uploaddir');
        $tempUploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ls-appearancemodetest-' . uniqid();
        $themeDir = $tempUploadDir . DIRECTORY_SEPARATOR . 'admintheme' . DIRECTORY_SEPARATOR . 'No_Dark_Mode';
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');
        mkdir($themeDir, 0777, true);
        file_put_contents($themeDir . '/config.xml', preg_replace('#<dark>.*?</dark>#s', '', $configXml));

        SettingsUser::setUserSetting('admintheme', '', self::UID);
        SettingsUser::setUserSetting('appearancemode', 'dark', self::UID);
        \Yii::app()->setConfig('uploaddir', $tempUploadDir);
        try {
            \Yii::app()->setConfig('admintheme', 'Sea_Green');
            $this->assertSame('dark', AdminTheme::getUserThemeAppearanceMode());

            \Yii::app()->setConfig('admintheme', 'No_Dark_Mode');
            $this->assertSame('light', AdminTheme::getUserThemeAppearanceMode());

            \Yii::app()->setConfig('admintheme', 'Missing_Theme');
            $this->assertSame('light', AdminTheme::getUserThemeAppearanceMode());
        } finally {
            \Yii::app()->setConfig('admintheme', $originalTheme);
            \Yii::app()->setConfig('uploaddir', $originalUploadDir);
            if ($originalThemeSetting === null) {
                SettingsUser::deleteUserSetting('admintheme', self::UID);
            } else {
                SettingsUser::setUserSetting('admintheme', $originalThemeSetting, self::UID);
            }
            unlink($themeDir . '/config.xml');
            rmdir($themeDir);
            rmdir(dirname($themeDir));
            rmdir($tempUploadDir);
        }
    }

    /**
     * The CSS files of a package with "cssMedia" are limited to that media query.
     */
    public function testPackageCssMedia()
    {
        $clientScript = \Yii::app()->clientScript;
        $clientScript->addPackage('appearance-mode-test', [
            'baseUrl' => '/appearance-mode-test',
            'css' => ['dark.css'],
            'cssMedia' => 'screen and (prefers-color-scheme: dark)',
        ]);
        $clientScript->registerPackage('appearance-mode-test');
        try {
            $clientScript->renderCoreScripts();
            $cssFiles = $clientScript->getCssFiles();
            $this->assertSame(
                'screen and (prefers-color-scheme: dark)',
                $cssFiles['/appearance-mode-test/dark.css'] ?? null
            );
        } finally {
            $clientScript->reset();
        }
    }

    /**
     * The React editor is opened by a request in the default language of the site. A theme with dark CSS for
     * left-to-right languages only is dark there if the language of the administrator is a left-to-right one.
     */
    public function testUserThemeAppearanceModeUsesAdminLanguage()
    {
        $originalThemeSetting = SettingsUser::getUserSettingValue('admintheme', self::UID);
        $originalTheme = \Yii::app()->getConfig('admintheme');
        $originalUploadDir = \Yii::app()->getConfig('uploaddir');
        $originalAdminLanguage = \Yii::app()->session['adminlang'];
        $originalLanguage = \Yii::app()->language;
        $tempUploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ls-appearancemodetest-' . uniqid();
        $themeDir = $tempUploadDir . DIRECTORY_SEPARATOR . 'admintheme' . DIRECTORY_SEPARATOR . 'No_Rtl_Dark_Mode';
        $configXml = file_get_contents(\Yii::app()->getConfig('styledir') . '/Sea_Green/config.xml');
        mkdir($themeDir, 0777, true);
        file_put_contents(
            $themeDir . '/config.xml',
            preg_replace('#(<rtl>.*?)<dark>.*?</dark>(.*?</rtl>)#s', '$1$2', $configXml)
        );

        SettingsUser::setUserSetting('admintheme', '', self::UID);
        SettingsUser::setUserSetting('appearancemode', 'dark', self::UID);
        \Yii::app()->setConfig('uploaddir', $tempUploadDir);
        \Yii::app()->setConfig('admintheme', 'No_Rtl_Dark_Mode');
        try {
            \Yii::app()->setLanguage('ar');
            \Yii::app()->session['adminlang'] = 'en';
            $this->assertSame('dark', AdminTheme::getUserThemeAppearanceMode());

            \Yii::app()->setLanguage('en');
            \Yii::app()->session['adminlang'] = 'ar';
            $this->assertSame('light', AdminTheme::getUserThemeAppearanceMode());
        } finally {
            \Yii::app()->setLanguage($originalLanguage);
            \Yii::app()->session['adminlang'] = $originalAdminLanguage;
            \Yii::app()->setConfig('admintheme', $originalTheme);
            \Yii::app()->setConfig('uploaddir', $originalUploadDir);
            if ($originalThemeSetting === null) {
                SettingsUser::deleteUserSetting('admintheme', self::UID);
            } else {
                SettingsUser::setUserSetting('admintheme', $originalThemeSetting, self::UID);
            }
            unlink($themeDir . '/config.xml');
            rmdir($themeDir);
            rmdir(dirname($themeDir));
            rmdir($tempUploadDir);
        }
    }

    /**
     * HTML that is not an admin page (the preview of the theme editor) is rendered without the dark CSS,
     * the script that sets data-bs-theme and the color-scheme meta tag. Other scripts are kept.
     */
    public function testUnregisterAppearanceMode()
    {
        $clientScript = \Yii::app()->clientScript;
        $clientScript->addPackage('admin-theme-dark', [
            'baseUrl' => '/appearance-mode-test',
            'css' => ['dark.css'],
        ]);
        $clientScript->registerPackage('admin-theme-dark');
        $clientScript->registerScript('appearancemode', '/* Appearance mode */', \CClientScript::POS_HEAD);
        $clientScript->registerScript('otherscript', '/* Other script */', \CClientScript::POS_HEAD);
        $clientScript->registerMetaTag('dark', 'color-scheme', null, [], 'color-scheme');
        try {
            AdminTheme::unregisterAppearanceMode();
            $this->assertFalse($clientScript->isScriptRegistered('appearancemode', \CClientScript::POS_HEAD));
            $this->assertTrue($clientScript->isScriptRegistered('otherscript', \CClientScript::POS_HEAD));

            $output = '<html><head><title>Preview</title></head><body></body></html>';
            $clientScript->render($output);
            $this->assertStringContainsString('Other script', $output);
            $this->assertStringNotContainsString('Appearance mode', $output);
            $this->assertStringNotContainsString('color-scheme', $output);
            $this->assertStringNotContainsString('dark.css', $output);
        } finally {
            $clientScript->reset();
        }
    }

    /**
     * Parses a config.xml the same way AdminTheme does.
     *
     * @param string $configXml
     * @return object
     */
    private function loadThemeConfig($configXml)
    {
        return json_decode(json_encode((array) simplexml_load_string($configXml), 1));
    }
}
