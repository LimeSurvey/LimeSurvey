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
<div class='col-sm-12 questionmancontainer'>
    <div id='question<?php echo $iQid; ?>_timer' class='alert alert-info' style='<?php echo $time_limit_message_style; ?>'>
        <?php echo $time_limit_message; ?>
    </div>
</div>

<div class='col-sm-12 questionmancontainer'>
    <div id='LS_question<?php echo $iQid; ?>_warning' class='alert alert-danger' style='<?php echo $time_limit_warning_style; ?>'>
        <?php echo $time_limit_warning_message; ?>
    </div>
</div>

<div class='col-sm-12 questionmancontainer'>
    <div id='LS_question<?php echo $iQid; ?>_warning_2' class='alert alert-danger' style='<?php echo $time_limit_warning_2_style; ?>'>
        <?php echo $time_limit_warning_2_message; ?>
    </div>
</div>

<div class='col-sm-12 questionmancontainer'>
    <div id='LS_question<?php echo $iQid; ?>_Timer' class='alert alert-warning' style='<?php echo $time_limit_timer_style; ?>'>
    </div>
</div>
