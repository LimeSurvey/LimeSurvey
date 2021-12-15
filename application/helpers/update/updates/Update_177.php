<?php

namespace LimeSurvey\Helpers\Update;

use Plugin;
use App;

class Update_177 extends DatabaseUpdateBase
{
    public function up()
    {
        if (\Yii::app()->getConfig('auth_webserver') === true) {
            // using auth webserver, now activate the plugin with default settings.
            if (!class_exists('Authwebserver', false)) {
                $plugin = Plugin::model()->findByAttributes(array('name' => 'Authwebserver'));
                if (!$plugin) {
                    $plugin = new Plugin();
                    $plugin->name = 'Authwebserver';
                    $plugin->active = 1;
                    $plugin->save();
                    $plugin = App()->getPluginManager()->loadPlugin('Authwebserver', $plugin->id);
                    $aPluginSettings = $plugin->getPluginSettings(true);
                    $aDefaultSettings = array();
                    foreach ($aPluginSettings as $key => $settings) {
                        if (is_array($settings) && array_key_exists('current', $settings)) {
                            $aDefaultSettings[$key] = $settings['current'];
                        }
                    }
                    $plugin->saveSettings($aDefaultSettings);
                } else {
                    $plugin->active = 1;
                    $plugin->save();
                }
            }
        }
        upgradeSurveys177();
    }
}
