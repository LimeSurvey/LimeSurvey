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
    <li id='notification-li' class='dropdown' onclick='LS.styleNotificationMenu(this);'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <?php // Use class 'notification-bell-pulse' for pulsating bell ?>
            <span id='notification-bell' class='fa fa-bell text-warning'></span>
            <span class='badge'><?php echo count($notifications); ?></span>
            <span class='caret'></span>
        </a>

        <ul id='notification-outer-ul' class='dropdown-menu' role='menu'>
            <li style='height: 88%;'>
                <ul id='notification-inner-ul' class='notification-list'>
                    <?php foreach ($notifications as $not): ?>
                        <li>
                            <a 
                                class='admin-notification-link'
                                data-url='<?php echo $not->ajaxUrl; ?>'
                                data-read-url='<?php echo $not->readUrl; ?>'
                                data-update-url='<?php echo Notification::getUpdateUrl($surveyId); ?>'
                                data-type='<?php echo $not->type; ?>'
                                data-status='<?php echo $not->status; ?>'
                                href='#'
                            >
                                <?php if ($not->status == 'new'): ?>
                                    <span class='fa fa-circle'></span>&nbsp;
                                    <strong><?php echo $not->title; ?></strong>
                                    <br />
                                    <span class='text-muted'><?php echo ellipsize($not->message, 50); ?></span>
                                <?php else: ?>
                                    <span class='text-muted'><?php echo $not->title; ?></span>
                                    <br />
                                    <span class='text-muted' style='opacity: 0.5;'><?php echo ellipsize($not->message, 50); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li class="divider"></li>
        
            <li id='notification-clear-all'>
                <a 
                onclick='LS.deleteAllNotifications("<?php echo $clearAllNotificationsUrl; ?>", "<?php echo Notification::getUpdateUrl($surveyId); ?>");' 
                    href='#'
                >
                    <span class='fa fa-trash text-warning'></span>&nbsp;
                    <?php eT('Delete all notifications'); ?>
                </a>
            </li>
        </ul>

    </li>
<?php endif; ?>
