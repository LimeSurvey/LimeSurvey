<button class='btn btn-default' onclick='LS.plugin.cintlink.showWidget();'><?php eT("Show widget"); ?></button>
<p class='help-block'>Use the Cint widget to buy participants</p>

<h4>Orders</h4>
<?php if (count($orders) > 0): ?>
    <table class='table table-striped'>
        <thead>
            <td>Url</td>
            <td>Status</td>
            <td></td>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order->url ;?></td>
                    <td><?php echo $order->status; ?></td>
                    <?php if ($order->status == 'hold'): ?>
                        <td>
                            <a class='btn btn-default btn-sm' href="https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=<?php echo htmlspecialchars($order->url); ?>" target="_blank"><?php eT("Pay now"); ?></a>
                            &nbsp;
                            <button class='btn btn-default btn-sm' onclick='LS.plugin.cintlink.cancelOrder("<?php echo $order->url; ?>");' ><?php eT("Cancel"); ?></button>
                        </td>
                    <?php elseif ($order->status == 'new'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'cancelled'): ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <i><?php eT("No orders made yet"); ?></i>
<?php endif; ?>
