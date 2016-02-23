<?php
/**
 * This file render the content of the timed question
 * @var $iQid  => $ia[0]
 * @var $time_limit_message_style
 * @var $time_limit_message
 * @var $time_limit_warning_style
 * @var $time_limit_warning_message
 * @var $time_limit_warning_2_style
 * @var $time_limit_warning_2_message
 * @var $time_limit_timer_style
 * @var
 */
?>
<div id='question<?php echo $iQid; ?>_timer' style='<?php echo $time_limit_message_style; ?>'>
    <?php echo $time_limit_message; ?>
</div>
<div id='LS_question<?php echo $iQid; ?>_warning' style='<?php echo $time_limit_warning_style; ?>'>
    <?php echo $time_limit_warning_message; ?>
</div>

<div id='LS_question<?php echo $iQid; ?>_warning_2' style='<?php echo $time_limit_warning_2_style; ?>'>
    <?php echo $time_limit_warning_2_message; ?>
</div>

<div id='LS_question<?php echo $iQid; ?>_Timer' style='<?php echo $time_limit_timer_style; ?>'>

</div>
