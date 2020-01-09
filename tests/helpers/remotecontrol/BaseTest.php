<?php

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    /* @var Survey */
    public $survey;
    /* @var User */
    public $user;
    /* @var remotecontrol_handle*/
    public $handler;

    const REMOTECONTROL_USERNAME = 'admin';
    const REMOTECONTROL_PASSWORD = 'password';
    const REMOTECONTROL_EMAIL    = 'admin@test.de';

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

        /*
        Yii::app()->db->createCommand()->truncateTable('{{users}}');
        Yii::app()->db->createCommand()->truncateTable('{{permissions}}');

        $user = new User();
        $user->users_name = self::REMOTECONTROL_USERNAME;
        $user->setPassword(self::REMOTECONTROL_PASSWORD);
        $user->full_name = 'REMOTECONROLUSER';
        $user->parent_id = 0;
        $user->lang = 'de';
        $user->email = self::REMOTECONTROL_EMAIL;
        if (!$user->save()) {
            $this->fail('Unable to create user');
        }

        $this->user = $user;
        Permission::model()->setGlobalPermission($this->getUserId(), 'auth_db');
        */

        $user = User::model()->findByPk(1);
        if (!$user) {
            $this->fail('Admin User missing');
        }

        $this->user = $user;
        $this->handler  = new remotecontrol_handle(new AdminController('dummyid'));
    }

    public function getUserId()
    {
        return $this->user->uid;
    }

    public function getUsername()
    {
        return self::REMOTECONTROL_USERNAME;
    }

    public function getPassword()
    {
        return self::REMOTECONTROL_PASSWORD;
    }

    protected function deleteSurvey()
    {
        $this->survey->delete();
    }

    protected function importSurvey($fileName)
    {
        Yii::app()->session['loginID'] = $this->getUserId();
        $fileName = implode(DIRECTORY_SEPARATOR, array(ROOT, 'data', 'surveys', $fileName));
        if (!file_exists($fileName)) {
            $this->fail('Found no survey file ' . $fileName);
        }

        $newSurveyName = null;
        $result = importSurveyFile($fileName, false, null,null);
        if ($result) {
            $this->survey = Survey::model()->findByPk($result['newsid']);
        } else {
            $this->fail('Could not import survey file ' . $fileName);
        }
    }

}
