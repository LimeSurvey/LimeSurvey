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

    /**
     * What URL to call for Rest API (limesurvey.org or limeservice.com for testing)
     *
     * @var string
     */
    private $baseURL = "https://www.limeservice.com/v2/index.php?option=>com_api";

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

        $pluginBaseUrl = Yii::app()->createUrl(
            'plugins/direct',
            array(
                'plugin' => 'CintLink',
                'surveyId' => $surveyId,
            )
        );

        $data['pluginBaseUrl'] = $pluginBaseUrl;

        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.index', $data, true);

        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/js');
        App()->clientScript->registerScriptFile("$assetsUrl/cintlink.js");

        /*
        $curl = new Curl();
        $response = $curl->post(
            $this->baseURL,
            array(
                'app' => 'cintlinklimesurveyrestapi',
                'format' => 'raw',
                'resource' => 'test',
                'key' => 'fbc4607979c0292465185aae3422c93b'
            )
        );
        var_dump($response->body);
        */

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
            $curl = new Curl();
            $response = $curl->post(
                $this->baseURL,
                array(
                    'app' => 'cintlinklimesurveyrestapi',
                    'format' => 'raw',
                    'resource' => 'test',
                    'key' => $this->limesurveyOrgKey
                )
            );

            if ($response->body == "post ok")
            {
                return json_encode(array('result' => true));
            }
            else
            {
                return json_encode(array('result' => false));
            }
        }
    }

    /**
     * Return HTMl for login form
     * Called by Ajax
     *
     * @return string
     */
    public function getLoginForm(LSHttpRequest $request)
    {
        $data = array();
        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.loginform', $data, true);
        return $content;
    }

    public function newDirectRequest()
    {
        $event = $this->event;
        if ($event->get('target') == "CintLink")
        {
            $request = $event->get('request');  // request = survey id for actionIndex?
            $functionToCall = $event->get('function');
            if ($functionToCall == "actionIndex")
            {
                $content = $this->actionIndex($request);
                $event->setContent($this, $content);
            }
            else if ($functionToCall == 'checkIfUserIsLoggedInOnLimesurveyorg'
                    || $functionToCall == 'getLoginForm')
            {
                echo $this->$functionToCall($request);
            }
        }
    }
}
