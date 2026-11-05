<?php

namespace ls\tests;

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\SolveChallengeOptions;
use CaptchaExtendedAction;
use LimeSurvey\Models\Services\CaptchaService;

/**
 * CoreCaptcha plugin: the ALTCHA and classic CAPTCHA types rendered and validated through the CAPTCHA events.
 *
 * @group plugins
 */
class CoreCaptchaTest extends TestBaseClass
{
    /** @var boolean|null Activation state of the plugin before the test, null if not installed */
    private static $pluginWasActive;

    /** @var \CoreCaptcha */
    private static $plugin;

    /** @var array Plugin settings before the test, restored afterwards */
    private static $settingsBefore = [];

    /**
     * Activate the plugin and import a survey
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $_POST = [];

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

        self::importSurvey(self::$surveysFolder . '/limesurvey_survey_186734.lss');
        \Yii::app()->setController(new CoreCaptchaTestController('dummyid'));
    }

    /**
     * Restore the plugin settings and activation state
     */
    public static function tearDownAfterClass(): void
    {
        self::$plugin->saveSettings(self::$settingsBefore);
        if (self::$pluginWasActive === false) {
            self::deActivatePlugin('CoreCaptcha');
        }
        $_POST = [];
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $_POST = [];
        self::$plugin->saveSettings([
            'altchaEnabled' => 1,
            'classicEnabled' => 1,
            'altchaDifficulty' => 'low',
            'altchaExpiryMinutes' => 60,
        ]);
        CaptchaService::reset();
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
     * Both core types are offered with a label and a description
     */
    public function testCaptchaTypesAreOffered()
    {
        $providers = (new CaptchaService())->getProviders();
        $this->assertArrayHasKey(\CoreCaptcha::TYPE_ALTCHA, $providers);
        $this->assertNotEmpty($providers[\CoreCaptcha::TYPE_ALTCHA]['label']);
        $this->assertNotEmpty($providers[\CoreCaptcha::TYPE_ALTCHA]['description']);
        $this->assertSame(self::isClassicCaptchaAvailable(), array_key_exists(\CoreCaptcha::TYPE_CLASSIC, $providers));
        if (self::isClassicCaptchaAvailable()) {
            $this->assertNotEmpty($providers[\CoreCaptcha::TYPE_CLASSIC]['description']);
        }
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
     * A type switched off in the plugin settings is not offered any more
     */
    public function testTypesCanBeSwitchedOff()
    {
        self::$plugin->saveSettings(['classicEnabled' => 0]);
        CaptchaService::reset();
        $providers = (new CaptchaService())->getProviders();
        $this->assertArrayHasKey(\CoreCaptcha::TYPE_ALTCHA, $providers);
        $this->assertArrayNotHasKey(\CoreCaptcha::TYPE_CLASSIC, $providers);

        self::$plugin->saveSettings(['classicEnabled' => 1, 'altchaEnabled' => 0]);
        CaptchaService::reset();
        $providers = (new CaptchaService())->getProviders();
        $this->assertArrayNotHasKey(\CoreCaptcha::TYPE_ALTCHA, $providers);
        $this->assertSame(self::isClassicCaptchaAvailable(), array_key_exists(\CoreCaptcha::TYPE_CLASSIC, $providers));
    }

    /**
     * A solved ALTCHA challenge is accepted once, a replayed or tampered payload and a missing payload are refused
     */
    public function testAltchaRoundTrip()
    {
        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_ALTCHA);
        $service = new CaptchaService();

        $rendered = $service->render($survey, CaptchaService::SCREEN_SURVEY_ACCESS);
        $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, $rendered['provider']);
        $this->assertStringContainsString('<altcha-widget', $rendered['html']);
        $this->assertStringContainsString('name="altcha"', $rendered['html']);
        $this->assertStringContainsString('name="' . CaptchaService::SUBMITTED_FIELD . '"', $rendered['html']);
        $this->assertStringContainsString('<altcha-widget', $rendered['legacyHtml']);
        $this->assertStringContainsString('loadsecurity', $rendered['legacyHtml'], 'Older themes get their classic answer field hidden');
        $this->assertNull($rendered['label']);
        $this->assertSame(1, preg_match('/ challenge="([^"]+)"/', $rendered['html'], $matches), 'The widget gets a challenge url');
        $this->assertStringContainsString('plugins/direct', html_entity_decode($matches[1], ENT_QUOTES));

        // The challenge the widget fetches from the plugin
        $challengeData = self::$plugin->createAltchaChallenge()->toArray();
        $this->assertSame('PBKDF2/SHA-256', $challengeData['parameters']['algorithm']);
        $this->assertSame(\CoreCaptcha::ALTCHA_COSTS['low'], $challengeData['parameters']['cost']);
        $this->assertNotEmpty($challengeData['signature']);

        // Solve the challenge like the widget does (the secret is only needed for signing, not for solving)
        $challenge = Challenge::fromArray($challengeData);
        $solution = (new Altcha(''))->solveChallenge(new SolveChallengeOptions(
            algorithm: new Pbkdf2(),
            challenge: $challenge,
        ));
        $this->assertNotNull($solution, 'The challenge could not be solved');
        $payload = (new Payload($challenge, $solution))->toBase64();

        $_POST = [];
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS), 'A missing payload must be refused');
        $this->assertNotEmpty($service->getErrorMessage());

        $_POST['altcha'] = $payload;
        $this->assertTrue($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS), 'The solved challenge must be accepted');
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS), 'A replayed payload must be refused');
        $used = (array) App()->session['coreCaptchaUsedAltchaChallenges'];
        $this->assertCount(1, $used);
        $this->assertGreaterThan(time(), reset($used), 'Used challenges are remembered until they expire');

        $tampered = json_decode(base64_decode($payload), true);
        $tampered['challenge']['parameters']['salt'] = bin2hex(random_bytes(16));
        $_POST['altcha'] = base64_encode(json_encode($tampered));
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS), 'A tampered payload must be refused');

        $_POST['altcha'] = 'not a payload';
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS), 'Garbage must be refused');
        $this->assertNotEmpty($service->getErrorMessage());
    }

    /**
     * The widget behind the deprecated twig function renderCaptcha() only returns the plugin markup, never an
     * image of its own that would not be validated
     */
    public function testLegacyWidgetOnlyReturnsPluginMarkup()
    {
        $widget = \Yii::app()->getController()->createWidget('LSCaptcha');
        $this->assertSame('', $widget->renderOut());
        $widget->prerenderedHtml = '<span class="from-plugin"></span>';
        $this->assertSame('<span class="from-plugin"></span>', $widget->renderOut());
    }

    /**
     * The classic CAPTCHA on the save/load screen keeps the image action and the answer in the survey session
     */
    public function testClassicSaveAndLoadScreen()
    {
        if (!self::isClassicCaptchaAvailable()) {
            // Without GD and FreeType the classic type is not offered, a survey set to it gets ALTCHA instead
            $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
            $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, (new CaptchaService())->resolveProvider($survey));
            return;
        }
        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
        $service = new CaptchaService();

        $rendered = $service->render($survey, CaptchaService::SCREEN_SAVE_AND_LOAD);
        $this->assertSame(\CoreCaptcha::TYPE_CLASSIC, $rendered['provider']);
        $this->assertStringContainsString('verification/image', $rendered['html']);
        $this->assertStringContainsString('name="loadsecurity"', $rendered['html']);
        $this->assertSame('loadsecurity', $rendered['inputId']);
        $this->assertNotEmpty($rendered['label']);
        $this->assertStringContainsString('verification/image', $rendered['sImageUrl'], 'Older themes still get the image url');

        $_SESSION['responses_' . self::$surveyId]['secanswer'] = 42;
        $_POST['loadsecurity'] = '';
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SAVE_AND_LOAD));
        $this->assertNotEmpty($service->getErrorMessage());
        $_POST['loadsecurity'] = '41';
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SAVE_AND_LOAD));
        $_POST['loadsecurity'] = ' 42 ';
        $this->assertTrue($service->validate($survey, CaptchaService::SCREEN_SAVE_AND_LOAD));
        unset($_SESSION['responses_' . self::$surveyId]);
    }

    /**
     * The classic CAPTCHA on the survey access screen uses the captcha action of the controller
     */
    public function testClassicSurveyAccessScreen()
    {
        if (!self::isClassicCaptchaAvailable()) {
            // Without GD and FreeType the classic type is not offered, a survey set to it gets ALTCHA instead
            $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
            $this->assertSame(\CoreCaptcha::TYPE_ALTCHA, (new CaptchaService())->resolveProvider($survey));
            return;
        }
        $survey = $this->surveyWithCaptchaType(\CoreCaptcha::TYPE_CLASSIC);
        $service = new CaptchaService();

        $rendered = $service->render($survey, CaptchaService::SCREEN_SURVEY_ACCESS);
        $this->assertStringContainsString('<img', $rendered['html']);
        $this->assertStringContainsString('id="reloadCaptcha"', $rendered['html']);
        $this->assertStringContainsString('name="' . CaptchaService::SUBMITTED_FIELD . '"', $rendered['legacyHtml']);
        $this->assertStringContainsString('<img', $rendered['legacyHtml']);
        $this->assertStringNotContainsString('id="reloadCaptcha"', $rendered['legacyHtml'], 'Older themes render the reload button themselves');
        $this->assertStringNotContainsString('name="loadsecurity"', $rendered['legacyHtml'], 'Older themes render the answer field themselves');
        $this->assertSame('loadsecurity', $rendered['inputId']);

        /** @var CaptchaExtendedAction $action */
        $action = \Yii::app()->getController()->createAction('captcha');
        $expected = $action->getVerifyResult();
        $_POST['loadsecurity'] = (string) ($expected + 1);
        $this->assertFalse($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS));
        $_POST['loadsecurity'] = (string) $expected;
        $this->assertTrue($service->validate($survey, CaptchaService::SCREEN_SURVEY_ACCESS));
    }
}

/**
 * Controller with the captcha action, like SurveyController and RegisterController
 */
class CoreCaptchaTestController extends DummyController
{
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'CaptchaExtendedAction',
                'mode' => CaptchaExtendedAction::MODE_MATH,
            ],
        ];
    }
}
