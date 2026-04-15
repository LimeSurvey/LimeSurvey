<?php
/**
 * Installer class for the TwoFactorAdminLogin Plugin
 * A collecton of static helpers to install the Plugin
 */
class TFAPluginInstaller {
    public static $instance = null;
    private $errors = [];

    /**
     * Singleton get Instance
     *
     * @return TFAPluginInstaller
     */
    public static function instance()
    {
        if(self::$instance == null) {
            self::$instance = new TFAPluginInstaller();
        }
        return self::$instance;
    }

    /**
     * Combined installation for all necessary options
     * 
     * @throws CHttpException
     * @return void
     */
    public function install()
    {
        try {
            $this->installTables();
        } catch(CHttpException $e) {
            $this->errors[] = $e;
        }

        if(count($this->errors) > 0) {
            throw new CHttpException(500, join(",\n", array_map(function($oError){ return $oError->getMessage();},$this->errors)));
        }
    }

    /**
     * Combined uninstallation for all necessary options
     * 
     * @throws CHttpException
     * @return void
     */
    public function uninstall()
    {
         try {
             $this->uninstallTables();
        } catch(CHttpException $e) {
            $this->errors[] = $e;
        }

        if(count($this->errors) > 0) {
            throw new CHttpException(500, join(",\n", array_map(function($oError){ return $oError->getMessage();},$this->errors)));
        }
    }

    /**
     * Install tables for the plugin
     * 
     * @throws CHttpException
     * @return boolean
     */
    public function installTables()
    {
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try{
            $oDB->createCommand()->createTable('{{user_mfa_settings}}', array(
                'uid' => 'integer NOT NULL',
                'secretKey' => 'string NOT NULL',
                'authType' => 'string NOT NULL',
                'firstLogin' => 'integer DEFAULT 1',
                'forceNewFirstLogin' => 'integer DEFAULT 0',
            ));

            $oTransaction->commit();
            return true;
        } catch(Exception $e) {
            $oTransaction->rollback();
            throw new CHttpException(500, $e->getMessage());
        }
    }

    /**
     * Uninstall tables for the plugin
     * 
     * @throws CHttpException
     * @return boolean
     */
    public function uninstallTables()
    {
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try{
            $oDB->createCommand()->dropTable('{{user_mfa_settings}}');
            $oTransaction->commit();
            return true;
        } catch(Exception $e) {
            $oTransaction->rollback();
            throw new CHttpException(500, $e->getMessage());
        }
    }
}