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
<div id='LS_question<?php echo $iQid; ?>_Timer' class='<?php echo $time_limit_timer_class; ?> alert alert-info' style='<?php echo $time_limit_timer_style; ?>'>
</div>
<div id='LS_question<?php echo $iQid; ?>_warning' class='<?php echo $time_limit_warning_class; ?> alert alert-warning' style='<?php echo $time_limit_warning_style; ?>'>
    <?php echo $time_limit_warning_message; ?>
</div>
<div id='LS_question<?php echo $iQid; ?>_warning_2' class='<?php echo $time_limit_warning_2_class; ?> alert alert-warning' style='<?php echo $time_limit_warning_2_style; ?>'>
    <?php echo $time_limit_warning_2_message; ?>
</div>
<div id='question<?php echo $iQid; ?>_timer' class='<?php echo $time_limit_message_class; ?> alert alert-danger' style='<?php echo $time_limit_message_style; ?>'>
    <?php echo $time_limit_message; ?>
</div>
