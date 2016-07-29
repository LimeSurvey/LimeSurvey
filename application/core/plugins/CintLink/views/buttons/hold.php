<a 
    class='btn btn-default btn-sm <?php echo $readonly; ?> ' 
    href='https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=" . htmlspecialchars($this->url) . "' 
    target='_blank'
    <?php if ($order->ordered_by != $user->id): ?>
        data-toggle='tooltip'
        title='<?php echo $plugin->gT('You can only pay for orders you placed your self.'); ?>'
        onclick='return false;'
    <?php elseif ($survey->active != 'Y'): ?>
        data-toggle='tooltip'
        title='<?php echo $plugin->gT('You can only pay for an order when the survey is active.'); ?>'
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
