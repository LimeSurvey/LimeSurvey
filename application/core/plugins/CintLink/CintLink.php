<?php

use \ls\menu\MenuItem;

require_once(__DIR__ . "/CintLinkAPI.php");

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

    /**
     * Key from Cint Link to access their widget
     *
     * @var string
     */
    private $cintApiKey = "";

    /**
     * This is the key handed to you from the
     * com_api Joomla component on limesurvey.org
     * after login, to access Rest plugins
     *
     * @var string
     */
    private $limesurveyOrgKey = "";

    public function init()
    {
        $this->subscribe('beforeToolsMenuRender');
        $this->subscribe('newDirectRequest');

        $this->cintApiKey = "7809687755495";  // Sandbox

        // Login session key from com_api at limesurvey.org
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');
        if (!empty($limesurveyOrgKey))
        {
            $this->limesurveyOrgKey = $limesurveyOrgKey;
        }
    }

    /**
     * todo place somewhere else
     */
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
        $data['limesurveyOrgKey'] = $this->limesurveyOrgKey;

        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.index', $data, true);

        $curl = new Curl();

        return $content;
    }

    /**
     * Return json result true if user is logged in on limesurvey.org
     *
     * @param LSHttpRequest $request
     * @return string JSON
     */
    public function checkIfUserIsLoggedInOnLimesurveyorg(LSHttpRequest $request)
    {
        if (empty($this->limesurveyOrgKey))
        {
            return json_encode(array('result' => false));
        }
        else
        {
        }
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
            else if ($functionToCall == 'checkIfUserIsLoggedInOnLimesurveyorg')
            {
                $content = $this->checkIfUserIsLoggedInOnLimesurveyorg($request);
                $event->setContent($this, $content);
            }
        }
    }
}
