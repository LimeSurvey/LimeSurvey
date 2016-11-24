<?php
/**
 * This file render the javascript for the timer
 * TODO : replace all php statement by JavaScript on hidden html input
 * @var $iAction
 */
?>
<!-- Some var for timer script, no need to post -->
<input
    type='hidden'
    name='timerquestion'
    value='<?php echo $timersessionname; ?>'
    disabled
/>
<input
    type='hidden'
    name='<?php echo $timersessionname; ?>'
    id='<?php echo $timersessionname; ?>'
    value='<?php echo $time_limit; ?>'
    disabled
/>
<input
    type='hidden'
    name='action-<?php echo $timersessionname; ?>'
    id='action-<?php echo $timersessionname; ?>'
    value='<?php echo intval($iAction); ?>'
    disabled
/>
<input
    type='hidden'
    name='disablenext-<?php echo $timersessionname; ?>'
    id='disablenext-<?php echo $timersessionname; ?>'
    value=<?php echo intval($disable_next); ?>
    disabled
/>
<input
    type='hidden'
    name='disableprev-<?php echo $timersessionname; ?>'
    id='disableprev-<?php echo $timersessionname; ?>'
    value='<?php echo intval($disable_prev); ?>'
    disabled
/>
<input
    type='hidden'
    name='message-delay-<?php echo $timersessionname; ?>'
    id='message-delay-<?php echo $timersessionname; ?>'
    value='<?php echo floatval($time_limit_message_delay); ?>'
    disabled
/>
<div id='countdown-message-<?php echo $timersessionname; ?>' class="hidden">
<?php echo $time_limit_countdown_message; ?>
</div>
