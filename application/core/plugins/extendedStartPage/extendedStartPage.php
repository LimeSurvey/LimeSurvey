<?php
/**
 * Extended startpage plugin to display more information about the 
 * sctive surveys on the startpage.
 *
 * @since 2016-07-22
 * @author Markus Flür
 *
 */
class ExtendedStartpage extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Extended start page view';
    static protected $name = 'extendStartpage';
    
    public function init()
    {
        $this->subscribe('beforeSurveysStartpageRender');
    }

    public function beforeSurveysStartpageRender(){
        $event = $this->getEvent();
        $aData = $event->get('aData');

        $html = $this->renderPartial('publicSurveyList_extended',$aData,true,true );
        $event->append('result',array('html'=>$html));
    }

}