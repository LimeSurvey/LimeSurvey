<?php

use \ls\menu\MenuItem;
use \ls\menu\Menu;

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
        $this->subscribe('beforeAdminMenuRender');
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
                    'raw' => 'text',  // Order xml
                    'status' => 'string',
                    'ordered_by' => 'int',  // User id
                    'deleted' => 'bool',  // Soft delete
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
                    $this->gT('An non-recoverable error happened during the update. Error details:')
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
            'label' => $this->gT('CintLink'),
            'iconClass' => 'fa fa-table',
            'href' => $href
        ));

        $event->append('menuItems', array($menuItem));
    }

    /**
     * @todo Maybe place somewhere else
     */
    public function beforeAdminMenuRender()
    {
        $event = $this->getEvent();
        $surveyId = $event->get('surveyId');

        $href = $this->api->createUrl(
            'admin/pluginhelper',
            array(
                'sa' => 'fullpagewrapper',
                'plugin' => $this->getName(),
                'method' => 'actionIndexGlobal'
            )
        );

        // Return new menu
        $event = $this->getEvent();
        $event->append('extraMenus', array(
          new Menu(array(
            'label' => $this->gT('CintLink'),
            'href' => $href
          ))
        ));
    }

    /**
     * @return string
     */
    public function actionIndex($surveyId)
    {
        $pluginBaseUrl = Yii::app()->createUrl(
            'plugins/direct',
            array(
                'plugin' => 'CintLink',
                'surveyId' => $surveyId,
            )
        );

        $data = array();
        $data['pluginBaseUrl'] = $pluginBaseUrl;
        $data['surveyId'] = $surveyId;
        $data['common'] = $this->renderPartial('common', $data, true);

        $content = $this->renderPartial('index', $data, true);

        $this->registerCssAndJs();

        return $content;
    }

    /**
     * As actionIndex but survey agnostic
     *
     * @return string
     */
    public function actionIndexGlobal()
    {
        $pluginBaseUrl = Yii::app()->createUrl(
            'plugins/direct',
            array(
                'plugin' => 'CintLink'
            )
        );

        $data = array();
        $data['pluginBaseUrl'] = $pluginBaseUrl;
        $data['common'] = $this->renderPartial('common', $data, true);

        $content = $this->renderPartial('indexGlobal', $data, true);

        $this->registerCssAndJs();

        return $content;
    }

    /**
     * Register CSS and JS
     *
     * @return void
     */
    protected function registerCssAndJs() {
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/js');
        App()->clientScript->registerScriptFile("$assetsUrl/cintlink.js");
        App()->clientScript->registerScriptFile("http://" . $this->cintApiKey . ".cds.cintworks.net/assets/cint-link-1-0-0.js");

        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/css');
        App()->clientScript->registerCssFile("$assetsUrl/cintlink.css");
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
        $content = $this->renderPartial('loginform', $data, true);
        return $content;
    }

    /**
     * Return HTML for survey specific dashboard
     * Called by Ajax
     *
     * @param LSHttpRequest $request
     * @return string
     */
    public function getDashboard(LSHttpRequest $request)
    {
        $surveyId = $request->getParam('surveyId');

        // If surveyId is empty, assume this is the global dashboard
        if (empty($surveyId))
        {
            return $this->getGlobalDashboard();
        }

        $orders = $this->getOrders(array(
            'sid' => $surveyId,
            'deleted' => false
        ));
        $orders = $this->updateOrders($orders);

        $data = array();
        $data['orders'] = $orders;
        $data['surveyId'] = $surveyId;
        $data['dateformatdata'] = getDateFormatData(Yii::app()->session['dateformat']);

        $content = $this->renderPartial('dashboard', $data, true);

        return $content;
    }

    /**
     * Return HTML for global dashboard
     *
     * @return string
     */
    public function getGlobalDashboard()
    {
        $orders = $this->getOrders(array(
            'deleted' => false
        ));
        $orders = $this->updateOrders($orders);

        $data = array();
        $data['orders'] = $orders;
        $data['dateformatdata'] = getDateFormatData(Yii::app()->session['dateformat']);

        $content = $this->renderPartial('dashboard', $data, true);

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
     * Cancel order
     * Run when user click "Cancel" in order table
     * Contact limesurvey.org, because cancel order needs access
     * to secret.
     *
     * @param LSHttpRequest $request
     * @return string JSON
     */
    public function cancelOrder(LSHttpRequest $request)
    {
        $orderUrl = $request->getParam('orderUrl');

        if (empty($orderUrl))
        {
            return json_encode(array('error' => 'Missing order url'));
        }

        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');

        if (empty($limesurveyOrgKey))
        {
            return json_encode(array('error' => 'Missing limesurveyOrgKey - user not logged in?'));
        }

        // DELETE does not support CURLOPT_POSTFIELDS?
        $url = $this->baseURL;
        $url .= '&app=cintlinklimesurveyrestapi';
        $url .= '&format=raw';
        $url .= '&resource=order';
        $url .= '&key=' . $limesurveyOrgKey;
        $url .= '&order_url=' . htmlspecialchars($orderUrl);

        $curl = new Curl();
        $response = $curl->delete($url, array());

        if (empty($response->body))
        {
            return json_encode(array('result' => $this->gT('Order was cancelled')));
        }
        else
        {
            return json_encode(array('result' => $response->body));
        }

    }

    /**
     * Soft delete an order
     *
     * @param LSHttpRequest $request
     * @return void
     */
    public function softDeleteOrder(LSHttpRequest $request)
    {
        $this->log('softDeleteOrder begin');

        $surveyId = $request->getParam('surveyId');
        $url = $request->getParam('orderUrl');

        $this->log('url = ' . $url);
        $this->log('surveyId = ' . $surveyId);

        $order = CintLinkOrder::model()->findByAttributes(
            array(
                'url' => $url,
                'sid' => $surveyId,
                'deleted' => false
            )
        );

        if (empty($order))
        {
            return json_encode(array('error' => $this->gT('Found no order')));
        }

        $order->deleted = true;
        $order->save();

        $this->log("softDeleteOrder end");
        return json_encode(array('result' => $this->gT('Order was deleted')));
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
     *
     * @param LSHttpRequest $request
     * @return string JSON
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
            if ($functionToCall == 'actionIndex' 
                || $functionToCall == 'actionIndexGlobal')
            {
                $content = $this->$functionToCall($request);
                $event->setContent($this, $content);
            }
            else if ($functionToCall == 'checkIfUserIsLoggedInOnLimesurveyorg'
                    || $functionToCall == 'getLoginForm'
                    || $functionToCall == 'getDashboard'
                    || $functionToCall == 'getNBillOrderForm'
                    || $functionToCall == 'submitFirstNBillPage'
                    || $functionToCall == "login"
                    || $functionToCall == "purchaseRequest"
                    || $functionToCall == "cancelOrder"
                    || $functionToCall == "softDeleteOrder"
                    || $functionToCall == "getSurvey")
            {
                echo $this->$functionToCall($request);
            }
        }
    }

    /**
     * Get all Cint orders saved on client
     *
     * @param array $conditions Like array('deleted' => false, ...)
     * @return array<CintLinkOrder>
     */
    protected function getOrders($conditions)
    {
        $orders = CintLinkOrder::model()->findAllByAttributes(
            $conditions,
            array('order' => 'url DESC')
        );
        return $orders;
    }

    /**
     * Call limesurvey.org to update orders in database
     *
     * @param array<CintLinkOrder> $orders
     * @return array<CintLinkOrder>|false - Returns false if some fetching goes amiss
     */
    protected function updateOrders(array $orders)
    {
        $this->log('updateOrder begin');

        $newOrders = array();
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');

        // Loop through orders and get updated info from Cint
        foreach ($orders as $order)
        {
            $this->log('loop order ' . $order->url);

            if ($order->status == 'cancelled'
                || $order->status == 'completed'
                || $order->status == 'closed')
            {
                $this->log('Don\'t fetch anything for cancelled/completed/closed order, skipped');
                $newOrders[] = $order;
                continue;
            }

            $curl = new Curl();
            $response = $curl->get(
                $order->url,
                array()
            );

            // Abort if we got nothing
            if (empty($response))
            {
                $this->log('updateOrder end with false, empty response');
                return false;
            }

            $orderXml = new SimpleXmlElement($response->body);

            $order->raw = $response->body;
            $order->status = (string) $orderXml->state;  // 'hold' means waiting for payment
            $order->modified = date('Y-m-d H:i:m', time());
            $order->save();

            $newOrders[] = $order;

        }

        $this->log('updateOrder end');
        return $newOrders;
    }

}
