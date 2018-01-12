<?php
/**
 * @var $this AdminController
 * Plugin options panel
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPlugins');

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
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-plugin<?php echo $id; ?>">
                <div class="panel-title h4">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="fa fa-chevron-left"></span>
			<span class="sr-only"><?php eT("Expand/Collapse");?></span>
                    </a>
                    <a id="button-plugin<?php echo $id; ?>" class="collapsed" data-parent="#accordion" role="button" data-toggle="collapse" href="#plugin<?php echo $id; ?>" aria-expanded="false" aria-controls="plugin<?php echo $id; ?>">
                        <?php printf(gT("Settings for plugin %s"), $plugin['name']); ?>
                    </a>
                </div>
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
