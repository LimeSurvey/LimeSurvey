<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Admin Theme Model
 *
 *
 * @package       LimeSurvey
 * @subpackage    Backend
 */
class AdminTheme extends CFormModel
{
    /** @var string[] The admin themes shipped with LimeSurvey, in themes/admin */
    const STANDARD_ADMIN_THEMES = ['Sea_Green', 'Sea_Green_WCAG_AA'];

    /** @var string[] The appearance modes: "system" follows the light or dark mode of the user's device */
    const APPEARANCE_MODES = ['light', 'dark', 'system'];

    /** @var string $name Admin Theme's name */
    public $name;
    /** @var string $path Admin Theme's path */
    public $path;
    /** @var string $sTemplateUrl URL to reach Admin Theme (used to get CSS/JS/Files when asset manager is off) */
    public $sTemplateUrl;
    /** @var mixed $config Contains the Admin Theme's configuration file */
    public $config;
    /** @var boolean $use_asset_manager If true, force the use of asset manager even if debug mode is on (useful to debug asset manager's problems) */
    public static $use_asset_manager;
    /** @var AdminTheme $instance The instance of theme object */
    private static $instance;

    /**
     * Get the list of admin theme, as an array containing each configuration object for each template
     * @return array the array of configuration objects, theme directory name => configuration object
     *               (a numeric directory name is an integer key)
     */
    public static function getAdminThemeList()
    {
        $sStandardTemplateRootDir  = Yii::app()->getConfig("styledir"); // The directory containing the default admin themes
        $sUserTemplateDir          = Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'admintheme'; // The directory containing the user themes

        $aStandardThemeObjects     = self::getThemeList($sStandardTemplateRootDir); // array containing the configuration files of standard admin themes (styles/...)
        $aUserThemeObjects         = self::getThemeList($sUserTemplateDir); // array containing the configuration files of user admin themes (upload/admintheme/...)
        // array_replace() keeps numeric directory names as keys, array_merge() would renumber them
        $aListOfThemeObjects       = array_replace($aStandardThemeObjects, $aUserThemeObjects);

        ksort($aListOfThemeObjects);
        return $aListOfThemeObjects;
    }

    /**
     * Returns whether users can choose their own admin theme in their personal settings
     * (global setting, a superadministrator can switch it off to use the same admin theme for everyone)
     *
     * @return bool
     */
    public static function isPersonalAdminThemeAllowed()
    {
        return (bool) App()->getConfig('allowpersonaladmintheme');
    }

