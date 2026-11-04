<?php

/**
 * CCaptcha class file.
 *
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @link http://www.limesurvey.org/
 * @copyright 2008-2018 LimeSurvey GmbH
 * @license GPLv3
 */

class LSCaptcha extends CCaptcha
{
    /**
     * @var string|null Markup to return instead of the image, set by the deprecated twig function renderCaptcha()
     *                  so older survey themes show the CAPTCHA of the plugin selected for the survey
     */
    public $prerenderedHtml = null;

    /**
     * Get the CAPTCHA markup: the prerendered plugin markup when set, otherwise the classic image.
     *
     * @return string
     */
    public function renderOut()
    {
        if ($this->prerenderedHtml !== null) {
            return $this->prerenderedHtml;
        }
        return $this->renderImage();
    }

    /**
     * Renders the CAPTCHA image.
     */
    protected function renderImage()
    {
        if (!isset($this->imageOptions['id'])) {
                    $this->imageOptions['id'] = $this->getId();
        }
        $this->imageOptions['class'] = "img-fluid";

        $url = $this->getController()->createUrl($this->captchaAction, array('v' => uniqid()));
        $alt = $this->imageOptions['alt'] ?? gT('Image showing the equation to solve', 'unescaped');
        return CHtml::image($url, $alt, $this->imageOptions);
    }
}
