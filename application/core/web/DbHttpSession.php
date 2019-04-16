<?php
/**
 * @inheritdoc
 * Leave it for compatibility of old config.php file
 */
class DbHttpSession extends \CDbHttpSession
{
    /**
     * @inheritdoc
     * Usage of config
     */
    public function getTimeout()
    {
        return (int)Yii::app()->getConfig('iSessionExpirationTime',ini_get('session.gc_maxlifetime'));
    }
}
