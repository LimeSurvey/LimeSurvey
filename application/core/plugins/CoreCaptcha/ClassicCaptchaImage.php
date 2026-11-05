<?php

/**
 * Image of the classic CAPTCHA (an equation drawn by the controller's "captcha" action, CaptchaExtendedAction)
 * on the survey access and registration screens.
 */
class ClassicCaptchaImage extends CCaptcha
{
    /**
     * Get the image tag of the CAPTCHA, with a text alternative.
     *
     * @return string
     */
    public function renderOut()
    {
        if (!isset($this->imageOptions['id'])) {
            $this->imageOptions['id'] = $this->getId();
        }
        $this->imageOptions['class'] = 'img-fluid';
        $url = $this->getController()->createUrl($this->captchaAction, ['v' => uniqid()]);
        $alt = $this->imageOptions['alt'] ?? gT('Image showing the equation to solve', 'unescaped');
        return CHtml::image($url, $alt, $this->imageOptions);
    }
}
