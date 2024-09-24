<?php
/**
 * @var $this AdminController
 * Plugin options panel
 */

App()->getClientScript()->registerScript(
    "plugin-panel-variables",
    "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT(
        "If you are using surveys with a closed participant group or notifications emails you need to set an administrator email address.",
        'js'
    ) . "'
    var sURLParameters = '';
    var sAddParam = '';
",
    LSYii_ClientScript::POS_BEGIN
);
if (!empty($plugin['settings'])): ?>
    <div class="accordion p-2" id="accordion">
        <div class="accordion-item ">
            <h2 class="accordion-header"  id="heading-plugin<?php echo $id; ?>">
                <button  id="button-plugin<?php echo $id; ?>" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#plugin<?php echo $id; ?>" aria-expanded="true" aria-controls="plugin<?php echo $id; ?>">
                    <?php printf(gT("Settings for plugin %s"), $plugin['name']); ?>
                </button>
            </h2>
            <div id="plugin<?php echo $id; ?>"  class="accordion-collapse collapse" aria-labelledby="heading-plugin<?php echo $id; ?>" data-bs-parent="#accordion">
                <div class="accordion-body">
                <?php
                    $this->widget('ext.SettingsWidget.SettingsWidget', [
                        'settings' => $plugin['settings'],
                        'form' => false,
                        'title' => null,
                        'prefix' => "plugin[{$plugin['name']}]",
                        'formHtmlOptions' => [
                            'aria-labelledby' => "button-plugin{$id}"
                        ]
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
