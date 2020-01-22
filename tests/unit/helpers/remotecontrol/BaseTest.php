<?php

namespace ls\tests;

abstract class BaseTest extends TestHelper
{
    /* @var User */
    public $user;
    /* @var remotecontrol_handle*/
    public $handler;

    /* @var string see .travis.yml */
    const REMOTECONTROL_USERNAME = 'admin';
    /* @var string see .travis.yml */
    const REMOTECONTROL_PASSWORD = 'password';

    protected function setUp()
    {
        $this->importAll();
        \Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);

        $user = \User::model()->findByPk(1);
        if (!$user) {
            $this->fail('Admin User missing');
        }

        $this->user = $user;
        $this->handler  = new \remotecontrol_handle(new \AdminController('dummyid'));
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user->uid;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return self::REMOTECONTROL_USERNAME;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return self::REMOTECONTROL_PASSWORD;
    }
}
