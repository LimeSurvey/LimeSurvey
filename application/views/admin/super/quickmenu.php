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
        <div
            <?php if ($quickMenuItem['openInNewTab']): ?>
                target='_blank'
            <?php endif; ?>

            href='<?php echo $quickMenuItem['href']; ?>'
            data-toggle="tooltip"
            data-title="<?php echo $quickMenuItem['tooltip']; ?>"
            data-placement="right"
            draggable="true"
            ondragstart="dragstart_handler(event);"
            ondragover="dragover_handler(event);"
            ondragleave="dragleave_handler(event);"
            ondrop="drop_handler(event);"
        >
            <div class='quick-icon-wrapper' draggable="false">
                <?php
                    /* pointer-events none is necessary to prevent HTML draggable events from affecting
                     * child elements. More info here: http://www.quirksmode.org/blog/archives/2009/09/the_html5_drag.html
                     */ ?>
                <span class="<?php echo $quickMenuItem['iconClass']; ?>" style="pointer-events: none;" draggable="false"></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

