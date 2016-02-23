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
?>
    <!-- Call the countdown script -->
    <script type='text/javascript'>
        $(document).ready(function()
        {
            countdown(<?php echo $iQid; ?>, <?php echo $time_limit; ?>, <?php echo $time_limit_action; ?>, <?php echo $time_limit_warning; ?>, <?php echo $time_limit_warning_2; ?>, <?php echo $time_limit_warning_display_time; ?>, <?php echo $time_limit_warning_2_display_time; ?>, '<?php echo $disable; ?>');
        });
    </script>

    </div>
</div>
