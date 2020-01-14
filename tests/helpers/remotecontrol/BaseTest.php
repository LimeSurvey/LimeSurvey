<?php

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /* @var User */
    public $user;
    /* @var remotecontrol_handle*/
    public $handler;

    /* @var string see .travis.yml */
    const REMOTECONTROL_USERNAME = 'admin';
    /* @var string see .travis.yml */
    const REMOTECONTROL_PASSWORD = 'password';

    public static function setUpBeforeClass()
    {
        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.replacements_helper', true);
        Yii::import('application.helpers.surveytranslator_helper', true);
        Yii::import('application.helpers.admin.import_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::import('application.helpers.qanda_helper', true);
        Yii::import('application.helpers.update.updatedb_helper', true);
        Yii::import('application.helpers.update.update_helper', true);
        Yii::import('application.helpers.SurveyRuntimeHelper', true);
        Yii::app()->loadHelper('admin/activate');
        Yii::import('application.helpers.remotecontrol.remotecontrol_handle', true);
    }
    
    protected function setUp()
    {
        $user = User::model()->findByPk(1);
        if (!$user) {
            $this->fail('Admin User missing');
        }

        $this->user = $user;
        $this->handler  = new remotecontrol_handle(new AdminController('dummyid'));
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
