<div id="quick-menu-container">
    <!-- TODO: Placement right won't work with right-to-left -->
    <a
        href="<?php echo $this->createUrl("surveyAdministration/view/surveyid/$surveyid"); ?>"
        data-bs-toggle="tooltip"
        data-title="<?php eT("Survey"); ?>"
        data-bs-placement="right"
    >
        <div class='quick-icon-wrapper'>
            <span class="ri-home-4-fill navbar-brand"></span>
        </div>
    </a>

    <?php foreach ($quickMenuItems as $quickMenuItem): ?>
        <div
            class='quick-menu-item'
            data-bs-toggle="tooltip"
            data-title="<?php echo $quickMenuItem['tooltip']; ?>"
            data-button-name="<?php echo $quickMenuItem['name']; ?>"
            data-bs-placement="right"
            draggable="true"
            ondragstart="dragstart_handler(event);"
            ondragover="dragover_handler(event);"
            ondragleave="dragleave_handler(event);"
            ondrop="drop_handler(event);"
        >
            <a
                href='<?php echo $quickMenuItem['href']; ?>'
                <?php if ($quickMenuItem['openInNewTab']): ?>
                    target='_blank'
                <?php endif; ?>
            >
                <div class='quick-icon-wrapper' draggable="false">
                    <?php
                        /* pointer-events none is necessary to prevent HTML draggable events from affecting
                         * child elements. More info here: http://www.quirksmode.org/blog/archives/2009/09/the_html5_drag.html
                         */ ?>
                    <span class="<?php echo $quickMenuItem['iconClass']; ?>" style="pointer-events: none;" draggable="false"></span>
                </div>
            </a>
        </div>
<?php endforeach; ?>
</div>
<script>
    // TODO: Needs to be moved to QuickMenu core plugin
    var saveQuickMenuButtonOrderLink = '<?php
        // Save order after drag-n-drop sorting
        echo Yii::app()->createUrl(
            'plugins/direct',
            array(
                'plugin' => 'QuickMenu',
                'function' => 'saveOrder'
            )
        );
        ?>';

</script>
