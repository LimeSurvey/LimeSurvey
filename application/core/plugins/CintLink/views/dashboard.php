<button class='btn btn-default' onclick='LS.plugin.cintlink.showWidget();'>
    <span class='fa fa-bars'></span>
    &nbsp;
    <?php echo $__plugin->gT("Show widget"); ?>
</button>
<p class='help-block'>Use the Cint widget to buy participants</p>

<h4>Orders</h4>
<?php if (count($orders) > 0): ?>
    <table class='table table-striped'>
        <thead>
            <th>Url</th>
            <th><?php echo $__plugin->gT('Created'); ?></th>
            <th><?php echo $__plugin->gT('Status'); ?></th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order->url ;?></td>
                    <td><?php echo convertDateTimeFormat($order->created, 'Y-m-d', $dateformatdata['phpdate']) ;?></td>

                    <!-- Status column -->
                    <?php if ($order->status == 'live'): ?>
                        <td><span class='label label-success'><?php echo $__plugin->gT(ucfirst($order->status)); ?></span></td>
                    <?php elseif ($order->status == 'denied'): ?>
                        <td><span class='label label-danger'><?php echo $__plugin->gT(ucfirst($order->status)); ?></span></td>
                    <?php else: ?>
                        <td><?php echo $__plugin->gT(ucfirst($order->status)); ?></td>
                    <?php endif; ?>

                    <!-- Button column -->
                    <?php if ($order->status == 'hold'): ?>
                        <td>
                            <a 
                                class='btn btn-default btn-sm' 
                                href="https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=<?php echo htmlspecialchars($order->url); ?>" 
                                target="_blank"
                            >
                                <span class='fa fa-credit-card'></span>
                                &nbsp;
                                <?php echo $__plugin->gT("Pay now"); ?>
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
                                <?php echo $__plugin->gT("Cancel"); ?>
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
                                <?php echo $__plugin->gT("Delete"); ?>
                            </button>
                        </td>
                    <?php elseif ($order->status == 'live'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'denied'): ?>
                        <td></td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <i><?php echo $__plugin->gT("No orders made yet"); ?></i>
<?php endif; ?>
