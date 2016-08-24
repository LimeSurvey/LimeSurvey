<?php

require_once(__DIR__ . "/../CintLinkAPI.php");

class CintNotLoggedInException extends Exception {}

/**
 * Order model
 *
 * Pretty crude, just containing url, raw (xml) and status
 *
 * @since 2017-07-15
 * @author Olle Haerstedt
 */
class CintLinkOrder extends CActiveRecord
{
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    public function tableName()
    {
        return '{{plugin_cintlink_orders}}';
    }

    public function primaryKey()
    {
        return 'url';
    }

    /**
     * Get survey URL for belonging survey
     */
    public function getSurveyUrl() {
        return Yii::app()->createUrl('admin/survey/sa/view/', array('surveyid' => $this->sid));
    }

    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'ordered_by'),
        );
    }

    /**
     * Search method provided to TbGridView widget

     * @return CActiveDataProvider
     */
    public function search($surveyId = null)
    {
        $pageSize = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'url' => array(
                'desc'=>'url desc'
            ),
            'created',
            'ordered_by',
            'status',
            'country'
        );
        $sort->defaultOrder = array('url' => CSort::SORT_DESC);

        $criteria = $this->getCriteria($surveyId);

        $dataProvider = new CActiveDataProvider('CintLinkOrder', array(
            'sort' => $sort,
            'criteria'=>$criteria,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));

        return $dataProvider;

    }

    /**
     * Get search criteria for dashboard
     *
     * @param int|null $surveyId
     * @return CDbCriteria
     */
    protected function getCriteria($surveyId) {
        $criteria = new CDbCriteria;
        $criteria->addCondition('deleted = 0');

        // In survey specific dashboard we will have survey id
        if ($surveyId !== null)
        {
            $criteria->addCondition('sid = ' . $surveyId);
        }
        else
        {
            // In global dashboard, if you're super admin you
            // will see all surveys, but if not, show only
            // Cint orders for the surveys you own.
            if (Permission::model()->hasGlobalPermission('superadmin'))
            {
                // Don't add more conditions, superadmin sees all
            }
            else
            {
                // Find all surveys the user own
                $surveysUserOwns = Survey::model()->findAllByAttributes(
                    array(
                        'owner_id' => Yii::app()->user->id
                    )
                );

                if (empty($surveysUserOwns))
                {
                    // No superadmin and no owner, so make impossible condition
                    $criteria->addCondition('sid = -1');
                }
                else
                {
                    // Add conditions like (sid = 1 OR sid = 2 OR ...)
                    $sidCriteria = new CDbCriteria;
                    foreach ($surveysUserOwns as $survey)
                    {
                        $sidCriteria->addCondition('sid = ' . $survey->sid, 'OR');
                    }
                    $criteria->mergeWith($sidCriteria);
                }
            }
        }

        Yii::app()->getPlugin()->log('criteria = ' . json_encode($criteria));

        return $criteria;
    }

    /**
     * Used in grid view
     *
     * @return string
     */
    public function getFormattedCreatedDate()
    {
        $dateformatdata = getDateFormatData(Yii::app()->session['dateformat']);
        return convertDateTimeFormat($this->created, "Y-m-d", $dateformatdata["phpdate"]);
    }

    /**
     * Used in grid view
     * @return string
     */
    public function getSurveyIdLink()
    {
        return '<a href="' . $this->getSurveyUrl() . '">' . $this->sid . '</a>';
    }

    /**
     * Used in grid view
     * @return string
     */
    public function getCompletedCheck()
    {
        if ($this->status == 'completed')
        {
            return '<span class="fa fa-check"></span>';
        }
        else
        {
            return '';
        }
    }

    /**
     * Used in grid view
     * @return string
     */
    public function getStyledStatus()
    {
        // NB: Can't use $plugin->gT here because it will be called for side-menu (not plugin action).
        switch ($this->status)
        {
            case 'live':
                return '<span class="label label-success">' . gT(ucfirst($this->status)) . '</span>';
                break;
            case 'denied':
                return '<span class="label label-danger">' . gT(ucfirst($this->status)) . '</span>';
                break;
            case 'new':
                return gT('Under review');
                break;
            case 'hold':
                return gT('Wating for payment');
            default:
                return gT(ucfirst($this->status));
                break;
        }
    }

    /**
     * Used in grid view
     * @return string
     */
    public function getButtons()
    {
        $plugin = Yii::app()->getPlugin();
        switch ($this->status)
        {
            case 'hold':

                $data = array();
                $data['order'] = $this;
                $data['user'] = Yii::app()->user;
                $data['survey'] = Survey::model()->findByPk($this->sid);

                $orderedByMe = $this->ordered_by == $data['user']->id;
                $surveyIsActive = $data['survey']->active != 'N';
                $data['readonly'] = $orderedByMe ? '' : 'readonly';

                return $plugin->renderPartial('buttons.hold', $data, true);

                break;

            case 'cancelled':

                $data = array();
                $data['order'] = $this;
                return $plugin->renderPartial('buttons.cancelled', $data, true);

                break;

            case 'new':
            case 'live':
            case 'denied':
            case 'completed':
                // Empty td
                break;
        }
        
    }

    /**
     * Traverse raw HTML and get target groups info.
     * Used in grid view.
     * @return string
     */
    public function getTargetGroup()
    {

        $result = '';
        $raw = $this->raw;
        $xml = new SimpleXmlElement($raw);
        $targetGroup = $xml->{'target-group'};
        foreach ($targetGroup->children() as $tagName => $target) {
            if ($tagName == 'country')
            {
                // In separate column
                continue;
            }
            if ($tagName == 'variable')
            {
                $result .= $this->formatGlobalVariable($target) . ', ';
            }
            else
            {
                $content = (string) $target->name;
                if ($content != '')
                {
                    $result .= (string) $target->name . ', ';
                }
            }
        }
        $result = trim($result, ', ');

        if ($result == '')
        {
            $result = '&#8211;';
        }

        return $result;
    }

    /**
     * Format global variable
     * @param SimpleXmlElement $target
     * @return string
     */
    protected function formatGlobalVariable(SimpleXmlElement $target)
    {
        $globalVars = Yii::app()->getPlugin()->getGlobalVariables();
        $id = (string) $target->id;
        $allValues = $this->flattenGlobalVariables($globalVars);
        return $allValues[$id]['text'];
    }

    /**
     * Flatten the XML into an array
     * @param SimpleXmlElement $globalVars
     * @return array
     */
    protected function flattenGlobalVariables($globalVars)
    {
        $result = array();

        // Only loop once
        static $allValues = array();

        if (empty($allValues))
        {
            foreach ($globalVars->children() as $vars) {
                $id = (string) $vars->id;
                $result[$id]['text'] = $vars->text;
                $result[$id]['values'] = array();

                foreach ($vars->values->children() as $value) {
                    $valueId = (string) $value['id'];
                    $result[$id]['values'][$valueId] = (string) $value;

                    $allValues[$valueId]['text'] = (string) $value;
                    $allValues[$valueId]['variable-id'] = $id;
                }
            }
        }
        return $allValues;
    }

    /**
     * Get price from raw XML
     * @return string
     */
    public function getPrice()
    {
        $xml = new SimpleXmlElement($this->raw);
        return (string) $xml->quote . '&#8364;';  // Euro sign
    }

    /**
     * Get number of ordered complete surveys from raw XML
     * @return string
     */
    public function getCompletes()
    {
        $xml = new SimpleXmlElement($this->raw);
        return (string) $xml->{'number-of-completes'};
    }

    /**
     * Age
     * Used in grid view
     * @return string
     */
    public function getAge()
    {
        $minAge = null;
        $maxAge = null;
        $xml = new SimpleXmlElement($this->raw);
        $targetGroup = $xml->{'target-group'};
        foreach ($targetGroup->children() as $tag => $target) {
            if ($tag == 'min-age')
            {
                $minAge = (string) $target;
            }
            else if ($tag == 'max-age')
            {
                $maxAge = (string) $target;
            }
        }

        $result = $minAge . '-' . $maxAge;
        if (strlen($result) == 3)  // E.g. 15- or 31-
        {
            $result[2] = '+';
        }

        return $result;
    }

    /**
     * Substring of URL
     * Used in grid view
     * @return string
     */
    public function getShortId()
    {
        return substr($this->url, 47);
    }

    /**
     * Updates order with information from Cint via limesurvey.org.
     * User must be logged in on limesurvey.org.
     * @return CintLinkOrder
     * @throws Exception if user is not logged in at limesurvey.org
     */
    public function updateFromCint()
    {
        $limesurveyOrgKey = Yii::app()->user->getState('limesurveyOrgKey');

        if (empty($limesurveyOrgKey))
        {
            throw new CintNotLoggedInException('User is not logged in at limesurvey.org');
        }

        // URL to LimeSurvey is stored in Cint plugin
        //$baseURL = Yii::app()->getPlugin()->baseURL;

        $curl = new Curl();
        $response = $curl->get(
            CintLink::$baseURL,
            array(
                'app' => 'cintlinklimesurveyrestapi',
                'format' => 'raw',
                'resource' => 'order',
                'orderUrl' => $this->url,
                'key' => $limesurveyOrgKey
            )
        );
        // Double up!
        $response = json_decode(json_decode($response));

        // Abort if we got nothing
        if (empty($response))
        {
            $this->log('Got empty response from Cint while update');
            throw new Exception('Got empty response from Cint while update');
        }

        $orderXml = new SimpleXmlElement($response->body);

        $this->raw = $response->body;
        $this->status = (string) $orderXml->state;  // 'hold' means waiting for payment
        $this->modified = date('Y-m-d H:i:m', time());
        $this->save();

        return $this;
    }

    /**
     * Update a bunch of orders
     * @param CintLinkOrder[]|int $orders Or survey id
     * @return CintLinkOrder[]|null
     */
    public static function updateOrders($orders)
    {
        // If $orders is an int, it's the survey id
        if (is_int($orders) || is_string($orders))
        {
            $surveyId = intval($orders);
            $orders = self::getOrders($surveyId);
        }

        if (empty($orders))
        {
            return null;
        }

        $newOrders = array();

        // Loop through orders and get updated info from Cint
        foreach ($orders as $order)
        {
            // No need to update these, since they will never change
            if ($order->status == 'cancelled'
                || $order->status == 'completed'
                || $order->status == 'closed')
            {
                $newOrders[] = $order;
                continue;
            }

            $newOrders[] = $order->updateFromCint();
        }

        return $newOrders;
    }

    /**
     * Get all orders for this survey that are not deleted
     * @param int $surveyId
     * @return CintLinkOrder[]
     */
    public static function getOrders($surveyId)
    {
        $conditions = array(
            'sid' => $surveyId,
            'deleted' => 0
        );
        $orders = self::model()->findAllByAttributes(
            $conditions,
            array('order' => 'url DESC')
        );
        return $orders;
    }

    /**
     * Returns true if survey has any blocking Cint orders, no matter the state.
     * A blocking order is in state hold, new or live (not completed, deleted, denied or cancelled)
     * @param int $surveyId
     * @return boolean
     */
    public static function hasAnyBlockingOrders($surveyId)
    {
        if (empty($surveyId))
        {
            throw new InvalidArgumentException('surveyId cannot be null or empty');
        }

        $surveyId = intval($surveyId);
        $criteria = new CDbCriteria();
        $criteria->addCondition('deleted = 0');
        $criteria->addCondition('status = \'hold\' OR status = \'new\' OR status = \'live\'');
        $criteria->addCondition('sid = ' . $surveyId);
        $count = CintLinkOrder::model()->count($criteria);
        return $count > 0;
    }

    /**
     * Returns true if ALL orders are completed OR cancelled.
     * Returns false if there are no orders.
     *
     * All orders are complete if:
     *   there are any orders
     *   at least one order is completed.
     *   no order is blocking (meaning all orders are 'completed' or 'cancelled')
     *
     * @param int $surveyId
     * @return boolean
     * @todo Can this be done in one query?
     */
    public static function allOrdersAreCompleted($surveyId)
    {
        $orders = self::getOrders($surveyId);

        if (self::hasAnyOrders($surveyId) &&
            self::anyOrderHasStatus($orders, 'completed') &&
            !self::hasAnyBlockingOrders($surveyId))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns true if this survey has ANY Cint order related to it, in any state.
     * @param int $surveyId
     * @return boolean
     */
    public static function hasAnyOrders($surveyId)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('deleted = 0');
        $criteria->addCondition('sid = ' . $surveyId);  // TODO: Escape
        $count = CintLinkOrder::model()->count($criteria);
        return $count > 0;
    }

    /**
     * Returns true if any order in $orders is in any state in $statuses.
     * Make sure to run updateOrders on $orders before calling this.
     *
     * @param array<CintLinkOrder>|CintLinkOrder $orders
     * @param array|string $statuses Array of status to check for
     * @return boolean
     */
    public static function anyOrderHasStatus($orders, $statuses)
    {
        if (empty($orders))
        {
            return false;
        }

        if (!is_array($orders))
        {
            $orders = array($orders);
        }

        if (!is_array($statuses))
        {
            $statuses = array($statuses);
        }

        foreach ($orders as $order)
        {
            if (in_array($order->status, $statuses))
            {
                return true;
            }
        }
        return false;
    }

}