    /**
     * Returns the admin themes users can choose in their personal settings
     *
     * @return array the array of configuration objects, theme name => configuration object
     */
    public static function getPersonalAdminThemeList()
    {
        return array_filter(
            self::getAdminThemeList(),
            function ($themeName) {
                return self::isPlainThemeName((string) $themeName);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Returns the URL of the theme tokens (css/theme_tokens.css, the colours of the theme as CSS variables)
     * of the current user's admin theme. The React editor loads this file.
     *
     * @return string|null The URL with the file time as version, or null if the theme has no theme tokens
     */
    public static function getThemeTokensUrl()
    {
        $themeName = self::getUserAdminThemeName() ?? App()->getConfig('admintheme');
        if (!is_string($themeName) || !self::isPlainThemeName($themeName)) {
            return null;
        }
        $tokensFile = self::getThemeRootDir($themeName) . DIRECTORY_SEPARATOR . $themeName
            . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme_tokens.css';
        if (!is_file($tokensFile)) {
            return null;
        }
        $themeUrl = in_array($themeName, self::STANDARD_ADMIN_THEMES, true)
            ? App()->getConfig('styleurl') . $themeName
            : App()->getConfig('uploadurl') . '/admintheme/' . $themeName;
        return $themeUrl . '/css/theme_tokens.css?v=' . filemtime($tokensFile);
    }

    /**
     * Returns the admin theme the current user chose in their personal settings
     *
     * @return string|null The theme name, or null if the user did not choose one, the theme does not exist (anymore)
     *                     or users cannot choose their own admin theme
     */
    public static function getUserAdminThemeName()
    {
        if (App()->user->isGuest || !self::isPersonalAdminThemeAllowed()) {
            return null;
        }
        $themeName = SettingsUser::getUserSettingValue('admintheme');
        // Users can write any of their own settings (e.g. via admin/user/sa/togglesetting),
        // so only accept plain directory names
        if (!is_string($themeName) || !self::isPlainThemeName($themeName)) {
            return null;
        }
        $configFile = self::getThemeRootDir($themeName) . DIRECTORY_SEPARATOR . $themeName
            . DIRECTORY_SEPARATOR . 'config.xml';
        if (!is_file($configFile)) {
            return null;
        }
        return $themeName;
    }

    /**
     * Returns the appearance modes with their translated names
     *
     * @return array<string,string> appearance mode => name
     */
    public static function getAppearanceModeList()
    {
        return [
            'light'  => gT("Light", 'unescaped'),
            'dark'   => gT("Dark", 'unescaped'),
            'system' => gT("System", 'unescaped'),
        ];
    }

    /**
     * Returns the default appearance mode (global setting), used for everyone who did not choose their own
     *
     * @return string One of APPEARANCE_MODES
     */
    public static function getDefaultAppearanceMode()
    {
        $appearanceMode = App()->getConfig('defaultappearancemode');
        return in_array($appearanceMode, self::APPEARANCE_MODES, true) ? $appearanceMode : 'light';
    }

    /**
     * Returns the appearance mode the current user chose in their personal settings
     *
     * @return string|null One of APPEARANCE_MODES, or null if the user did not choose one
     */
    public static function getUserAppearanceMode()
    {
        if (App()->user->isGuest) {
            return null;
        }
        $appearanceMode = SettingsUser::getUserSettingValue('appearancemode');
        // Users can write any of their own settings (e.g. via admin/user/sa/togglesetting)
        return in_array($appearanceMode, self::APPEARANCE_MODES, true) ? $appearanceMode : null;
    }

    /**
     * Returns the appearance mode of the current user: Their personal choice first, then the global setting
     *
     * @return string One of APPEARANCE_MODES
     */
    public static function getAppearanceMode()
    {
        return self::getUserAppearanceMode() ?? self::getDefaultAppearanceMode();
    }

    /**
     * Returns whether this admin theme has a dark mode for the text direction of the current language:
     * CSS files for it are listed in config.xml (files > dark > css, for right-to-left languages
     * files > rtl > dark > css).
     * These are the files registerStylesAndScripts() loads, so a theme with dark CSS for one direction only
     * is not reported as dark in the other direction.
     *
     * @param string|null $language Language code, null for the language of the current request
     * @return bool
     */
    public function hasDarkMode($language = null)
    {
        App()->loadHelper('surveytranslator');
        $files = $this->config->files ?? null;
        $darkFiles = getLanguageRTL($language ?? App()->language) ? ($files->rtl->dark ?? null) : ($files->dark ?? null);
        return $this->getCssFileList($darkFiles) !== [];
    }

    /**
     * Returns the appearance mode this admin theme is shown in:
     * The appearance mode of the current user, or "light" if the theme has no dark mode
     *
     * @param string|null $language Language code, null for the language of the current request
     * @return string One of APPEARANCE_MODES
     */
    public function getActiveAppearanceMode($language = null)
    {
        return $this->hasDarkMode($language) ? self::getAppearanceMode() : 'light';
    }

    /**
     * Returns the appearance mode the admin theme of the current user is shown in, without loading the theme.
     * For code outside the admin pages (the React editor); the admin pages use getActiveAppearanceMode().
     *
     * @return string One of APPEARANCE_MODES, "light" if the admin theme has no dark mode
     */
    public static function getUserThemeAppearanceMode()
    {
        $themeName = self::getUserAdminThemeName() ?? App()->getConfig('admintheme');
        if (!is_string($themeName) || !self::isPlainThemeName($themeName)) {
            return 'light';
        }
        $configFile = self::getThemeRootDir($themeName) . DIRECTORY_SEPARATOR . $themeName
            . DIRECTORY_SEPARATOR . 'config.xml';
        if (!is_file($configFile)) {
            return 'light';
        }
        $theme = new self();
        $theme->config = json_decode(json_encode((array) simplexml_load_string(file_get_contents($configFile)), 1));
        // The request that opens the editor has the default language of the site, the admin pages have the
        // language of the administrator: That one decides which CSS files (left-to-right or right-to-left) they load
        $adminLanguage = App()->session['adminlang'] ?? null;
        return $theme->getActiveAppearanceMode(is_string($adminLanguage) && $adminLanguage !== '' ? $adminLanguage : null);
    }

    /**
     * Returns whether a theme name is a plain directory name: No path, not "." or ".." and no hidden directory
     *
     * @param string $themeName Name of the admin theme, which is the name of its directory
     * @return bool True if the name is a plain directory name
     */
    private static function isPlainThemeName(string $themeName)
    {
        return preg_match('/^[A-Za-z0-9_-][A-Za-z0-9_.-]*$/', $themeName) === 1;
    }

    /**
     * Returns the directory that contains an admin theme:
     * themes/admin for the standard themes, upload/admintheme for the others
     *
     * @param string $themeName Name of the admin theme
     * @return string Path of the directory, without the directory of the theme itself
     */
    private static function getThemeRootDir(string $themeName)
    {
        return in_array($themeName, self::STANDARD_ADMIN_THEMES, true)
            ? App()->getConfig('styledir')
            : App()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'admintheme';
    }

    /**
     * Initializes the admin theme for the current request.
     *
     * Resolves the admin theme name (the user's personal setting first, then the global settings
     * or config-defaults.php), falls back to Sea_Green if the theme directory does not exist (e.g. after an upgrade
     * or if a custom theme was deleted), then:
     *  - Sets \->name, \->path, \->sTemplateUrl
     *  - Parses config.xml into \->config
     *  - Defines theme constants via defineConstants()
     *  - Registers all CSS/JS assets via registerStylesAndScripts()
     *
     * @return AdminTheme \, to allow chaining from getInstance()
     */
    public function setAdminTheme()
    {
        // The user's personal choice wins over the admin theme in config ( {{settings_global}} or config-defaults.php )
        $sAdminThemeName           = self::getUserAdminThemeName() ?? App()->getConfig('admintheme');
        $sStandardTemplateRootDir  = App()->getConfig("styledir"); // Path for the standard Admin Themes
        $sUserTemplateDir          = App()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'admintheme'; // Path for the user Admin Themes

        // Check if the required theme is a standard one
        if ($this->isStandardAdminTheme($sAdminThemeName)) {
            $sTemplateDir = $sStandardTemplateRootDir; // It's standard, so it will be in standard path
            $sTemplateUrl = Yii::app()->getConfig('styleurl') . $sAdminThemeName; // Available via a standard URL
        } else {
            // If it's not a standard theme, we bet it's a user one.
            // In fact, it could also be a old 2.06 admin theme just aftet an update (it will then be caught as "non existent" in the next if statement")
            $sTemplateDir = $sUserTemplateDir;
            $sTemplateUrl = Yii::app()->getConfig('uploadurl') . DIRECTORY_SEPARATOR . 'admintheme' . DIRECTORY_SEPARATOR . $sAdminThemeName;
        }

        // If the theme directory doesn't exist, it can be that:
        // - user updated from 2.06 and still have old theme configured in database
        // - user deleted a custom theme
        // In any case, we just set Sea Green as the template to use
        if (!is_dir($sTemplateDir . DIRECTORY_SEPARATOR . $sAdminThemeName)) {
            $sAdminThemeName   = 'Sea_Green';
            $sTemplateDir      = $sStandardTemplateRootDir;
            $sTemplateUrl      = Yii::app()->getConfig('styleurl') . DIRECTORY_SEPARATOR . $sAdminThemeName;
            SettingGlobal::setSetting('admintheme', 'Sea_Green');
        }

        // Now that we are sure we have an existing template, we can set the variables of the AdminTheme
        $this->sTemplateUrl = $sTemplateUrl;
        $this->name         = $sAdminThemeName;
        $this->path         = $sTemplateDir . DIRECTORY_SEPARATOR . $this->name;

        // This is necessary because a lot of files still use "adminstyleurl".
        // TODO: replace everywhere the call to Yii::app()->getConfig('adminstyleurl) by $oAdminTheme->sTemplateUrl;
        Yii::app()->setConfig('adminstyleurl', $this->sTemplateUrl);


        //////////////////////
        // Config file loading

        $sXMLConfigFile        = file_get_contents(realpath($this->path . '/config.xml')); // Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

        // Simple Xml is buggy on PHP < 5.4. The [ array -> json_encode -> json_decode ] workaround seems to be the most used one.
        // @see: http://php.net/manual/de/book.simplexml.php#105330 (top comment on PHP doc for simplexml)
        $this->config = json_decode(json_encode((array) simplexml_load_string($sXMLConfigFile), 1));

        // If developers want to test asset manager with debug mode on
        self::$use_asset_manager = isset($this->config->engine->use_asset_manager_in_debug_mode) ? ($this->config->engine->use_asset_manager_in_debug_mode == 'true') : false;

        $this->defineConstants(); // Define the (still) necessary constants
        $this->registerStylesAndScripts(); // Register all CSS and JS
        return $this;
    }

    /**
     * Load the default admin interface CSS and JavaScript Packages including the admin_theme
     *
     * Register all the styles and scripts of the current template.
     * Check if RTL is needed, use asset manager if needed.
     * This function is public because it appears that sometime, the package need to be register again in header (probably a cache problem)
     *
     * @return void
     */
    public function registerStylesAndScripts()
    {
        // First we register the different needed packages

        if (!Yii::app()->request->getQuery('isAjax', false)) {
            Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport');
            App()->getClientScript()->registerScript('coreuser', '
           window.LS = window.LS || {}; window.LS.globalUserId = "' . Yii::app()->user->id . '";', CClientScript::POS_HEAD);
            App()->getClientScript()->registerPackage('jquery-migrate'); // jquery + migrate
            App()->getClientScript()->registerPackage('jqueryui'); // Added for nestedSortable to work (question organizer)
            App()->getClientScript()->registerPackage('js-cookie'); // js-cookie
            App()->getClientScript()->registerPackage('fontawesome'); // fontawesome
            App()->getClientScript()->registerPackage('font-ibm-sans'); // font-ibm-sans
            App()->getClientScript()->registerPackage('font-ibm-serif'); // font-ibm-serif
            App()->getClientScript()->registerPackage('remix'); // remix
            //            App()->getClientScript()->registerPackage('bootstrap-switch');
            App()->getClientScript()->registerPackage('tempus-dominus');
            //            App()->getClientScript()->registerPackage('bootstrap-datetimepicker');
            App()->getClientScript()->registerPackage('font-roboto');
            App()->getClientScript()->registerPackage('font-icomoon');
            App()->getClientScript()->registerPackage('adminbasics'); // Combined scripts and style
            App()->getClientScript()->registerPackage('adminsidepanel'); // The new admin panel
            // CKEditor (and the modaleditor, which depends on it) is only registered on pages
            // that actually use an editor, via PrepareEditorScript(). See bug #19391.
        }
        App()->getClientScript()->registerPackage('select2-bootstrap');
        $this->registerSelect2Translations();
        // Then we add the different CSS/JS files to load in arrays
        // It will check if it needs or not the RTL files
        // and it will add the directory prefix to the file name (css/ or js/ )
        // This last step is needed for the package (yii package use a single baseUrl / basePath for css and js files )

        // Shorter writing.
        $files = $this->config->files;
        // We check if RTL is needed
        if (getLanguageRTL(Yii::app()->language)) {
            // RTL style
            if (
                !isset($files->rtl)
                || !isset($files->rtl->css)
            ) {
                throw new CException("Invalid template configuration: No CSS files found for right-to-left languages");
            }

            App()->getClientScript()->registerPackage('font-roboto');
            $this->registerAdminTheme($files, $this->getCssFileList($files->rtl), $this->getCssFileList($files->rtl->dark ?? null));
            App()->getClientScript()->registerPackage('adminsidepanelrtl');
        } else {
            // LTR style
            $this->registerAdminTheme($files, $this->getCssFileList($files), $this->getCssFileList($files->dark ?? null));
            App()->getClientScript()->registerPackage('adminsidepanelltr');
        }
        App()->getClientScript()->registerPackage('bootstrap-js');
        App()->clientScript->registerPackage('moment'); // register moment for correct dateTime calculation
    }

    /**
     * Makes Select2 show its own messages (e.g. "No results found") in the admin language.
     *
     * Select2 only ships language files for a part of our languages, so the messages come from our own
     * translations and are set as the Select2 default language. The script is placed in the head: There it
     * runs after the Select2 script and before any dropdown is initialised.
     *
     * @return void
     */
    private function registerSelect2Translations()
    {
        // Select2 escapes the messages itself, except "loadingMore", which it inserts as HTML.
        // "%s" is replaced with a number in the browser.
        $messages = [
            'errorLoading'    => gT('The results could not be loaded.', 'unescaped'),
            'inputTooLong'    => gT('Please delete %s or more characters', 'unescaped'),
            'inputTooShort'   => gT('Please enter %s or more characters', 'unescaped'),
            'loadingMore'     => gT('Loading more results...'),
            'maximumSelected' => gT('Maximum number of items you can select: %s', 'unescaped'),
            'noResults'       => gT('No results found', 'unescaped'),
            'searching'       => gT('Searching...', 'unescaped'),
            'removeAllItems'  => gT('Remove all items', 'unescaped'),
            'removeItem'      => gT('Remove item', 'unescaped'),
            'search'          => gT('Search', 'unescaped'),
        ];
        $messagesJson = json_encode($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $script = <<<JS
(function ($) {
    if (!$.fn.select2) {
        return;
    }
    var messages = $messagesJson;
    var fixed = function (key) {
        return function () {
            return messages[key];
        };
    };
    var counted = function (key, getCount) {
        return function (args) {
            return messages[key].replace('%s', getCount(args));
        };
    };
    $.fn.select2.defaults.set('language', {
        errorLoading: fixed('errorLoading'),
        inputTooLong: counted('inputTooLong', function (args) {
            return args.input.length - args.maximum;
        }),
        inputTooShort: counted('inputTooShort', function (args) {
            return args.minimum - args.input.length;
        }),
        loadingMore: fixed('loadingMore'),
        maximumSelected: counted('maximumSelected', function (args) {
            return args.maximum;
        }),
        noResults: fixed('noResults'),
        searching: fixed('searching'),
        removeAllItems: fixed('removeAllItems'),
        removeItem: fixed('removeItem'),
        search: fixed('search')
    });
})(jQuery);
JS;
        App()->getClientScript()->registerScript('select2translations', $script, CClientScript::POS_HEAD);
    }

    /**
     * Returns the CSS files listed in a part of config.xml, with the "css/" directory
     * (Yii packages use a single base URL / base path for their CSS and JS files)
     *
     * @param object|null $files A node of config.xml that contains the CSS files as css > filename
     *                           (files, files > rtl, files > dark or files > rtl > dark)
     * @return string[]
     */
    private function getCssFileList($files)
    {
        $fileNames = $files->css->filename ?? [];
        if (is_string($fileNames)) {
            $fileNames = [$fileNames];
        }
        $cssFiles = [];
        if (is_array($fileNames)) {
            foreach ($fileNames as $fileName) {
                if (is_string($fileName) && $fileName !== '') {
                    $cssFiles[] = 'css/' . $fileName;
                }
            }
        }
        return $cssFiles;
    }

    /**
     * Register the admin-theme package, and the admin-theme-dark package if the dark mode is used.
     *
     * The dark mode CSS files are loaded after the other CSS files of the theme, only for screens:
     * always in the "dark" appearance mode, and if the device of the user is in dark mode in the "system" appearance mode.
     *
     * @param object $files The files node of config.xml
     * @param string[] $aCssFiles The CSS files of the theme, with the "css/" directory
     * @param string[] $darkCssFiles The dark mode CSS files of the theme, with the "css/" directory
     * @return void
     */
    private function registerAdminTheme($files, $aCssFiles, $darkCssFiles = [])
    {
        $aJsFiles = [];
        if (!empty($files->js->filename)) {
            if (is_array($files->js->filename)) {
                foreach ($files->js->filename as $jsfile) {
                    $aJsFiles[] = 'scripts/' . $jsfile; // add the 'js/' prefix to the js files
                }
            } elseif (is_string($files->js->filename)) {
                $aJsFiles[] = 'scripts/' . $files->js->filename;
            }
        }

        $package = [];
        $package['css'] = $aCssFiles; // add the css files to the package
        $package['js'] = $aJsFiles; // add the js files to the package

        // We check if the asset manager should be use.
        // When defining the package with a base path (a directory on the file system), the asset manager is used
        // When defining the package with a base url, the file is directly registered without the asset manager
        // See : http://www.yiiframework.com/doc/api/1.1/CClientScript#packages-detail
        $packageBase = [];
        if (!YII_DEBUG || self::$use_asset_manager || App()->getConfig('use_asset_manager')) {
            Yii::setPathOfAlias('admin.theme.path', $this->path);
            $packageBase['basePath'] = 'admin.theme.path'; // add the base path to the package, so it will use the asset manager
        } else {
            $packageBase['baseUrl'] = $this->sTemplateUrl; // add the base url to the package, so it will not use the asset manager
        }

        App()->clientScript->addPackage('admin-theme', $package + $packageBase); // add the package
        App()->clientScript->registerPackage('admin-theme'); // register the package

        $this->registerAppearanceMode($darkCssFiles, $packageBase);
    }

    /**
     * Registers what the appearance mode of the current user needs: The dark mode CSS files of the theme,
     * the "color-scheme" meta tag and a script that sets the data-bs-theme attribute of the html element
     * to "light" or "dark" (for scripts and CSS that need to know the mode in use, e.g. the date picker).
     *
     * @param string[] $darkCssFiles The dark mode CSS files of the theme, with the "css/" directory
     * @param array $packageBase The base path or base URL of the theme, as part of a package definition
     * @return void
     */
    private function registerAppearanceMode($darkCssFiles, $packageBase)
    {
        $appearanceMode = $darkCssFiles === [] ? 'light' : self::getAppearanceMode();
        $systemMode = $appearanceMode === 'system';
        $darkMediaQuery = '(prefers-color-scheme: dark)';

        if ($appearanceMode !== 'light') {
            $package = $packageBase;
            $package['css'] = $darkCssFiles;
            // Not for print: Browsers do not print backgrounds by default, so the light text would be unreadable
            $package['cssMedia'] = $systemMode ? 'screen and ' . $darkMediaQuery : 'screen';
            $package['depends'] = ['admin-theme'];
            App()->clientScript->addPackage('admin-theme-dark', $package);
            App()->clientScript->registerPackage('admin-theme-dark');
        }

        if (App()->request->getQuery('isAjax', false)) {
            return;
        }
        if ($appearanceMode !== 'light') {
            // Dark browser background and form controls from the start, before the CSS files are loaded
            App()->clientScript->registerMetaTag($systemMode ? 'light dark' : 'dark', 'color-scheme', null, [], 'color-scheme');
        }
        $script = $systemMode
            ? '(function () {
    var darkMode = window.matchMedia(' . json_encode($darkMediaQuery) . ');
    var setTheme = function () {
        document.documentElement.setAttribute("data-bs-theme", darkMode.matches ? "dark" : "light");
    };
    document.documentElement.setAttribute("data-appearance-mode", "system");
    setTheme();
    if (darkMode.addEventListener) {
        darkMode.addEventListener("change", setTheme);
    }
})();'
            : 'document.documentElement.setAttribute("data-appearance-mode", ' . json_encode($appearanceMode) . ');
document.documentElement.setAttribute("data-bs-theme", ' . json_encode($appearanceMode) . ');';
        App()->clientScript->registerScript('appearancemode', $script, CClientScript::POS_HEAD);
    }

    /**
     * Removes the appearance mode from the page that is rendered next: The dark CSS files, the script that sets
     * data-bs-theme and the color-scheme meta tag.
     * For HTML that is not an admin page but is rendered during an admin request, like the preview of the theme editor.
     *
     * @return void
     */
    public static function unregisterAppearanceMode()
    {
        App()->clientScript->unregisterPackage('admin-theme-dark');
        App()->clientScript->unregisterScript('appearancemode', CClientScript::POS_HEAD);
        App()->clientScript->unregisterMetaTag('color-scheme');
    }

    /**
     * Returns the path of a file in the "images" directory of the theme.
     * A theme does not need to have its own images: If it has no such file, the file of Sea Green is used.
     *
     * @param string $fileName Name of the file in the "images" directory, e.g. "limesurvey.pal"
     * @return string The full path of the file
     */
    public function getImagePath($fileName)
    {
        $themeFile = $this->path . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($themeFile)) {
            return $themeFile;
        }
        return App()->getConfig('styledir') . DIRECTORY_SEPARATOR . 'Sea_Green' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Returns the singleton AdminTheme instance, creating and initializing it on first call.
     *
     * Prefer this over direct instantiation or global variables to ensure
     * the theme is only loaded once per request.
     *
     * @return AdminTheme
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
            self::$instance->setAdminTheme();
        }
        return self::$instance;
    }


    /**
     * Return an array containing the configuration object of all templates in a given directory
     *
     * @param string $sDir          the directory to scan
     * @return array                the array of object
     */
    private static function getThemeList($sDir)
    {
        $aListOfFiles = array();
        $oAdminTheme = new AdminTheme();
        if ($sDir && $pHandle = opendir($sDir)) {
            while (false !== ($file = readdir($pHandle))) {
                if (is_dir($sDir . DIRECTORY_SEPARATOR . $file) && is_file($sDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . 'config.xml')) {
                    $sXMLConfigFile = file_get_contents(realpath($sDir . DIRECTORY_SEPARATOR . $file . '/config.xml')); // Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

                    // Simple Xml is buggy on PHP < 5.4. The [ array -> json_encode -> json_decode ] workaround seems to be the most used one.
                    // @see: http://php.net/manual/de/book.simplexml.php#105330 (top comment on PHP doc for simplexml)
                    $oTemplateConfig = json_decode(json_encode((array) simplexml_load_string($sXMLConfigFile), 1));
                    if ($oAdminTheme->isStandardAdminTheme($file)) {
                        $previewUrl = Yii::app()->getConfig('styleurl') . $file;
                    } else {
                        $previewUrl = Yii::app()->getConfig('uploadurl') . DIRECTORY_SEPARATOR . 'admintheme' . DIRECTORY_SEPARATOR . $file;
                    }
                    $oTemplateConfig->path    = $sDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR;
                    $oTemplateConfig->name    = $file;
                    $oTemplateConfig->preview = '<img src="' . $previewUrl . '/preview.png" alt="admin theme preview" height="200" class="img-thumbnail" />';
                    $aListOfFiles[$file] = $oTemplateConfig;
                }
            }
            closedir($pHandle);
        }
        return $aListOfFiles;
    }


    /**
     * Few constants depending on Template
     */
    private function defineConstants()
    {
        // Define images url
        if (!YII_DEBUG || self::$use_asset_manager || Yii::app()->getConfig('use_asset_manager')) {
            if (file_exists($this->path . '/images/logo.svg')) {
                define('LOGO_URL', App()->getAssetManager()->publish($this->path . '/images/logo.svg'));
            } else {
                define('LOGO_URL', App()->getAssetManager()->publish(App()->getConfig("styledir") . '/Sea_Green/images/logo.svg'));
            }
            if (file_exists($this->path . '/images/logo_icon.png')) {
                define('LOGO_ICON_URL', App()->getAssetManager()->publish($this->path . '/images/logo_icon.png'));
            } else {
                define('LOGO_ICON_URL', App()->getAssetManager()->publish(App()->getConfig("styledir") . '/Sea_Green/images/logo_icon.png'));
            }
        } else {
            if (file_exists($this->path . '/images/logo.svg')) {
                define('LOGO_URL', $this->sTemplateUrl . '/images/logo.svg');
            } else {
                define('LOGO_URL', App()->getConfig('styleurl') . '/Sea_Green/images/logo.svg');
            }
            if (file_exists($this->path . '/images/logo_icon.png')) {
                define('LOGO_ICON_URL', $this->sTemplateUrl . '/images/logo_icon.png');
            } else {
                define('LOGO_ICON_URL', App()->getConfig('styleurl') . '/Sea_Green/images/logo_icon.png');
            }
        }

        // Define presentation text on welcome page
        if (isset($this->config->metadata->presentation) && $this->config->metadata->presentation) {
            define('PRESENTATION', $this->config->metadata->presentation);
        } else {
            define('PRESENTATION', gT('This is the LimeSurvey admin interface. Start to build your survey from here.'));
        }
    }

    /**
     * Use to check if admin theme is standard
     *
     * @param string $sAdminThemeName the name of the template
     * @return boolean                  return true if it's a standard template, else false
     */
    private function isStandardAdminTheme($sAdminThemeName)
    {
        return in_array($sAdminThemeName, self::STANDARD_ADMIN_THEMES, true);
    }
}
