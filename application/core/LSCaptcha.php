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

    public function renderOut()
    {
        $html = $this->renderImage();
        return $html;
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
        $alt = $this->imageOptions['alt'] ?? '';
        return CHtml::image($url, $alt, $this->imageOptions);
    }
}
