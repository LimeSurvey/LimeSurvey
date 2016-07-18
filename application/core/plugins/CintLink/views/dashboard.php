<button class='btn btn-default' onclick='LS.plugin.cintlink.showWidget();'><?php eT("Show widget"); ?></button>
<p class='help-block'>Use the Cint widget to buy participants</p>

<h4>Orders</h4>
<?php if (count($orders) > 0): ?>
    <table class='table table-striped'>
        <thead>
            <td>Url</td>
            <td>Status</td>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order->url ;?></td>
                    <td><?php echo $order->status; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <i>No orders made yet</i>
<?php endif; ?>
