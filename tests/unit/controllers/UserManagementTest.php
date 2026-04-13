<?php

namespace ls\tests\controllers;

/**
 *  LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */



use ls\tests\TestBaseClass;
use Yii;

class UserManagementTest extends TestBaseClass
{
    public static $newUserId = null;
    private $dataSet;
    
    public function __construct() {
        include(ROOT.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'datasets'.DIRECTORY_SEPARATOR.'userdata.php');
        parent::__construct();
        $this->dataSet = $aDataSet;
    }
    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $config = include(APPPATH . DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
        $dsn = 'mysql:dbname=limesurvey;host=localhost';
        $user = $config['components']['db']['username'];
        $password = $config['components']['db']['password'];
        $pdo = new \PDO($dsn, $user, $password);
        return $this->createDefaultDBConnection($pdo);
    }

    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        $_SESSION = [];
        include(ROOT.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'datasets'.DIRECTORY_SEPARATOR.'userdata.php');
        //\Yii::import('application.controllers.admin.UserManagement', true);
        \Yii::import('application.controllers.UserManagementController', true);
        \Yii::import('application.models.User', true);
        \Yii::app()->session['loginID'] = 1;
        
        $oUser = new \User();
        $oUser->setAttributes($aDataSet['new_user_data']);
        if(!$oUser->save()) {
            throw new \Exception( 
                "Could not save user: "
                .print_r($oUser->getErrors(),true)
            );
        };

        self::$newUserId = $oUser->uid;
    }

    public function setUp(): void
    {
        $oUser = \User::model()->findByPk(self::$newUserId);
        $oUser->setAttributes($this->dataSet['new_user_data']);
        $oUser->save();
    }

    public function testUpdateAdminUserPassword() {
        $oUserManagementController = new \UserManagementController('userManagement');
        $aChangeDataSet = $this->dataSet['user_change_password'];
        $aChangeDataSet['uid'] = self::$newUserId;
        $oUserManagementController->updateAdminUser($aChangeDataSet);

        $oUser = \User::model()->findByPk(self::$newUserId);
        $success = $oUser->checkPassword($this->dataSet['user_change_password']['password']);
        if($success) {
            $this->assertTrue($success);
        } else {
            throw new \Exception( 
                "Test ".__METHOD__ ." failed: \n"
                ."The password has not been changed correctly"
            );
        }
    }

    public function testUpdateAdminUserFullName() {
        $oUserManagementController = new \UserManagementController('userManagement');
        $aChangeDataSet = $this->dataSet['user_change_full_name'];
        $aChangeDataSet['uid'] = self::$newUserId;

        $oUserManagementController->updateAdminUser($aChangeDataSet);

        $oUser = \User::model()->findByPk(self::$newUserId);
        $success = $oUser->full_name == $this->dataSet['user_change_full_name']['full_name'];
        if($success) {
            $this->assertTrue($success);
        } else {
            throw new \Exception( 
                "Test ".__METHOD__ ." failed: \n"
                ."The full name has not been changed correctly"
            );
        }

    }

    public function testUpdateAdminUserTamperproofed() {
        $_SESSION = [];
        $oUserManagementController = new \UserManagementController('userManagement');
        $aChangeDataSet = $this->dataSet['change_admin_user'];
        $aChangeDataSet['uid'] = 1;
        \Yii::app()->session['loginID'] = self::$newUserId;
        try {
            $oUserManagementController->updateAdminUser($aChangeDataSet);
        } catch(\CException $exception) {
            if($exception->statusCode == 403) {
                \Yii::app()->session['loginID'] = 1;
                $this->assertTrue(true);
                return;
            }
            /* throw the exception : user was not updated, but bad exception happen */
            throw $exception;
        }
        \Yii::app()->session['loginID'] = 1;
        throw new \Exception( 
            "Test ".__METHOD__ ." failed: \n"
            ."The admin user has been changed"
        );

    }

    public static function tearDownAfterClass(): void
    {
        $oUser = \User::model()->findByPk(self::$newUserId);
        $oUser->delete();
    }


}
