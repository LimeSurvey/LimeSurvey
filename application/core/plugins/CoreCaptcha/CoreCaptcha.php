<?php

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\VerifySolutionOptions;
use LimeSurvey\Models\Services\CaptchaService;
use LimeSurvey\PluginManager\CaptchaPluginBase;

/**
 * Core plugin offering the CAPTCHA types shipped with LimeSurvey:
 *  - altcha: the accessible ALTCHA proof-of-work CAPTCHA (self-hosted, no external service)
 *  - classic: the classic image CAPTCHA (an equation drawn as an image, not accessible)
 *
 * The ALTCHA widget (MIT, https://altcha.org) is bundled in assets/, the challenges are created
 * and verified with the official PHP library (altcha-org/altcha).
 */
class CoreCaptcha extends CaptchaPluginBase
{
    protected static $name = 'CoreCaptcha';
    protected static $description = 'Core: CAPTCHA types for survey access, registration and save/load - the accessible ALTCHA proof-of-work CAPTCHA and the classic image CAPTCHA';

    /** @inheritdoc */
    protected $storage = 'DbStorage';

    /** @inheritdoc This plugin has no public methods */
    public $allowedPublicMethods = [];

    public const TYPE_ALTCHA = 'altcha';
    public const TYPE_CLASSIC = 'classic';

    /** Name of the form field carrying the ALTCHA payload */
    public const ALTCHA_FIELD = 'altcha';
    /** Function name of the direct request (plugins/direct) the widget fetches a challenge from */
    public const ALTCHA_CHALLENGE_FUNCTION = 'altchaChallenge';
    /** Name of the form field carrying the classic CAPTCHA answer (kept for custom survey themes) */
    public const CLASSIC_FIELD = 'loadsecurity';

    public const DEFAULT_ALTCHA_DIFFICULTY = 'high';
    public const DEFAULT_ALTCHA_AUTO = 'off';
    public const DEFAULT_ALTCHA_EXPIRY_MINUTES = 5;
    /** How many solved challenges per session are remembered to refuse replays */
    public const ALTCHA_MAX_USED_CHALLENGES = 50;
    /** PBKDF2 iterations per attempt, the widget needs about 256 attempts on average for the "00" key prefix */
    public const ALTCHA_COSTS = [
        'low' => 2000,
        'medium' => 5000,
        'high' => 15000,
    ];

    protected $settings = [
        'altchaPart' => [
            'type' => 'separator',
            'title' => 'ALTCHA - accessible proof-of-work CAPTCHA',
        ],
        'altchaEnabled' => [
            'type' => 'boolean',
            'label' => 'Enabled',
            'default' => true,
            'help' => 'The participant ticks a checkbox and the browser solves a small computing puzzle in the background, there is nothing to read or type. Works with screen readers and needs no external service.',
        ],
        'altchaDifficulty' => [
            'type' => 'select',
            'label' => 'Difficulty',
            'options' => [
                'low' => 'Low - fastest, also for slow devices',
                'medium' => 'Medium',
                'high' => 'High - more protection, slower on old devices',
            ],
            'default' => self::DEFAULT_ALTCHA_DIFFICULTY,
            'help' => 'A higher difficulty makes automated submissions more expensive, but the puzzle takes longer on slow devices.',
        ],
        'altchaAuto' => [
            'type' => 'select',
            'label' => 'Verification starts',
            'options' => [
                'off' => 'When the participant ticks the checkbox',
                'onload' => 'Automatically when the page loads',
                'onsubmit' => 'Automatically when the form is submitted',
            ],
            'default' => self::DEFAULT_ALTCHA_AUTO,
        ],
        'altchaExpiryMinutes' => [
            'type' => 'int',
            'label' => 'Verification valid for (minutes)',
            'default' => self::DEFAULT_ALTCHA_EXPIRY_MINUTES,
            'help' => 'If the form is submitted later than this, the participant has to verify again.',
        ],
        'altchaShowFooter' => [
            'type' => 'boolean',
            'label' => 'Show the ALTCHA attribution link',
            'default' => false,
        ],
        'classicPart' => [
            'type' => 'separator',
            'title' => 'Classic CAPTCHA - equation shown as an image (not accessible)',
        ],
        'classicEnabled' => [
            'type' => 'boolean',
            'label' => 'Enabled',
            'default' => true,
            'help' => 'Shows a simple equation as an image that the participant has to solve. It cannot be used with a screen reader, so it is not accessible. Needs the GD extension with FreeType support.',
        ],
    ];

