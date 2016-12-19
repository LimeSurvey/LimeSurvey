<!-- CintLink side-menu content -->

<li>
    <a id='cintlink-sidemenu-button' href='<?php echo $href; ?>'>
        <span class='cintlink-icons cinticon cintlink-icon-sidemenu'></span>
        <?php echo $plugin->gT('CintLink'); ?>
    </a>
</li>
<?php if (count($orders) > 0): ?>
    <li class='cintlink-no-hover'>
            <table class='table table-striped cintlink-sidemenu-table'>
                <thead>
                    <th><?php echo $plugin->gT('ID'); ?></th>
                    <th><?php echo $plugin->gT('Country'); ?></th>
                    <th><?php echo $plugin->gT('Status'); ?></th>
                </thead>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order->getShortId(); ?></td>
                    <td><?php echo $order->country; ?></td>
                    <td><?php echo $order->getStyledStatus(); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
    </li>
<?php endif; ?>
