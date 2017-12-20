<!-- Admin notification system -->
<?php if ($nrOfNotifications == 0): ?>
    <li id='notification-li' class='dropdown'>
        <a aria-expanded='false' 
            href='#'>
            <span class='fa fa-bell text-muted'></span>
	    <span class='sr-only'>Notifications</span>
        </a>
    </li>
<?php elseif($showLoader): ?>
<li id='notification-li' class='dropdown' onclick='LS.updateNotificationWidget("<?php echo $updateUrl; ?>");' >
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <?php // Use class 'notification-bell-pulse' for pulsating bell ?>
            <span id='notification-bell' class='fa fa-bell <?php echo $bellColor; ?>'></span>

            <?php if ($nrOfNewNotifications): ?>
                <span class='badge'><?php echo $nrOfNewNotifications; ?></span>
            <?php endif; ?>

            <span class='caret'></span>
        </a>
        <ul class='dropdown-menu' role='menu'>
            <li>
                <a><span class='fa fa-spinner fa-spin'></span><span class='sr-only'>Loading notifications</span></a>
            </li>
        </ul>
    </li>
<?php else: ?>
    <li id='notification-li' class='dropdown' onclick='LS.styleNotificationMenu();'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <?php // Use class 'notification-bell-pulse' for pulsating bell ?>
            <span id='notification-bell' class='fa fa-bell <?php echo $bellColor; ?>'></span>

            <?php if ($nrOfNewNotifications): ?>
                <span class='badge'><?php echo $nrOfNewNotifications; ?></span>
            <?php endif; ?>

            <span class='caret'></span>
        </a>

        <ul id='notification-outer-ul' class='dropdown-menu' role='menu'>
            <li id='notification-inner-li' style='height: 88%;'>
                <ul id='notification-inner-ul' class='notification-list'>
                    <?php foreach ($notifications as $not): ?>
                        <li>
                            <a 
                                class='admin-notification-link'
                                data-url='<?php echo $not->ajaxUrl; ?>'
                                data-read-url='<?php echo $not->readUrl; ?>'
                                data-update-url='<?php echo $updateUrl; ?>'
                                data-importance='<?php echo $not->importance; ?>'
                                data-status='<?php echo $not->status; ?>'
                                href='#'
                            >
                                <?php if ($not->status == 'new'): ?>
                                    <span class='fa fa-circle text-<?php echo $not->display_class; ?>'></span>&nbsp;
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
            <li id='notification-divider' class="divider"></li>

            <li id='notification-clear-all'>
                <a 
                    href='#'
                    onclick='(function() { LS.deleteAllNotifications("<?php echo $clearAllNotificationsUrl ?>", "<?php echo $updateUrl; ?>"); })()'
                >
                    <span class='fa fa-trash text-warning'></span>&nbsp;
                    <?php eT('Delete all notifications'); ?>
                </a>
            </li>
        </ul>

    </li>
<?php endif; ?>
<?php
    $notificationLanguageString=array(
        'errorTitle' => gT("Error : %s"),
        'errorUnknow' => gT("unknown"),
        'unknowText' => gT("An unknown error occurred"),
    );
    $script = "LS.lang = $.extend(LS.lang,".json_encode($notificationLanguageString).");\n";
    Yii::app()->getClientScript()->registerScript('notificationLanguageString',$script,CClientScript::POS_HEAD);
?>
