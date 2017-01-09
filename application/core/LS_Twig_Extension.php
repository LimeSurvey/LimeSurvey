<?php
class LS_Twig_Extension extends Twig_Extension
{

    static public function registerPublicCssFile($value)
    {
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . $value);
    }
}
