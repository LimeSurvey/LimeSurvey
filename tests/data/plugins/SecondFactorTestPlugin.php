<?php

/**
 * Test plugin requiring a second authentication step for every login, like TwoFactorAdminLogin
 * does for users with a 2FA key
 */
class SecondFactorTestPlugin extends PluginBase
{
    protected static $description = 'Test plugin requiring a second authentication step';
    protected static $name = 'SecondFactorTestPlugin';
    protected $storage = 'DbStorage';

    /** @var bool Whether the second step is required, so the plugin does nothing outside of its tests */
    public static $requireSecondFactor = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subscribe('beforeSuccessfulLogin');
    }

    /**
     * Require the second authentication step
     *
     * @return void
     */
    public function beforeSuccessfulLogin()
    {
        if (self::$requireSecondFactor) {
            $this->getEvent()->set('secondFactorPlugin', get_class($this));
        }
    }
}
