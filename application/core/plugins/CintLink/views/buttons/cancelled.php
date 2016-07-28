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
