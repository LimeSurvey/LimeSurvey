<?php
/**
 * Plugin options panel
 */
?>
<?php if (isset($pluginSettings)): ?>
    <div id='plugin' class="tab-pane fade in">
        <?php
        foreach ($pluginSettings as $id => $plugin)
        {
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'settings' => $plugin['settings'],
                'form' => false,
                'title' => sprintf(gT("Settings for plugin %s"), $plugin['name']),
                'prefix' => "plugin[{$plugin['name']}]"

            ));
        }
        ?>
    </div>
<?php endif; ?>
