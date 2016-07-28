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

        $criteria = new CDbCriteria;
        $criteria->addCondition('deleted = false');

        if ($surveyId !== null)
        {
            $criteria->addCondition('sid = ' . $surveyId);
        }

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
                return gT('Under review');
                break;
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

                $orderedByMe = $this->ordered_by == $data['user']->id;
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

}
