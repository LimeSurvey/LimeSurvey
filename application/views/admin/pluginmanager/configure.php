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
                <div id="overview" class="tab-pane show active" role="tabpanel" aria-labelledby="overview-tab">
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

                <div id="settings" class="tab-pane" role="tabpanel" aria-labelledby="settings-tab">
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

