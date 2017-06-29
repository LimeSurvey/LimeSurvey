<?php
/**
 * Plugin options panel
 */
?>
<?php if (!empty($plugin['settings'])): ?>
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-plugin<?php echo $id; ?>">
                <h4 class="panel-title">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="fa fa-chevron-left"></span>
                    </a>
                    <a id="button-plugin<?php echo $id; ?>" class="collapsed" data-parent="#accordion" role="button" data-toggle="collapse" href="#plugin<?php echo $id; ?>" aria-expanded="false" aria-controls="plugin<?php echo $id; ?>">
                        <?php printf(gT("Settings for plugin %s"), $plugin['name']); ?>
                    </a>
                </h4>
            </div>
            <div id="plugin<?php echo $id; ?>" class="panel-collapse collapse" role="tabpanel">
                <div class="panel-body">
                <?php
                $this->widget('ext.SettingsWidget.SettingsWidget', array(
                    'settings' => $plugin['settings'],
                    'form' => false,
                    'title' => null,
                    'prefix' => "plugin[{$plugin['name']}]",
                    'formHtmlOptions' =>array(
                        'aria-labelledby'=>"button-plugin{$id}"
                    )
                ));
                ?>
                </div>
            </div>
        </div>
<?php endif; ?>
