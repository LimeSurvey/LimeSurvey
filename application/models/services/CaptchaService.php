<?php

namespace LimeSurvey\Models\Services;

use PluginEvent;
use Survey;

/**
 * Resolves which CAPTCHA plugin a survey uses and dispatches the rendering
 * and validation of the CAPTCHA to that plugin.
 *
 * A plugin can offer one or more CAPTCHA types, each identified by a unique type id (e.g. "altcha").
 * CAPTCHA plugins subscribe to three events:
 *  - getCaptchaProviders: append ['providers' => [<typeId> => ['label' => ..., 'description' => ...]]]
 *  - renderCaptcha: when 'provider' is one of the plugin's type ids, set 'html' (the complete form field
 *    markup), optionally 'label' (plain text), 'inputId' (for the label's "for" attribute), 'legacyHtml'
 *    (markup for older survey themes that render their own answer field, see LS_Twig_Extension::renderCaptcha())
 *    and 'data' (extra template data)
 *  - validateCaptcha: when 'provider' is one of the plugin's type ids, set 'valid' (bool) and optionally 'message'
 *
 * See LimeSurvey\PluginManager\CaptchaPluginBase for a base class that handles the events.
 *
 * The CAPTCHA type is a survey setting (captchatype) with the usual inheritance from the survey
 * group and the global survey settings.
 */
class CaptchaService
{
    public const SCREEN_SURVEY_ACCESS = 'surveyaccessscreen';
    public const SCREEN_REGISTRATION = 'registrationscreen';
    public const SCREEN_SAVE_AND_LOAD = 'saveandloadscreen';

    /** Value of the captchatype survey setting that inherits from the group or global settings */
    public const INHERIT = 'inherit';

    /** CAPTCHA type meaning "no CAPTCHA": nothing is shown and validated, even where the CAPTCHA is switched on */
    public const NONE = 'none';

    /** Name of the hidden field rendered with every CAPTCHA, telling the server that a form with a CAPTCHA was submitted */
    public const SUBMITTED_FIELD = 'captcha_submitted';

    /** @var string|null The CAPTCHA type of the global survey settings, cached per request */
    private static $globalProvider = null;

    /** @var bool Whether the missing CAPTCHA plugin was already logged in this request */
    private static $noProviderLogged = false;

    /** @var array<string, array{label: string, description: string}>|null CAPTCHA types collected from plugins, cached per request */
    private static $providers = null;

    /** @var array<string, array> Rendered CAPTCHA data per survey and screen, so a widget is only rendered once per request */
    private static $rendered = [];

    /** @var array|null The CAPTCHA data rendered last, used by the deprecated twig function renderCaptcha() */
    private static $lastRendered = null;

    /** @var string Error message of the last failed validation */
    private $errorMessage = '';

    /**
     * Get all CAPTCHA types offered by the active plugins.
     *
     * @return array<string, array{label: string, description: string}> CAPTCHA type id => label and description
     */
    public function getProviders(): array
    {
        if (self::$providers === null) {
            $event = new PluginEvent('getCaptchaProviders');
            \App()->getPluginManager()->dispatchEvent($event);
            $providers = [];
            foreach ((array) $event->get('providers', []) as $name => $provider) {
                if (!is_string($name) || $name === '') {
                    continue;
                }
                $providers[$name] = [
                    'label' => (string) ($provider['label'] ?? $name),
                    'description' => (string) ($provider['description'] ?? ''),
                ];
            }
            self::$providers = $providers;
        }
        return self::$providers;
    }

    /**
     * Get the CAPTCHA type that is used when nothing else is configured or the configured type is not available.
     *
     * This is the type of the global survey settings when a plugin offers it, otherwise the first
     * offered type in alphabetical order, so the fallback does not depend on the plugin load order.
     *
     * @return string|null CAPTCHA type id, null when no CAPTCHA plugin is available
     */
    public function getDefaultProvider(): ?string
    {
        $providers = $this->getProviders();
        if (empty($providers)) {
            return null;
        }
        $globalProvider = $this->getGlobalProvider();
        if ($globalProvider !== null && array_key_exists($globalProvider, $providers)) {
            return $globalProvider;
        }
        $typeIds = array_keys($providers);
        sort($typeIds, SORT_STRING);
        return (string) $typeIds[0];
    }

    /**
     * Get the CAPTCHA type of the global survey settings (survey group settings with gsid 0).
     *
     * @return string|null Type id, null when unset, "inherit" or "none"
     */
    private function getGlobalProvider(): ?string
    {
        if (self::$globalProvider === null) {
            $globalSettings = \SurveysGroupsettings::model()->findByPk(0);
            self::$globalProvider = (string) ($globalSettings->captchatype ?? '');
        }
        if (self::$globalProvider === '' || self::$globalProvider === self::INHERIT || self::$globalProvider === self::NONE) {
            return null;
        }
        return self::$globalProvider;
    }

    /**
     * Resolve the CAPTCHA type used by a survey, taking inheritance into account.
     *
     * A configured type that no active plugin offers falls back to the default type; without
     * any available type the result is NONE, so the survey works without a CAPTCHA.
     *
     * @param Survey $survey
     * @return string CAPTCHA type id, or NONE for no CAPTCHA
     */
    public function resolveProvider(Survey $survey): string
    {
        $configured = $this->getConfiguredProvider($survey);
        if ($configured === self::NONE) {
            return self::NONE;
        }
        if ($configured !== null && array_key_exists($configured, $this->getProviders())) {
            return $configured;
        }
        $defaultProvider = $this->getDefaultProvider();
        if ($defaultProvider === null) {
            $this->logNoProvider((int) $survey->sid, $configured);
            return self::NONE;
        }
        return $defaultProvider;
    }