    /**
     * @inheritdoc The widget fetches its challenge through a direct request
     */
    #[\Override]
    public function init()
    {
        parent::init();
        $this->subscribe('newDirectRequest');
    }

    /**
     * Direct request: answer the ALTCHA widget's request for a new challenge with JSON.
     *
     * @return void
     */
    public function newDirectRequest()
    {
        $event = $this->getEvent();
        if ($event->get('target') !== self::$name || $event->get('function') !== self::ALTCHA_CHALLENGE_FUNCTION) {
            return;
        }
        if (!$this->isTypeEnabled('altchaEnabled')) {
            throw new CHttpException(404, gT("This CAPTCHA type is not available."));
        }
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo $this->createAltchaChallenge()->toJson();
        App()->end();
    }

    /**
     * Create a new ALTCHA challenge for the current session.
     *
     * @return Challenge
     */
    public function createAltchaChallenge(): Challenge
    {
        return $this->createAltchaInstance()->createChallenge(new CreateChallengeOptions(
            algorithm: new Pbkdf2(),
            cost: $this->getAltchaCost(),
            expiresAt: time() + $this->getAltchaExpiryMinutes() * 60,
        ));
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function getCaptchaTypes(): array
    {
        $types = [];
        if ($this->isTypeEnabled('altchaEnabled')) {
            $types[self::TYPE_ALTCHA] = [
                'label' => gT("ALTCHA (accessible, recommended)", 'unescaped'),
                'description' => gT("The participant ticks a checkbox and the browser solves a small computing puzzle in the background, there is nothing to read or type. Works with screen readers and needs no external service.", 'unescaped'),
            ];
        }
        if ($this->isTypeEnabled('classicEnabled') && $this->isClassicCaptchaAvailable()) {
            $types[self::TYPE_CLASSIC] = [
                'label' => gT("Classic (image, not accessible)", 'unescaped'),
                'description' => gT("Shows a simple equation as an image that the participant has to solve. It cannot be used with a screen reader, so it is not accessible.", 'unescaped'),
            ];
        }
        return $types;
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    protected function renderCaptchaWidget(string $type, int $surveyId, string $screen): array
    {
        if ($type === self::TYPE_CLASSIC) {
            return $this->renderClassicCaptcha($surveyId, $screen);
        }
        return $this->renderAltchaCaptcha($surveyId);
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    protected function validateCaptchaAnswer(string $type, int $surveyId, string $screen): bool
    {
        if ($type === self::TYPE_CLASSIC) {
            return $this->validateClassicCaptcha($surveyId, $screen);
        }
        return $this->validateAltchaCaptcha();
    }

    /**
     * Whether a CAPTCHA type is switched on in the plugin settings.
     *
     * @param string $settingName 'altchaEnabled' or 'classicEnabled'
     * @return bool
     */
    protected function isTypeEnabled(string $settingName): bool
    {
        $value = $this->get($settingName, null, null, true);
        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * The classic CAPTCHA draws an image, so it needs GD with FreeType support.
     *
     * @return bool
     */
    protected function isClassicCaptchaAvailable(): bool
    {
        return extension_loaded('gd') && function_exists('imagettftext');
    }

    /**
     * Render the ALTCHA widget. It fetches a fresh challenge from this plugin when the verification starts,
     * so the expiry only counts from then.
     *
     * @param int $surveyId
     * @return array{html: string, label: null, inputId: null}
     */
    protected function renderAltchaCaptcha(int $surveyId): array
    {
        $assetsUrl = App()->getAssetManager()->publish(__DIR__ . '/assets');
        App()->getClientScript()->registerScriptFile($assetsUrl . '/altcha.i18n.umd.min.js');
        // Open the session now, so the challenge fetched later is signed for the same session
        App()->session->open();

        $configuration = [
            'hideLogo' => true,
            'hideFooter' => !$this->getAltchaShowFooter(),
        ];
        $widget = CHtml::tag('altcha-widget', [
            'challenge' => App()->createUrl('plugins/direct', ['plugin' => self::$name, 'function' => self::ALTCHA_CHALLENGE_FUNCTION]),
            'configuration' => json_encode($configuration),
            'name' => self::ALTCHA_FIELD,
            'auto' => $this->getAltchaAuto(),
            'language' => $this->getAltchaLanguage(),
            'id' => 'altcha-' . $surveyId,
        ], '');
        $noscript = CHtml::tag(
            'noscript',
            [],
            CHtml::tag('div', ['class' => 'alert alert-warning', 'role' => 'alert'], CHtml::encode(gT("Please enable JavaScript to pass the CAPTCHA.")))
        );
        return [
            'html' => CHtml::tag('div', ['class' => 'ls-captcha ls-captcha-altcha'], $widget . $noscript),
            'label' => null,
            'inputId' => null,
        ];
    }

    /**
     * Verify the ALTCHA payload sent with the form.
     *
     * A payload is accepted once per session: the challenge signature is bound to the session
     * and solved challenges are remembered.
     *
     * @return bool
     */
    protected function validateAltchaCaptcha(): bool
    {
        $payload = App()->getRequest()->getPost(self::ALTCHA_FIELD, '');
        if (!is_string($payload) || $payload === '') {
            $this->setCaptchaErrorMessage(gT("The CAPTCHA verification is missing. Please make sure JavaScript is enabled and try again."));
            return false;
        }
        try {
            $result = $this->createAltchaInstance()->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: new Pbkdf2(),
            ));
        } catch (\Throwable) {
            // A malformed payload is treated like a failed verification
            $result = null;
        }
        if ($result === null || !$result->verified) {
            if ($result !== null && $result->expired) {
                $this->setCaptchaErrorMessage(gT("The CAPTCHA verification has expired - please try again."));
            } else {
                $this->setCaptchaErrorMessage(gT("The CAPTCHA verification failed - please try again."));
            }
            return false;
        }
        if (!$this->rememberAltchaChallenge($payload)) {
            $this->setCaptchaErrorMessage(gT("The CAPTCHA verification failed - please try again."));
            return false;
        }
        return true;
    }

    /**
     * Remember a solved challenge for this session, refusing it when it was already used.
     *
     * @param string $payload Base64 encoded payload as sent by the widget
     * @return bool False when the challenge was used before
     */
    protected function rememberAltchaChallenge(string $payload): bool
    {
        $decoded = json_decode((string) base64_decode($payload, true), true);
        $signature = (string) ($decoded['challenge']['signature'] ?? '');
        if ($signature === '') {
            return false;
        }
        $session = App()->session;
        $used = (array) ($session['coreCaptchaUsedAltchaChallenges'] ?? []);
        if (in_array($signature, $used, true)) {
            return false;
        }
        $used[] = $signature;
        $session['coreCaptchaUsedAltchaChallenges'] = array_slice($used, -self::ALTCHA_MAX_USED_CHALLENGES);
        return true;
    }

    /**
     * Create the ALTCHA server instance.
     *
     * The signing secret is derived from an installation secret and the session id, so a
     * payload solved in one session cannot be submitted in another.
     *
     * @return Altcha
     */
    protected function createAltchaInstance(): Altcha
    {
        $session = App()->session;
        $session->open();
        $sessionKey = hash_hmac('sha256', (string) $session->getSessionID(), $this->getAltchaSecret());
        return new Altcha(hmacSignatureSecret: $sessionKey);
    }

    /**
     * Get the installation wide secret for signing challenges, created on first use.
     *
     * @return string
     */
    protected function getAltchaSecret(): string
    {
        $secret = (string) $this->get('altchaSecret', null, null, '');
        if ($secret === '') {
            $secret = bin2hex(random_bytes(32));
            $this->set('altchaSecret', $secret);
        }
        return $secret;
    }

    /**
     * PBKDF2 iterations for the configured difficulty.
     *
     * @return int
     */
    protected function getAltchaCost(): int
    {
        $difficulty = (string) $this->get('altchaDifficulty', null, null, self::DEFAULT_ALTCHA_DIFFICULTY);
        return self::ALTCHA_COSTS[$difficulty] ?? self::ALTCHA_COSTS[self::DEFAULT_ALTCHA_DIFFICULTY];
    }

    /**
     * When the widget runs the verification: 'onload', 'onsubmit' or 'off' (participant ticks the checkbox).
     *
     * @return string
     */
    protected function getAltchaAuto(): string
    {
        $auto = (string) $this->get('altchaAuto', null, null, self::DEFAULT_ALTCHA_AUTO);
        return in_array($auto, ['onload', 'onsubmit', 'off'], true) ? $auto : self::DEFAULT_ALTCHA_AUTO;
    }

    /**
     * Minutes a challenge stays valid.
     *
     * @return int
     */
    protected function getAltchaExpiryMinutes(): int
    {
        $minutes = (int) $this->get('altchaExpiryMinutes', null, null, self::DEFAULT_ALTCHA_EXPIRY_MINUTES);
        return $minutes > 0 ? $minutes : self::DEFAULT_ALTCHA_EXPIRY_MINUTES;
    }

    /**
     * Whether the ALTCHA attribution link is shown in the widget.
     *
     * @return bool
     */
    protected function getAltchaShowFooter(): bool
    {
        return (bool) $this->get('altchaShowFooter', null, null, false);
    }

    /**
     * Language code for the widget texts: the survey language without LimeSurvey's dialect suffixes.
     *
     * @return string
     */
    protected function getAltchaLanguage(): string
    {
        $language = strtolower(str_replace('_', '-', (string) App()->language));
        $language = (string) preg_replace('/-(informal|formal|valencia)$/', '', $language);
        return $language !== '' ? $language : 'en';
    }

    /**
     * Render the classic image CAPTCHA with its answer field.
     *
     * The survey access and registration screens use the CaptchaExtended action of the current
     * controller; the save/load screen keeps its own image action and session answer, so custom
     * survey themes with the old save/load templates keep working.
     *
     * @param int $surveyId
     * @param string $screen
     * @return array{html: string, label: string, inputId: string, data: array}
     */
    protected function renderClassicCaptcha(int $surveyId, string $screen): array
    {
        $data = [];
        if ($screen === CaptchaService::SCREEN_SAVE_AND_LOAD) {
            $imageUrl = App()->getController()->createUrl('/verification/image', ['sid' => $surveyId]);
            $image = CHtml::image($imageUrl, gT("Image showing the equation to solve"), ['class' => 'img-fluid']);
            $data['sImageUrl'] = $imageUrl;
            $reload = '';
        } else {
            $image = App()->getController()->createWidget('LSCaptcha', ['captchaAction' => 'captcha'])->renderOut();
            $reload = CHtml::htmlButton(
                CHtml::tag('i', ['class' => 'ri-refresh-line fa fa-refresh', 'aria-hidden' => 'true'], ''),
                [
                    'type' => 'button',
                    'class' => 'btn btn-sm btn-outline-secondary',
                    'id' => 'reloadCaptcha',
                    'title' => gT("Reload captcha"),
                    'aria-label' => gT("Reload captcha"),
                    'data-bs-toggle' => 'captcha',
                ]
            );
        }
        $input = CHtml::textField(self::CLASSIC_FIELD, '', [
            'id' => self::CLASSIC_FIELD,
            'class' => 'form-control ls-important-field',
            'size' => 15,
            'maxlength' => 15,
            'required' => 'required',
            'autocomplete' => 'off',
            'inputmode' => 'numeric',
            'placeholder' => gT("Enter result here - numbers only"),
        ]);
        $html = CHtml::tag(
            'div',
            ['class' => 'ls-captcha ls-captcha-classic row align-items-center'],
            CHtml::tag('div', ['class' => 'captcha-container col-auto'], $image . $reload)
            . CHtml::tag('div', ['class' => 'captcha-input col'], $input)
        );
        return [
            'html' => $html,
            'label' => gT("Please solve the following equation:"),
            'inputId' => self::CLASSIC_FIELD,
            'data' => $data,
        ];
    }

    /**
     * Check the answer to the classic image CAPTCHA.
     *
     * @param int $surveyId
     * @param string $screen
     * @return bool
     */
    protected function validateClassicCaptcha(int $surveyId, string $screen): bool
    {
        $answer = trim((string) App()->getRequest()->getPost(self::CLASSIC_FIELD, ''));
        if ($answer === '') {
            $this->setCaptchaErrorMessage(gT("You did not answer to the security question."));
            return false;
        }
        if ($screen === CaptchaService::SCREEN_SAVE_AND_LOAD) {
            $expected = $_SESSION['responses_' . $surveyId]['secanswer'] ?? null;
            $valid = $expected !== null && $answer === (string) $expected;
        } else {
            $valid = App()->getController()->createAction('captcha')->validate($answer, false);
        }
        if (!$valid) {
            $this->setCaptchaErrorMessage(gT("Your answer to the security question was not correct - please try again."));
        }
        return $valid;
    }
}
