<?php

namespace LimeSurvey\PluginManager;

/**
 * Base class for plugins that offer one or more CAPTCHA types.
 *
 * A CAPTCHA type is identified by a unique id (for example "altcha"). The id is stored in the
 * captchatype survey setting, so it must stay stable and should be prefixed with something
 * specific to the plugin to avoid clashes with types of other plugins.
 *
 * Subclasses implement getCaptchaTypes(), renderCaptchaWidget() and validateCaptchaAnswer();
 * this class takes care of the getCaptchaProviders, renderCaptcha and validateCaptcha events.
 */
abstract class CaptchaPluginBase extends PluginBase
{
    /** @var string Error message set by validateCaptchaAnswer() for the current validation */
    private $captchaErrorMessage = '';

    /**
     * Subscribe to the CAPTCHA events. Subclasses overriding init() must call parent::init().
     *
     * @return void
     */
    public function init()
    {
        $this->subscribe('getCaptchaProviders');
        $this->subscribe('renderCaptcha');
        $this->subscribe('validateCaptcha');
    }

    /**
     * Get the CAPTCHA types this plugin offers right now.
     *
     * Types that cannot work in the current environment (missing PHP extension, missing
     * configuration) must be left out, the core then falls back to another available type.
     *
     * @return array<string, array{label: string, description: string}> Type id => translated label and description
     *         as plain text (not HTML escaped), the views encode them
     */
    abstract public function getCaptchaTypes(): array;

    /**
     * Render the CAPTCHA form field for a survey screen.
     *
     * The returned html must contain everything the participant needs, including the input
     * that carries the answer, and is placed inside the form by the survey theme.
     *
     * @param string $type One of the type ids returned by getCaptchaTypes()
     * @param int $surveyId
     * @param string $screen One of the LimeSurvey\Models\Services\CaptchaService::SCREEN_* constants
     * @return array{html: string, label?: string|null, inputId?: string|null, data?: array}
     *         label: text for a visible label rendered by the theme (null for none),
     *         inputId: id of the input the label points to, data: extra data for the theme templates
     */
    abstract protected function renderCaptchaWidget(string $type, int $surveyId, string $screen): array;

    /**
     * Validate the CAPTCHA answer sent with the current request.
     *
     * Call setCaptchaErrorMessage() to tell the participant what went wrong.
     *
     * @param string $type One of the type ids returned by getCaptchaTypes()
     * @param int $surveyId
     * @param string $screen One of the LimeSurvey\Models\Services\CaptchaService::SCREEN_* constants
     * @return bool
     */
    abstract protected function validateCaptchaAnswer(string $type, int $surveyId, string $screen): bool;

    /**
     * Event handler: offer this plugin's CAPTCHA types.
     *
     * @return void
     */
    public function getCaptchaProviders()
    {
        $types = $this->getCaptchaTypes();
        if (!empty($types)) {
            $this->getEvent()->append('providers', $types);
        }
    }

    /**
     * Event handler: render the CAPTCHA when the requested type belongs to this plugin.
     *
     * @return void
     */
    public function renderCaptcha()
    {
        $event = $this->getEvent();
        $type = (string) $event->get('provider');
        if (!array_key_exists($type, $this->getCaptchaTypes())) {
            return;
        }
        $result = $this->renderCaptchaWidget($type, (int) $event->get('surveyId'), (string) $event->get('screen'));
        $event->set('html', (string) ($result['html'] ?? ''));
        if (array_key_exists('label', $result)) {
            $event->set('label', $result['label']);
        }
        if (array_key_exists('inputId', $result)) {
            $event->set('inputId', $result['inputId']);
        }
        if (!empty($result['data']) && is_array($result['data'])) {
            $event->set('data', $result['data']);
        }
    }

    /**
     * Event handler: validate the CAPTCHA answer when the requested type belongs to this plugin.
     *
     * @return void
     */
    public function validateCaptcha()
    {
        $event = $this->getEvent();
        $type = (string) $event->get('provider');
        if (!array_key_exists($type, $this->getCaptchaTypes())) {
            return;
        }
        $this->captchaErrorMessage = '';
        $valid = $this->validateCaptchaAnswer($type, (int) $event->get('surveyId'), (string) $event->get('screen'));
        $event->set('valid', $valid);
        if (!$valid && $this->captchaErrorMessage !== '') {
            $event->set('message', $this->captchaErrorMessage);
        }
    }

    /**
     * Set the message shown to the participant when the validation fails.
     *
     * @param string $message Translated message
     * @return void
     */
    protected function setCaptchaErrorMessage(string $message): void
    {
        $this->captchaErrorMessage = $message;
    }
}
