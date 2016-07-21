<?php

use \ls\menu\MenuItem;

require_once(__DIR__ . "/CintLinkAPI.php");
require_once(__DIR__ . "/model/CintLinkOrder.php");

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
    private $cintApiKey = "7809687755495";  // Sandbox

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
    private $baseURL = "https://www.limesurvey.org/index.php?option=com_api";

    public function init()
    {
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeToolsMenuRender');
        $this->subscribe('newDirectRequest');

        // Login session key from com_api at limesurvey.org
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');
        if (!empty($limesurveyOrgKey))
        {
            $this->limesurveyOrgKey = $limesurveyOrgKey;
        }
    }

    /**
     * Add database tables to store information from CintLink
     *
     * @return void
     */
    public function beforeActivate()
    {
        $oDB = Yii::app()->getDb();

        if ($oDB->schema->getTable("{{plugin_cintlink_orders}}") === null)
        {
            $oDB->schemaCachingDuration = 0;  // Deactivate schema caching
            $oTransaction = $oDB->beginTransaction();
            try
            {
                $aFields = array(
                    'url' => 'string primary key',
                    'sid' => 'int',  // Survey id
                    'raw' => 'text',
                    'status' => 'string',
                    'ordered_by' => 'int',  // User id
                    'created' => 'datetime',
                    'modified' => 'datetime',
                );
                $oDB->createCommand()->createTable('{{plugin_cintlink_orders}}', $aFields);
                $oTransaction->commit();
            }
            catch(Exception $e)
            {
                $oTransaction->rollback();
                // Activate schema caching
                $oDB->schemaCachingDuration = 3600;
                // Load all tables of the application in the schema
                $oDB->schema->getTables();
                // Clear the cache of all loaded tables
                $oDB->schema->refresh();
                $event = $this->getEvent();
                $event->set('success', false);
                $event->set(
                    'message',
                    gT('An non-recoverable error happened during the update. Error details:')
                    . "<p>"
                    . htmlspecialchars($e->getMessage())
                    . "</p>"
                );
                return;
            }
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
        $data['surveyId'] = $surveyId;

        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.index', $data, true);

        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/js');
        App()->clientScript->registerScriptFile("$assetsUrl/cintlink.js");
        App()->clientScript->registerScriptFile("http://" . $this->cintApiKey . ".cds.cintworks.net/assets/cint-link-1-0-0.js");

        //$response = json_decode($response);
        /*
        $c = curl_init("https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10");
        curl_setopt($c, CURLOPT_COOKIEJAR, './cookie.txt');
        curl_setopt($c, CURLOPT_COOKIEFILE, './cookie.txt');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, true);
        //curl_setopt($c, CURLOPT_HTTPHEADER, array("Cookie: aeca3d1c1ce0a356a06332c24d79aa4e=eela13alsi3g7uc052nupk12b2"));
        $output = curl_exec($c);
        $headers = curl_getinfo($c, CURLINFO_HEADER_OUT);
        curl_close($c);

        //var_dump(htmlspecialchars($output));
        var_dump($headers);
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
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');
        if (empty($limesurveyOrgKey))
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
                    'key' => $limesurveyOrgKey
                )
            );
            $response = json_decode($response);

            if ($response == "post ok")
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
     * @param LSHttpRequest $request
     * @return string
     */
    public function getLoginForm(LSHttpRequest $request)
    {
        $data = array();
        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.loginform', $data, true);
        return $content;
    }

    /**
     * Return HTML for dashboard
     * Called by Ajax
     *
     * @param LSHttpRequest $request
     * @return string
     */
    public function getDashboard(LSHttpRequest $request)
    {
        $data = array();

        $orders = $this->getOrders();
        $orders = $this->updateOrders($orders);

        $data['orders'] = $orders;

        Yii::setPathOfAlias('cintLink', dirname(__FILE__));
        $content = Yii::app()->controller->renderPartial('cintLink.views.dashboard', $data, true);

        Yii::trace('getDashboard end');
        return $content;
    }

    /**
     * Login to limesurvey.org using com_api
     *
     * @param LSHttpRequest $request
     * @return string JSON
     */
    public function login(LSHttpRequest $request)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        $curl = new Curl();
        $response = $curl->post(
            $this->baseURL,
            array(
                'app' => 'cintlinklimesurveyrestapi',
                'format' => 'raw',
                'resource' => 'login',
                'username' => $username,
                'password' => $password
            )
        );
        $result = json_decode($response->body);

        if ($result->code == 403)
        {
            return json_encode(array('result' => false));
        }
        else if ($result->code == 200)
        {
            Yii::app()->user->setState('limesurveyOrgKey', $result->auth);
            $this->limesurveyOrgKey = $result->auth;

            return json_encode(array('result' => true, 'response' => $response));
        }
        else
        {
            return json_encode(array('error' => 'Unknown return code: ' . $result->code));
        }
    }

    /**
     * When user click "Place order" in the widget,
     * this function is called to contact limesurvey.org
     * and place an order.
     *
     * @param LSHttpRequest $request
     * @return string JSON
     */
    public function purchaseRequest(LSHttpRequest $request)
    {
        $purchaseRequest = $request->getParam('purchaseRequest');
        $surveyId = $request->getParam('surveyId');
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');
        $userId = Yii::app()->user->getId();

        $curl = new Curl();
        $response = $curl->post(
            $this->baseURL,
            array(
                'app' => 'cintlinklimesurveyrestapi',
                'format' => 'raw',
                'resource' => 'order',
                'purchaseRequest' => $purchaseRequest,
                'key' => $limesurveyOrgKey
            )
        );
        $body = json_decode($response->body);

        // Abort if we got nothing
        if ($body === null)
        {
            return json_encode(array(
                'result' => 'false',
                'error' => 'Got NULL from server. Please check error logs.'
            ));
        }

        $order = new CintLinkOrder();
        $order->url = $body->url;
        $order->sid = $surveyId;
        $order->ordered_by = $userId;
        $order->raw = json_encode(get_object_vars($body->raw));
        $order->status = (string) $body->raw->state;  // 'hold' means waiting for payment
        $order->created = date('Y-m-d H:i:m', time());
        $order->save();

        return json_encode(array('result' => $response->body));
    }

    /**
     * After order is placed, show nBill order form to
     * make payment
     *
     * @todo Remove, use link to open payment in other tab
     * @return string JSON
     */
    public function getNBillOrderForm()
    {
        $curl = new Curl();
        $response = $curl->get(
            "https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&tmpl=component",
            array()
        );

        return json_encode(array('result' => $response->body));
    }

    /**
     * Submit first page of two-page form from nBill.
     * The first page selects payment type (Skrill, Payone, ...)
     *
     * @todo Can't work because of login cookies, third-party cookies etc; must use link to payment site
     * @param LSHttpRequest $request
     * @return string JSON
     */
    public function submitFirstNBillPage(LSHttpRequest $request)
    {
        /*
        $formValues = $request->getParam('formValues');
        $formValues = explode("&", $formValues);
        $formValues2 = array();
        foreach ($formValues as $value)
        {
            $keyAndValue = explode("=", $value);
            $formValues2[$keyAndValue[0]] = $keyAndValue[1];
        }


        $curl = new Curl();
        $response = $curl->post(
            "https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&tmpl=component",
            $formValues2
        );
        */
        return json_encode(array('result' => false));
    }

    /**
     * Get survey information
     */
    public function getSurvey(LSHttpRequest $request)
    {
        $surveyId = $request->getParam('surveyId');
        $survey = Survey::model()->findByPk($surveyId);
        $data = $survey->getAttributes();

        $surveyLanguage = SurveyLanguageSetting::model()->findByPk(array(
            'surveyls_survey_id' => $surveyId,
            'surveyls_language' => $survey->language
        ));
        $data = array_merge($data, $surveyLanguage->getAttributes());

        $user = $this->api->getCurrentUser();

        $link = Yii::app()->createAbsoluteUrl(
            'survey/index',
            array(
                'sid' => $surveyId,
                'lang' => $data['surveyls_language']
            )
        );

        return json_encode(array(
            'result' => json_encode($data),
            'name' => $user->full_name,
            'email' => $user->email,
            'link' => $link
        ));
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
                    || $functionToCall == 'getLoginForm'
                    || $functionToCall == 'getDashboard'
                    || $functionToCall == 'getNBillOrderForm'
                    || $functionToCall == 'submitFirstNBillPage'
                    || $functionToCall == "login"
                    || $functionToCall == "purchaseRequest"
                    || $functionToCall == "getSurvey")
            {
                echo $this->$functionToCall($request);
            }
        }
    }

    /**
     * Get all Cint orders saved on client
     *
     * @return array<CintLinkOrder>
     */
    private function getOrders()
    {
        $orders = CintLinkOrder::model()->findAll();
        return $orders;
    }

    /**
     * Call limesurvey.org to update orders in database
     *
     * @param array<CintLinkOrder> $orders
     * @return array<CintLinkOrder>|false - Returns false if some fetching goes amiss
     */
    private function updateOrders(array $orders)
    {
        Yii::log('updateOrder begin', CLogger::LEVEL_TRACE, 'cintlink');

        $newOrders;
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');

        // Loop through orders and get updated info from Cint
        foreach ($orders as $order)
        {
            Yii::log('loop order ' . $order->url, CLogger::LEVEL_TRACE, 'cintlink');
            $curl = new Curl();
            $response = $curl->get(
                $order->url,
                array()
            );

            // Abort if we got nothing
            if (empty($response))
            {
                Yii::log('updateOrder end with false, empty response', CLogger::LEVEL_TRACE, 'cintlink');
                return false;
            }

            $orderXml = new SimpleXmlElement($response->body);

            $order->raw = $response->body;
            $order->status = (string) $orderXml->state;  // 'hold' means waiting for payment
            $order->modified = date('Y-m-d H:i:m', time());
            $order->save();

            $newOrders[] = $order;

        }

        Yii::log('updateOrder end', CLogger::LEVEL_TRACE, 'cintlink');
        return $newOrders;
    }
}
