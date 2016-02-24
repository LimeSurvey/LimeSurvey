<?php
/**
 * This file render the headers of the div containing the timer
 * @var $timersessionname
 * @var $timersessionname
 * @var $time_limit
 */
?>
<div class="row" id="timer_header">
    <div class="col-xs-12">
        <input
            type='hidden'
            name='timerquestion'
            value='<?php echo $timersessionname; ?>'
        />

        <input
            type='hidden'
            name='<?php echo $timersessionname; ?>'
            id='<?php echo $timersessionname; ?>'
            value='<?php echo $time_limit; ?>'
        />
