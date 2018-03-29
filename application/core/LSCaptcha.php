<?php
/**
 * CCaptcha class file.
 *
 * @author Markus Flür <markus.fluer@limesurvey.org>
 * @link http://www.limesurvey.org/
 * @copyright 2008-2018 LimeSurvey GmbH
 * @license GPLv3
 */

class LSCaptcha extends CCaptcha
{

    public function renderOut() {
        $html = $this->renderImage();
        return $html;
    }

    /**
     * Renders the CAPTCHA image.
     */
    protected function renderImage()
    {
        if(!isset($this->imageOptions['id']))
            $this->imageOptions['id']=$this->getId();

        $url=$this->getController()->createUrl($this->captchaAction,array('v'=>uniqid()));
        $alt=isset($this->imageOptions['alt'])?$this->imageOptions['alt']:'';
        return CHtml::image($url,$alt,$this->imageOptions);
    }
}