    /**
     * Warn the administrator (application log) that a survey wants a CAPTCHA but no active plugin offers one,
     * so the CAPTCHA is silently switched off. Logged once per request.
     *
     * @param int $surveyId
     * @param string|null $configured The CAPTCHA type configured for the survey, null when inherited without a value
     * @return void
     */
    private function logNoProvider(int $surveyId, ?string $configured): void
    {
        if (self::$noProviderLogged) {
            return;
        }
        self::$noProviderLogged = true;
        \Yii::log(
            sprintf(
                'Survey %d uses the CAPTCHA type "%s", but no active plugin offers a CAPTCHA type, so no CAPTCHA is shown. Activate a CAPTCHA plugin (for example CoreCaptcha) or set the CAPTCHA type to "none".',
                $surveyId,
                $configured ?? self::INHERIT
            ),
            \CLogger::LEVEL_WARNING,
            'application.captcha'
        );
    }

    /**
     * Whether a survey shows a CAPTCHA at all, i.e. its resolved CAPTCHA type is not NONE.
     *
     * @param Survey $survey
     * @return bool
     */
    public function isCaptchaActive(Survey $survey): bool
    {
        return $this->resolveProvider($survey) !== self::NONE;
    }

    /**
     * Get the CAPTCHA type configured for a survey after inheritance, without checking its availability.
     *
     * @param Survey $survey
     * @return string|null
     */
    public function getConfiguredProvider(Survey $survey): ?string
    {
        if ($survey->oOptions === null || !property_exists($survey->oOptions, 'captchatype')) {
            $survey->setOptions($survey->gsid);
        }
        $configured = $survey->oOptions->captchatype ?? null;
        if (empty($configured) || $configured === self::INHERIT) {
            return null;
        }
        return (string) $configured;
    }

    /**
     * Render the CAPTCHA of a survey for a screen.
     *
     * The result is cached for the request, so templates can access the same widget several times.
     *
     * @param Survey $survey
     * @param string $screen One of the SCREEN_* constants
     * @return array{show: bool, provider: string, html: string, legacyHtml: string, label: string|null, inputId: string|null}
     *         plus any plugin data; show is false when the survey uses no CAPTCHA, html starts with a hidden
     *         field (SUBMITTED_FIELD), label is HTML encoded for the survey themes
     */
    public function render(Survey $survey, string $screen): array
    {
        $cacheKey = $survey->sid . ':' . $screen;
        if (!isset(self::$rendered[$cacheKey])) {
            self::$rendered[$cacheKey] = $this->renderUncached($survey, $screen);
        }
        self::$lastRendered = self::$rendered[$cacheKey];
        return self::$rendered[$cacheKey];
    }

    /**
     * Validate the CAPTCHA answer sent with the current request.
     *
     * @param Survey $survey
     * @param string $screen One of the SCREEN_* constants
     * @return bool
     */
    public function validate(Survey $survey, string $screen): bool
    {
        $this->errorMessage = '';
        $provider = $this->resolveProvider($survey);
        if ($provider === self::NONE) {
            return true;
        }
        $event = new PluginEvent('validateCaptcha');
        $event->set('provider', $provider);
        $event->set('surveyId', (int) $survey->sid);
        $event->set('screen', $screen);
        $event->set('valid', false);
        \App()->getPluginManager()->dispatchEvent($event);
        if ($event->get('valid') === true) {
            return true;
        }
        $message = (string) $event->get('message', '');
        $this->errorMessage = $message !== '' ? $message : \gT("The CAPTCHA verification failed - please try again.");
        return false;
    }

    /**
     * Get the error message of the last failed validation.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get the CAPTCHA data rendered last in this request.
     *
     * Only used by the deprecated twig function renderCaptcha(), which has no survey or screen parameter.
     *
     * @return array|null
     */
    public static function getLastRendered(): ?array
    {
        return self::$lastRendered;
    }

    /**
     * Forget the cached providers and rendered widgets (used by tests).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$providers = null;
        self::$globalProvider = null;
        self::$noProviderLogged = false;
        self::$rendered = [];
        self::$lastRendered = null;
    }

    /**
     * Render the CAPTCHA by dispatching the renderCaptcha event to the resolved plugin.
     *
     * @param Survey $survey
     * @param string $screen
     * @return array
     */
    private function renderUncached(Survey $survey, string $screen): array
    {
        $provider = $this->resolveProvider($survey);
        $result = [
            'show' => $provider !== self::NONE,
            'provider' => $provider,
            'html' => '',
            'legacyHtml' => '',
            'label' => null,
            'inputId' => null,
        ];
        if ($provider === self::NONE) {
            return $result;
        }
        $event = new PluginEvent('renderCaptcha');
        $event->set('provider', $provider);
        $event->set('surveyId', (int) $survey->sid);
        $event->set('screen', $screen);
        $event->set('language', \App()->language);
        \App()->getPluginManager()->dispatchEvent($event);

        // The hidden field tells the server that the submitted form carried a CAPTCHA (see SurveyRuntimeHelper)
        $submittedField = \CHtml::hiddenField(self::SUBMITTED_FIELD, '1');
        $result['html'] = $submittedField . (string) $event->get('html', '');
        $legacyHtml = $event->get('legacyHtml');
        $result['legacyHtml'] = $submittedField . (($legacyHtml === null || $legacyHtml === '') ? (string) $event->get('html', '') : (string) $legacyHtml);
        $label = $event->get('label');
        $result['label'] = ($label === null || $label === '') ? null : \CHtml::encode((string) $label);
        $inputId = $event->get('inputId');
        $result['inputId'] = ($inputId === null || $inputId === '') ? null : (string) $inputId;
        $data = $event->get('data');
        if (is_array($data)) {
            $result = array_merge($data, $result);
        }
        return $result;
    }
}
