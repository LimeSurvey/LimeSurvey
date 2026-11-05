<?php

namespace ls\tests;

use LimeSurvey\Models\Services\CaptchaService;

/**
 * CaptchaService: resolving the CAPTCHA type of a survey (with inheritance) and failing closed without a plugin.
 *
 * @group services
 */
class CaptchaServiceTest extends TestBaseClass
{
    /** @var boolean|null Activation state of the plugin before the test, null if not installed */
    private static $pluginWasActive;

    /** @var \CoreCaptcha */
    private static $plugin;

    /** @var array Plugin settings before the test, restored afterwards */
    private static $settingsBefore = [];

    /** @var string Global survey settings CAPTCHA type before the test, restored afterwards */
    private static $globalCaptchaTypeBefore;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $plugin = \Plugin::model()->findByAttributes(['name' => 'CoreCaptcha']);
        if ($plugin) {
            self::$pluginWasActive = (bool) $plugin->active;
        } else {
            $plugin = new \Plugin();
            $plugin->name = 'CoreCaptcha';
            $plugin->plugin_type = 'core';
        }
        $plugin->active = 1;
        $plugin->save();
        self::$plugin = App()->getPluginManager()->loadPlugin('CoreCaptcha', $plugin->id);
        foreach (self::$plugin->getPluginSettings() as $name => $setting) {
            if (($setting['type'] ?? '') !== 'separator') {
                self::$settingsBefore[$name] = $setting['current'];
            }
        }
        self::$globalCaptchaTypeBefore = \SurveysGroupsettings::model()->findByPk(0)->captchatype;

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_186734.lss');
    }

    public static function tearDownAfterClass(): void
    {
        self::setGlobalCaptchaType(self::$globalCaptchaTypeBefore);
        self::$plugin->saveSettings(self::$settingsBefore);
        if (self::$pluginWasActive === false) {
            self::deActivatePlugin('CoreCaptcha');
        }
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::$plugin->saveSettings(['altchaEnabled' => 1, 'classicEnabled' => 1]);
        CaptchaService::reset();
    }

    /**
     * Set the CAPTCHA type in the global survey settings and drop the cached group settings
     *
     * @param string $type
     * @return void
     */
    private static function setGlobalCaptchaType(string $type): void
    {
        \SurveysGroupsettings::model()->updateByPk(0, ['captchatype' => $type]);
        // SurveysGroupsettings::getInstance() caches the loaded settings for the request
        $cache = new \ReflectionProperty(\SurveysGroupsettings::class, 'aSurveysGroupSettings');
        $cache->setAccessible(true);
        $cache->setValue(null, []);
    }

    /**
     * Set the CAPTCHA type of the test survey and reload it, so the options are resolved like at runtime
     *
     * @param string $type
     * @return \Survey
     */
    private function surveyWithCaptchaType(string $type): \Survey
    {
        \Survey::model()->updateByPk(self::$surveyId, ['captchatype' => $type]);
        \Survey::model()->resetCache();
        return \Survey::model()->findByPk(self::$surveyId);
    }

    /**
     * A survey set to inherit uses the global CAPTCHA type, an own value wins over it
     */
    public function testCaptchaTypeIsInherited()
    {
        $service = new CaptchaService();

        self::setGlobalCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
        $survey = $this->surveyWithCaptchaType(CaptchaService::INHERIT);
        $this->assertSame(\CoreCaptcha::TYPE_CLASSIC, $service->getConfiguredProvider($survey), 'The inherited global type is the configured type');
        $this->assertSame(self::isClassicCaptchaAvailable() ? \CoreCaptcha::TYPE_CLASSIC : \CoreCaptcha::TYPE_ALTCHA, $service->resolveProvider($survey));

        self::setGlobalCaptchaType(\CoreCaptcha::TYPE_ALTCHA);
        $survey = $this->surveyWithCaptchaType(CaptchaService::INHERIT);
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $service->resolveProvider($survey));
        $this->assertTrue($service->isCaptchaActive($survey));

        self::setGlobalCaptchaType(CaptchaService::NONE);
        $survey = $this->surveyWithCaptchaType(CaptchaService::INHERIT);
        $this->assertSame(CaptchaService::NONE, $service->resolveProvider($survey));
        $this->assertFalse($service->isCaptchaActive($survey));
        \Survey::model()->updateByPk(self::$surveyId, ['usecaptcha' => 'A']);
        \Survey::model()->resetCache();
        $this->assertFalse(\Survey::model()->findByPk(self::$surveyId)->isCaptchaEnabled('surveyaccessscreen'), 'Type "none" switches the CAPTCHA off');

        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_ALTCHA);
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $service->getConfiguredProvider($survey), 'The survey\'s own type wins over the global one');
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $service->resolveProvider($survey));
    }

    /**
     * The classic CAPTCHA draws its image with GD and FreeType, the same rule the plugin applies
     *
     * @return bool
     */
    private static function isClassicCaptchaAvailable(): bool
    {
        return extension_loaded('gd') && function_exists('imagettftext');
    }

    /**
     * A configured type that no active plugin offers falls back to the global type, else the first offered type
     */
    public function testUnavailableTypeFallsBack()
    {
        $service = new CaptchaService();
        $survey = $this->surveyWithCaptchaType('no_such_captcha');
        $this->assertSame('no_such_captcha', $service->getConfiguredProvider($survey));

        self::setGlobalCaptchaType(CaptchaService::NONE);
        CaptchaService::reset();
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $service->getDefaultProvider(), 'Alphabetically first offered type');
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $service->resolveProvider($survey));

        if (!self::isClassicCaptchaAvailable()) {
            // Without GD and FreeType there is no second core type to fall back to
            return;
        }
        self::setGlobalCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
        CaptchaService::reset();
        $this->assertSame(\CoreCaptcha::TYPE_CLASSIC, $service->getDefaultProvider(), 'The global type when it is offered');
        $this->assertSame(\CoreCaptcha::TYPE_CLASSIC, $service->resolveProvider($survey));

        self::setGlobalCaptchaType(CaptchaService::NONE);
        self::$plugin->saveSettings(['altchaEnabled' => 0]);
        CaptchaService::reset();
        $this->assertSame(\CoreCaptcha::TYPE_CLASSIC, $service->resolveProvider($survey), 'The only offered type');
    }

    /**
     * Without any offered CAPTCHA type the survey falls back to no CAPTCHA
     */
    public function testFallsBackToNoneWithoutProvider()
    {
        self::$plugin->saveSettings(['altchaEnabled' => 0, 'classicEnabled' => 0]);
        CaptchaService::reset();
        $service = new CaptchaService();
        $this->assertSame([], $service->getProviders());
        $this->assertNull($service->getDefaultProvider());

        \Survey::model()->updateByPk(self::$surveyId, ['usecaptcha' => 'A']);
        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_ALTCHA);
        $warningsBefore = count(\Yii::getLogger()->getLogs('warning', 'application.captcha'));
        $this->assertSame(CaptchaService::NONE, $service->resolveProvider($survey));
        $this->assertSame(CaptchaService::NONE, $service->resolveProvider($survey));
        $this->assertCount($warningsBefore + 1, \Yii::getLogger()->getLogs('warning', 'application.captcha'), 'The administrator is warned once per request');
        $this->assertFalse($service->isCaptchaActive($survey));
        $this->assertFalse($survey->isCaptchaEnabled('registrationscreen'), 'The CAPTCHA switches are ignored without a CAPTCHA type');

        $rendered = $service->render($survey, CaptchaService::SCREEN_REGISTRATION);
        $this->assertFalse($rendered['show']);
        $this->assertSame(CaptchaService::NONE, $rendered['provider']);
        $this->assertSame('', $rendered['html']);

        $_POST = [];
        $this->assertTrue($service->validate($survey, CaptchaService::SCREEN_REGISTRATION), 'Nothing to validate without a CAPTCHA');
        $_POST = [];
    }

    /**
     * The rendered widget is cached for the request and available to the deprecated twig function
     */
    public function testRenderIsCachedPerScreen()
    {
        $service = new CaptchaService();
        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_ALTCHA);

        $first = $service->render($survey, CaptchaService::SCREEN_SURVEY_ACCESS);
        $second = $service->render($survey, CaptchaService::SCREEN_SURVEY_ACCESS);
        $this->assertSame($first['html'], $second['html']);
        $this->assertStringStartsWith('<input type="hidden" value="1" name="' . CaptchaService::SUBMITTED_FIELD . '"', $first['html']);
        $this->assertSame($first, CaptchaService::getLastRendered());

        $other = $service->render($survey, CaptchaService::SCREEN_SAVE_AND_LOAD);
        $this->assertTrue($other['show']);
        $this->assertSame($other, CaptchaService::getLastRendered());
    }
}
