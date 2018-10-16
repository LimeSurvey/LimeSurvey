<?php
/**
 * This file render the footer of the timed question
 *
 * TODO : replace php variable by hidden input or javascript var
 *
 * @var $iQid
 * @var $time_limit
 * @var $time_limit_action
 * @var $time_limit_warning
 * @var $time_limit_warning_2
 * @var $time_limit_warning_display_time
 * @var $time_limit_warning_display_time
 * @var $time_limit_warning_2_display_time
 * @var $disable
 */
Yii::app()->getClientScript()->registerScript("TimerQuestion".$iQid, "
    countdown($iQid, $time_limit, $time_limit_action, $time_limit_warning, $time_limit_warning_2, $time_limit_warning_display_time, $time_limit_warning_2_display_time, '$disable');
", LSYii_ClientScript::POS_POSTSCRIPT);
?>
</div>
