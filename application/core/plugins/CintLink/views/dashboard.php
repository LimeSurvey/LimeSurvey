<!-- Cint widget button (not visible from global dashboard) -->
<?php if (empty($surveyId)): ?>
    <p class='alert alert-info'>
        <span class='fa fa-info-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('To buy participants, please go to the survey specific CintLink view.'); ?>
    </p>
<?php else: ?>
    <button class='btn btn-default' onclick='LS.plugin.cintlink.showWidget();'>
        <span class='fa fa-bars'></span>
        &nbsp;
        <?php echo $plugin->gT('Choose participants'); ?>
    </button>
    <p class='help-block'><?php echo $plugin->gT('Use the Cint widget to buy participants'); ?></p>
<?php endif; ?>

<h4>Orders</h4>
<?php if (count($orders) > 0): ?>
    <table class='table table-striped'>
        <thead>
            <th><?php echo $plugin->gT('ID'); ?></th>
            <th><?php echo $plugin->gT('Created'); ?></th>
            <th><?php echo $plugin->gT('Survey ID'); ?></th>
            <th><?php echo $plugin->gT('Ordered by'); ?></th>
            <th><?php echo $plugin->gT('Status'); ?></th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo substr($order->url, 47); // TODO: This is only correct assuming the base url does not change ?></td>
                    <td><?php echo convertDateTimeFormat($order->created, 'Y-m-d', $dateformatdata['phpdate']) ;?></td>
                    <td><a href='<?php echo $order->getSurveyUrl(); ?>'><?php echo $order->sid; ?></a></td>
                    <td><?php echo $order->user->full_name; ?></td>

                    <!-- Status column -->
                    <?php if ($order->status == 'live'): ?>
                        <td><span class='label label-success'><?php echo $plugin->gT(ucfirst($order->status)); ?></span></td>
                    <?php elseif ($order->status == 'denied'): ?>
                        <td><span class='label label-danger'><?php echo $plugin->gT(ucfirst($order->status)); ?></span></td>
                    <?php elseif ($order->status == 'new'): ?>
                        <td><?php echo $plugin->gT('Under review'); ?></td>
                    <?php elseif ($order->status == 'completed'): ?>
                        <td><span class='fa fa-check'></span>&nbsp;<?php echo $plugin->gT(ucfirst($order->status)); ?></td>
                    <?php else: ?>
                        <td><?php echo $plugin->gT(ucfirst($order->status)); ?></td>
                    <?php endif; ?>

                    <!-- Button column -->
                    <?php if ($order->status == 'hold'): ?>
                        <td>
                            <a 
                                class='btn btn-default btn-sm' 
                                href='https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&ctl_order_id=<?php echo htmlspecialchars($order->url); ?>' 
                                target='_blank'
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
                                <?php echo $plugin->gT('Delete'); ?>
                            </button>
                        </td>
                    <?php elseif ($order->status == 'live'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'denied'): ?>
                        <td></td>
                    <?php elseif ($order->status == 'completed'): ?>
                        <td></td>
                    <?php endif; ?>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <i><?php echo $plugin->gT('No orders made yet'); ?></i>
<?php endif; ?>
