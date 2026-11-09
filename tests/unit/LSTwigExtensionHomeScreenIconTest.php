<?php

namespace ls\tests;

use LS_Twig_Extension;

/**
 * Tests for LS_Twig_Extension::homeScreenIconSrc().
 *
 * The theme inheritance chain is built from theme configurations pointing to temporary folders,
 * so the tests don't depend on installed themes.
 *
 * @group template
 */
class LSTwigExtensionHomeScreenIconTest extends TestBaseClass
{
    /** @var string Temporary folder holding the theme folders of a test */
    private $themesRoot;

    /**
     * Creates the temporary folder for the theme folders.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->themesRoot = sys_get_temp_dir() . '/homescreenicontest' . uniqid();
        mkdir($this->themesRoot);
    }

    /**
     * Removes the temporary theme folders and the theme instance.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        rmdirr($this->themesRoot);
        \Template::resetInstance();
        parent::tearDown();
    }

    /**
     * Creates a theme folder with the given icons and returns its configuration.
     *
     * @param string $themeName Name of the theme folder
     * @param string[] $iconFiles Icon file names to put into the theme's files folder
     * @param \TemplateConfiguration|null $motherTheme The theme this one extends, null for a core theme
     * @return \TemplateConfiguration The configuration of the new theme
     */
    private function createTheme(string $themeName, array $iconFiles, ?\TemplateConfiguration $motherTheme = null): \TemplateConfiguration
    {
        $themePath = $this->themesRoot . '/' . $themeName . '/';
        mkdir($themePath . 'files', 0777, true);
        foreach ($iconFiles as $iconFile) {
            $this->assertTrue(copy(
                \Yii::app()->getConfig('standardthemerootdir') . '/fruity_twentythree/files/' . $iconFile,
                $themePath . 'files/' . $iconFile
            ));
        }
        $theme = new \TemplateConfiguration();
        $theme->path = $themePath;
        $theme->oMotherTemplate = $motherTheme;
        return $theme;
    }

    /**
     * Makes the given theme the one being rendered.
     *
     * @param \TemplateConfiguration $theme The theme configuration
     * @return void
     */
    private function renderTheme(\TemplateConfiguration $theme): void
    {
        $instance = new \ReflectionProperty(\Template::class, 'instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $theme);
    }

    /**
     * Returns the published URL of a theme's home screen icon.
     *
     * @param \TemplateConfiguration $theme The theme holding the icon
     * @return string The asset URL
     */
    private function publishedIconUrl(\TemplateConfiguration $theme): string
    {
        return \Yii::app()->assetManager->publish($theme->path . './files/home-screen-icon-192.png');
    }

    /**
     * A core theme shows its own home screen icon.
     */
    public function testCoreTheme()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $this->renderTheme($core);
        $this->assertSame($this->publishedIconUrl($core), LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * An extended theme without own icons inherits the home screen icon together with the favicon.
     */
    public function testExtendedThemeWithoutOwnIcons()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $this->renderTheme($this->createTheme('extended', [], $core));
        $this->assertSame($this->publishedIconUrl($core), LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * An extended theme with its own favicon but no own home screen icon shows no home screen icon.
     */
    public function testExtendedThemeWithOwnFaviconOnly()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $this->renderTheme($this->createTheme('extended', ['favicon.ico'], $core));
        $this->assertFalse(LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * An extended theme with its own favicon and home screen icon shows its own home screen icon.
     */
    public function testExtendedThemeWithOwnFaviconAndIcon()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $extended = $this->createTheme('extended', ['favicon.ico', 'home-screen-icon-192.png'], $core);
        $this->renderTheme($extended);
        $this->assertSame($this->publishedIconUrl($extended), LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * An extended theme with only its own home screen icon shows it, even with the inherited favicon.
     */
    public function testExtendedThemeWithOwnIconOnly()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $extended = $this->createTheme('extended', ['home-screen-icon-192.png'], $core);
        $this->renderTheme($extended);
        $this->assertSame($this->publishedIconUrl($extended), LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * A theme extending a theme with its own favicon doesn't get the core home screen icon either.
     */
    public function testSecondLevelThemeBelowOwnFavicon()
    {
        $core = $this->createTheme('core', ['favicon.ico', 'home-screen-icon-192.png']);
        $extended = $this->createTheme('extended', ['favicon.ico'], $core);
        $this->renderTheme($this->createTheme('secondlevel', [], $extended));
        $this->assertFalse(LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * Without a favicon anywhere in the chain, the home screen icon is shown.
     */
    public function testNoFavicon()
    {
        $core = $this->createTheme('core', ['home-screen-icon-192.png']);
        $this->renderTheme($this->createTheme('extended', [], $core));
        $this->assertSame($this->publishedIconUrl($core), LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * Without a home screen icon anywhere in the chain, there is nothing to show.
     */
    public function testNoHomeScreenIcon()
    {
        $core = $this->createTheme('core', ['favicon.ico']);
        $this->renderTheme($this->createTheme('extended', [], $core));
        $this->assertFalse(LS_Twig_Extension::homeScreenIconSrc());
    }

    /**
     * The function is available to survey themes.
     */
    public function testAvailableInTwig()
    {
        $templateConfiguration = \Template::getInstance('fruity_twentythree', null, null, false);
        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ homeScreenIconSrc() }}', [], $templateConfiguration);
        $this->assertSame(
            \Yii::app()->assetManager->publish($templateConfiguration->path . './files/home-screen-icon-192.png'),
            $output
        );
    }
}
