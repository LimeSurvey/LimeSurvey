<?php

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
            'url'=>array(
            'desc'=>'url desc'
        ));
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
        $criteria->addCondition('deleted = false');

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
        $plugin = Yii::app()->getPlugin();
        switch ($this->status)
        {
            case 'live':
                return '<span class="label label-success">' . $plugin->gT(ucfirst($this->status)) . '</span>';
                break;
            case 'denied':
                return '<span class="label label-danger">' . $plugin->gT(ucfirst($this->status)) . '</span>';
                break;
            case 'new':
                return $plugin->gT('Under review');
                break;
            case 'hold':
                return $plugin->gT('Wating for payment');
            default:
                return $plugin->gT(ucfirst($this->status));
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
        foreach ($targetGroup->children() as $target) {
            $content = (string) $target->name;
            if ($content != '')
            {
                $result .= (string) $target->name . ', ';
            }
        }
        $result = trim($result, ', ');
        return $result;
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
     * Returns true if survey has any blocking Cint orders, no matter the state.
     * A blocking order is in state hold, new or live (not completed, deleted, or cancelled)
     * @param int $surveyId
     * @return boolean
     */
    public static function hasAnyBlockingOrders($surveyId)
    {
        if (empty($surveyId))
        {
            throw new InvalidArgumentException('surveyId cannot be null or empty');
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('deleted = false AND status != \'completed\' AND status != \'cancelled\'');
        $criteria->addCondition('sid = ' . $surveyId);  // TODO: Escape
        $count = CintLinkOrder::model()->count($criteria);
        return $count > 0;
    }

}
