<?php
/**
 * @var $this AdminController
 * Plugin options panel
 */


App()->getClientScript()->registerScript("plugin-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN);

    if (!empty($plugin['settings'])): ?>
        <div class="card card-primary">
            <div class="card-header bg-primary" role="tab" id="heading-plugin<?php echo $id; ?>">
                <a class="btn btn-outline-secondary btn-xs">
                    <span class="fa fa-chevron-left"></span>
                    <span class="sr-only"><?php eT("Expand/Collapse");?></span>
                </a>
                <a id="button-plugin<?php echo $id; ?>" class="collapsed bg-primary" data-bs-parent="#accordion" role="button" data-bs-toggle="collapse" href="#plugin<?php echo $id; ?>" aria-expanded="false" aria-controls="plugin<?php echo $id; ?>">
                    <?php printf(gT("Settings for plugin %s"), $plugin['name']); ?>
                </a>
            </div>
            <div id="plugin<?php echo $id; ?>" class="panel-collapse collapse" role="tabpanel">
                <div class="card-body">
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
