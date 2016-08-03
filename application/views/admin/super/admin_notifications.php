<!-- Admin notification system -->
<?php if ($nrOfNotifications === 0): ?>
    <li id='notification-li' class='dropdown'>
        <a aria-expanded='false' 
            href='#'>
            <span class='fa fa-bell text-muted'></span>
        </a>
    </li>
<?php elseif($showLoader): ?>
<li id='notification-li' class='dropdown' onclick='LS.updateNotificationWidget("<?php echo Notification::getUpdateUrl($surveyId); ?>");'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <?php // Use class 'notification-bell-pulse' for pulsating bell ?>
            <span id='notification-bell' class='fa fa-bell text-warning'></span>
            <span class='badge'><?php echo $nrOfNotifications; ?></span>
            <span class='caret'></span>
        </a>
        <ul class='dropdown-menu' role='menu'>
            <li>
                <a><span class='fa fa-spinner fa-spin'></span></a>
            </li>
        </ul>
    </li>
<?php else: ?>
    <li id='notification-li' class='dropdown'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <?php // Use class 'notification-bell-pulse' for pulsating bell ?>
            <span id='notification-bell' class='fa fa-bell text-warning'></span>
            <span class='badge'><?php echo count($notifications); ?></span>
            <span class='caret'></span>
        </a>

        <ul class='dropdown-menu' role='menu'>
            <?php foreach ($notifications as $not): ?>
                <li>
                    <a 
                        class='admin-notification-link'
                        data-url='<?php echo $not->ajaxUrl; ?>'
                        data-read-url='<?php echo $not->readUrl; ?>'
                        data-update-url='<?php echo Notification::getUpdateUrl($surveyId); ?>'
                        data-type='<?php echo $not->type; ?>'
                        href='#'
                    >
                        <?php echo $not->title; ?>
                        <br />
                        <span class='text-muted'><?php echo ellipsize($not->message, 50); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="divider"></li>
            <li>
                <a href='<?php echo $clearAllNotificationsUrl; ?>'><?php eT('Clear all notifications'); ?></a>
            </li>
        </ul>

    </li>
<?php endif; ?>
