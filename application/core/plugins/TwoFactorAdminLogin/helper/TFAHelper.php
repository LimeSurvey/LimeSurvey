<?php
/**
 * Helper functionalities for 2fa plugin
 */
class TFAHelper {
  

    /**
     * Get the status of the plugin from db
     *
     * @return boolean
     */
    public static function isPluginActive()
    {
        $plugin = Plugin::model()->findByAttributes(["name" => "TwoFactorAdminLogin"]);
        
        if ($plugin) {
           return (int)$plugin->active;
        } else {
            return 0;
        }
    }
}