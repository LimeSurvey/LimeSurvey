<div id="quick-menu-container">
    <!-- TODO: Placement right won't work with right-to-left -->
    <a
        href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>"
        data-toggle="tooltip"
        data-title="<?php eT("Survey"); ?>"
        data-placement="right"
    >
        <div class='quick-icon-wrapper'>
            <span class="glyphicon glyphicon-home navbar-brand"></span>
        </div>
    </a>

    <?php foreach ($quickMenuItems as $quickMenuItem): ?>
        <a
            <?php if ($quickMenuItem['openInNewTab']): ?>
                target='_blank'
            <?php endif; ?>

            href='<?php echo $quickMenuItem['href']; ?>'
            data-toggle="tooltip"
            data-title="<?php echo $quickMenuItem['tooltip']; ?>"
            data-placement="right"
        >
            <div class='quick-icon-wrapper'>
                <span class="<?php echo $quickMenuItem['iconClass']; ?>"></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

