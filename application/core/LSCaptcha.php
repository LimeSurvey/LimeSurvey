<?php

/**
 * Widget behind the deprecated twig function renderCaptcha() (see LS_Twig_Extension::renderCaptcha()).
 *
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @link http://www.limesurvey.org/
 * @copyright 2008-2026 LimeSurvey GmbH
 * @license GPLv3
 * @deprecated The CAPTCHA markup comes from the CAPTCHA plugin selected for the survey (see
 *             LimeSurvey\Models\Services\CaptchaService); survey themes get it from aSurveyInfo.
 */
class LSCaptcha extends CWidget
{
    /**
     * @var string Not used any more, kept so existing callers that pass it keep working
     */
    public $captchaAction = 'captcha';

    /**
     * @var string|null Markup of the CAPTCHA plugin, set by the twig function renderCaptcha()
     */
    public $prerenderedHtml = null;

    /**
     * Get the CAPTCHA markup of the survey's CAPTCHA plugin; nothing when no CAPTCHA was rendered,
     * so no CAPTCHA is shown that would not be validated.
     *
     * @return string
     */
    public function renderOut()
    {
        return $this->prerenderedHtml ?? '';
    }
}
