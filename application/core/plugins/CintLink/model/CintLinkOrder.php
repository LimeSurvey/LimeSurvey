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
    public function search()
    {
        $pageSize = Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->attributes = array(
            'url'=>array(
            'desc'=>'url desc'
        ));

        $dataProvider = new CActiveDataProvider('CintLinkOrder', array(
            'sort' => $sort,
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
     * @todo Use $plugin->gT instead of gT - how??
     * @return string
     */
    public function getStyledStatus()
    {
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
            /*
                    <!-- Button column -->
                    <?php if ($order->status == 'hold'): ?>
                        <td>
                            <a 
                                class='btn btn-default btn-sm <?php if ($order->ordered_by != $user->id): echo 'readonly'; endif; ?>' 
                                href='https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=<?php echo htmlspecialchars($order->url); ?>' 
                                target='_blank'
                                <?php if ($order->ordered_by != $user->id): ?>
                                    data-toggle='tooltip'
                                    title='<?php echo $plugin->gT('You can only pay for orders you placed your self.'); ?>'
                                    onclick='return false;'
                                <?php endif; ?>
                            >
                                <span class='fa fa-credit-card'></span>
                                &nbsp;
                                <?php echo $plugin->gT('Pay now'); ?>
                            </a>
                            &nbsp;
                            <button
                                data-toggle='modal'
                                data-target='#confirmation-modal'
                                data-onclick='(function() { LS.plugin.cintlink.cancelOrder("<?php echo $order->url; ?>"); })'
                                class='btn btn-warning btn-sm'
                            >
                                <span class='fa fa-ban'></span>
                                &nbsp;
                                <?php echo $plugin->gT('Cancel'); ?>
                            </button>
                        </td>
                    <?php elseif ($order->status == 'new'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'cancelled'): ?>
                        <td>
                            <button
                                data-toggle='modal'
                                data-target='#confirmation-modal'
                                data-onclick='(function() { LS.plugin.cintlink.softDeleteOrder("<?php echo $order->url; ?>"); })'
                                class='btn btn-warning btn-sm'
                            >
                                <span class='fa fa-trash'></span>
                                &nbsp;
                                <?php echo $plugin->gT('Delete'); ?>
                            </button>
                        </td>
                    <?php elseif ($order->status == 'live'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'denied'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'completed'): ?>
                        <td></td>
                    <?php endif; ?>
                    */
        $plugin = Yii::app()->getPlugin();
        switch ($this->status)
        {
            case 'hold':
                $orderedByMe = $this->ordered_by == $this->user->uid;
                $readonly = $orderedByMe ? 'readonly' : '';
                return $plugin->renderPartial('buttons.hold', array(), true);

                //Yii::app()->getPlugin()->renderPartial();  // Possible if we modify LSYii_Application
                //Yii::app()->getController()->renderPartial(); // <--- PluginController
                //CintLinkController->renderPartial();
                /*
                return "
                    <a 
                        class='btn btn-default btn-sm " . $readonly . "' 
                        href='https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=" . htmlspecialchars($this->url) . "' 
                        target='_blank'
                        <?php if ($order->ordered_by != $user->id): ?>
                            data-toggle='tooltip'
                            title='<?php echo $plugin->gT('You can only pay for orders you placed your self.'); ?>'
                            onclick='return false;'
                        <?php endif; ?>
                    >
                        <span class='fa fa-credit-card'></span>
                        &nbsp;
                        <?php echo $plugin->gT('Pay now'); ?>
                    </a>
                    &nbsp;
                    <button
                        data-toggle='modal'
                        data-target='#confirmation-modal'
                        data-onclick='(function() { LS.plugin.cintlink.cancelOrder("<?php echo $order->url; ?>"); })'
                        class='btn btn-warning btn-sm'
                    >
                        <span class='fa fa-ban'></span>
                        &nbsp;
                        <?php echo $plugin->gT('Cancel'); ?>
                    </button>
                ";
                */
                break;
            case 'cancelled':
                return "
                    <button
                        data-toggle='modal'
                        data-target='#confirmation-modal'
                        data-onclick='(function() { LS.plugin.cintlink.softDeleteOrder(\"" . $this->url . "\"); })'
                        class='btn btn-warning btn-sm'
                    >
                        <span class='fa fa-trash'></span>
                        &nbsp;
                        " . gT('Delete') . "
                    </button>
                ";
                break;
        }
        
    }

}
