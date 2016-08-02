<!-- Admin notification system -->
<?php if ($zeroNotifications): ?>
    <li id='notification-li' class='dropdown'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <span class='fa fa-bell text-muted'></span>
        </a>
    </li>
<?php else: ?>
    <li id='notification-li' class='dropdown'>
        <a class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' href='#'>
            <span id='notification-bell' class='fa fa-bell'></span>
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
                        data-update-url='<?php echo $not->getUpdateUrl($surveyId); ?>'
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
                <a href='#'><?php eT('See all notifications'); ?></a>
            </li>
        </ul>

    </li>
<?php endif; ?>
