<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestBaseClass extends TestCase
{
    /**
     * @var TestHelper
     */
    protected static $testHelper = null;

    /** @var  string $tempFolder*/
    protected static $tempFolder;

    /** @var  string $screenshotsFolder */
    protected static $screenshotsFolder;

    /** @var  string $surveysFolder */
    protected static $surveysFolder;

    /** @var  string $dataFolder */
    protected static $dataFolder;

    /** @var  string $viewsFolder */
    protected static $viewsFolder;

    /** @var  \Survey */
    protected static $testSurvey;

    /** @var  integer */
    protected static $surveyId;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        // Clear database cache.
        \Yii::app()->db->schema->refresh();

        //$lt = ini_get('session.gc_maxlifetime');
        //var_dump('gc_maxlifetime = ' . $lt);
        //die;

        // This did not fix the langchang test failure on Travis.
        //session_destroy();
        //session_start();

        self::$testHelper = new TestHelper();

        self::$dataFolder = __DIR__.'/data';
        self::$viewsFolder = self::$dataFolder."/views";
        self::$surveysFolder = self::$dataFolder.'/surveys';
        self::$tempFolder = __DIR__.'/tmp';
        self::$screenshotsFolder = self::$tempFolder.'/screenshots';
        self::$testHelper->importAll();
    }

    /**
     * @param string $fileName
     * @return void
     */
    protected static function importSurvey($fileName)
    {
        \Yii::app()->session['loginID'] = 1;
        $surveyFile = $fileName;
        if (!file_exists($surveyFile)) {
            self::assertTrue(false, 'Found no survey file ' . $fileName);
        }

        $translateLinksFields = false;
        $newSurveyName = null;
        $result = \importSurveyFile(
            $surveyFile,
            $translateLinksFields,
            $newSurveyName,
            null
        );
        if ($result) {
            \Survey::model()->resetCache(); // Reset the cache so findByPk doesn't return a previously cached survey
            self::$testSurvey = \Survey::model()->findByPk($result['newsid']);
            self::$surveyId = $result['newsid'];
        } else {
            self::assertTrue(false, 'Could not import survey file ' . $fileName);
        }
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        // Make sure we have permission to delete survey.
        \Yii::app()->session['loginID'] = 1;

        if (self::$testSurvey) {
            if (!self::$testSurvey->delete()) {
                self::assertTrue(
                    false,
                    'Fatal error: Could not clean up survey '
                    . self::$testSurvey->sid
                    . '; errors: '
                    . json_encode(self::$testSurvey->errors)
                );
            }
            self::$testSurvey = null;
        }
    }

    protected static function createUserWithPermissions(array $userData, array $permissions = [])
    {
        if ($userData['password'] != ' ') {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        $oUser = new \User();
        $oUser->setAttributes($userData);

        if (!$oUser->save()) {
            throw new \Exception(
                "Could not save user: "
                . print_r($oUser->getErrors(), true)
            );
        };

        $newUserId = $oUser->uid;
        self::addUserPermissions($newUserId, $permissions);

        return $oUser;
    }

    /**
     * Adds permission to a users
     * Needs an array in the form of [PERMISSIONID][PERMISSION]
     *
     * @param int $userId
     * @param array $permissions
     * @return array
     */
    protected static function addUserPermissions(int $userId, array $permissions)
    {
        //Delete all current Permissions
        $criteria = new \CDbCriteria();
        $criteria->compare('uid', $userId);
        // without entity
        $criteria->compare('entity_id', 0);
        // except for template entity (no entity_id is set here)
        $criteria->compare('entity', "<>template");
        \Permission::model()->deleteAll($criteria);

        $results = [];
        //Apply the permission array
        foreach ($permissions as $permissionKey => $permissionSettings) {
            $permission = new \Permission();
            $permission->entity = 'global';
            $permission->entity_id = 0;
            $permission->uid = $userId;
            $permission->permission = $permissionKey;

            foreach ($permissionSettings as $settingKey => $settingValue) {
                $permissionDBSettingKey = $settingKey . '_p';
                $permission->$permissionDBSettingKey = $settingValue == 'on' ? 1 : 0;
            }

            $results[$permissionKey] = [
                'success' => $permission->save(),
                'storedValue' => $permission->attributes
            ];
        }
        return $results;
    }
}
