<?php
/* @var $this AdminController */

/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('configurePlugin');
?>

<div class="plugin--configure">
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="settingTabs" role="tablist" aria-label="<?php eT('Plugin configuration tabs'); ?>">
                <li role="presentation" class="nav-item">
                    <a id="overview-tab" class="nav-link active" role="tab" data-bs-toggle="tab" href='#overview' aria-selected="true" aria-controls="overview" tabindex="0"><?php eT("Overview"); ?></a>
                </li>
                <li role="presentation" class="nav-item">
                    <a id="settings-tab" class="nav-link" role="tab" data-bs-toggle="tab" href='#settings' aria-selected="false" aria-controls="settings" tabindex="-1"><?php eT("Settings"); ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="overview" class="tab-pane show active" role="tabpanel" aria-labelledby="overview-tab" aria-hidden="false">
                    <?php $this->renderPartial(
                        './pluginmanager/overview',
                        [
                            'plugin' => $plugin,
                            'pluginObject' => $pluginObject,
                            'config' => $pluginObject->config,
                            'metadata' => $pluginObject->config->metadata,
                            'showactive' => true
                        ]
                    ); ?>
                </div>

                <div id="settings" class="tab-pane" role="tabpanel" aria-labelledby="settings-tab" aria-hidden="true">
                    <?php if ($settings) :
                        $this->widget(
                            'ext.SettingsWidget.SettingsWidget',
                            [
                                'settings' => $settings,
                                'formHtmlOptions' => [
                                    'id' => "pluginsettings-{$plugin['name']}",
                                ],
                                'labelWidth' => 4,
                                'controlWidth' => 6,
                                'method' => 'post',
                                'buttons' => $buttons,
                            ]
                        );
                        ?>
                    <?php else : ?>
                        <i><?php eT('This plugin has no settings.'); ?></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var $tabs = $('#settingTabs [role="tab"]');
    var $tabpanels = $('.tab-pane[role="tabpanel"]');

    // Function to find first focusable element in a tab panel
    function getFirstFocusableElement($panel) {
        var focusableSelectors = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';
        return $panel.find(focusableSelectors).first();
    }

    // Handle keyboard navigation
    $tabs.on('keydown', function(e) {
        var $current = $(this);
        var $next, $prev;

        switch(e.keyCode) {
            case 37: // Left arrow
            case 38: // Up arrow
                e.preventDefault();
                $prev = $current.parent().prev().find('[role="tab"]');
                if ($prev.length === 0) {
                    $prev = $tabs.last();
                }
                $prev.focus().click();
                break;
            case 39: // Right arrow
            case 40: // Down arrow
                e.preventDefault();
                $next = $current.parent().next().find('[role="tab"]');
                if ($next.length === 0) {
                    $next = $tabs.first();
                }
                $next.focus().click();
                break;
            case 36: // Home
                e.preventDefault();
                $tabs.first().focus().click();
                break;
            case 35: // End
                e.preventDefault();
                $tabs.last().focus().click();
                break;
        }
    });

    // Update ARIA attributes when tabs change and move focus to first interactive element
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        var $targetPanel = $(target);

        // Update aria-selected for all tabs
        $tabs.attr('aria-selected', 'false').attr('tabindex', '-1');
        $(e.target).attr('aria-selected', 'true').attr('tabindex', '0');

        // Hide all tabpanels
        $tabpanels.attr('aria-hidden', 'true').removeClass('show active');

        // Show the selected tabpanel
        $targetPanel.attr('aria-hidden', 'false').addClass('show active');

        // Move focus to first focusable element in the tab panel
        var $firstFocusable = getFirstFocusableElement($targetPanel);
        if ($firstFocusable.length > 0) {
            setTimeout(function() {
                $firstFocusable.focus();
            }, 100); // Small delay to ensure the panel is fully shown
        }
    });
});
</script>
