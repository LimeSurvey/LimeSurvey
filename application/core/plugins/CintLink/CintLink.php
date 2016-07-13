<?php

use \ls\menu\MenuItem;

/**
 * CintLink integration to be able to buy respondents
 * from within LimeSurvey.
 *
 * @since 2016-07-13
 * @author Olle Härstedt
 */
class CintLink extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Buy respondents inside LimeSurvey';
    static protected $name = 'CintLink';

    protected $storage = 'DbStorage';
    //protected $settings = array();

    private $cintApiKey = "";

    public function init()
    {
        $this->subscribe('beforeToolsMenuRender');
        $this->subscribe('newDirectRequest');

        $this->cintApiKey = "7809687755495";  // Sandbox
    }

    public function beforeToolsMenuRender()
    {
        $event = $this->getEvent();
        $surveyId = $event->get('surveyId');

        $href = Yii::app()->createUrl(
            'admin/pluginhelper',
            array(
                'sa' => 'sidebody',
                'plugin' => 'CintLink',
                'method' => 'actionIndex',
                'surveyId' => $surveyId
            )
        );

        $menuItem = new MenuItem(array(
            'label' => gT('CintLink'),
            'iconClass' => 'fa fa-table',
            'href' => $href
        ));

        $event->append('menuItems', array($menuItem));
    }

    /**
     * @return string
     */
    public function actionIndex($surveyId)
    {
        $data = array();

        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.index', $data, true);

        return $content;
    }

    public function newDirectRequest()
    {
        $event = $this->event;
        if ($event->get('target') == "CintLink")
        {
            $request = $event->get('request');
            $functionToCall = $event->get('function');
            if ($functionToCall == "actionIndex")
            {
                $content = $this->actionIndex($request);
                $event->setContent($this, $content);
            }
        }
    }
}
