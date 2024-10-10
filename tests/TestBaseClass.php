<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;
use Exception;
use Survey;

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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Enable Debug and Error Reporting if logging is enabled
        $isDebug = getenv('RUNNER_DEBUG', false);
        // fwrite(STDERR, 'Error Reporting and Debug: ' . ($isDebug ? 'Yes' : 'No'));
        if ($isDebug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        }

        // Clear database cache.
        \Yii::app()->db->schema->refresh();

        //$lt = ini_get('session.gc_maxlifetime');
        //var_dump('gc_maxlifetime = ' . $lt);
        //die;

        // This did not fix the langchang test failure on Travis.
        //session_destroy();
        //session_start();

        self::$testHelper = new TestHelper();

        self::$dataFolder = __DIR__ . '/data';
        self::$viewsFolder = self::$dataFolder . "/views";
        self::$surveysFolder = self::$dataFolder . '/surveys';
        self::$tempFolder = __DIR__ . '/tmp';
        self::$screenshotsFolder = self::$tempFolder . '/screenshots';
        self::$testHelper->importAll();

        \Yii::import('application.helpers.globalsettings_helper', true);
    }

    /**
     * @param string $fileName
     * @param integer $asuser
     * @return void
     */
    protected static function importSurvey($fileName, $asuser = 1)
    {
        \Yii::app()->session['loginID'] = $asuser;
        $surveyFile = $fileName;
        if (!file_exists($surveyFile)) {
            throw new Exception(sprintf('Survey file %s not found', $surveyFile));
        }

        // Reset the cache to prevent import from failing if there is a cached survey and it's active.
        // When importing, activating, deleting and importing again (usual with automated tests),
        // as using the same SID, it was picking up the cached (old) version of the survey
        Survey::model()->resetCache();
        $translateLinksFields = false;
        $newSurveyName = null;
        $result = \importSurveyFile(
            $surveyFile,
            $translateLinksFields,
            $newSurveyName,
            null
        );
        if ($result) {
            if (!empty($result['error'])) {
                throw new Exception(sprintf('Could not import survey %s: %s', $fileName, $result['error']));
            }
            // Reset the cache so findByPk doesn't return a previously cached survey
            Survey::model()->resetCache();
            self::$testSurvey = \Survey::model()->findByPk($result['newsid']);
            self::$surveyId = $result['newsid'];
        } else {
            throw new Exception(sprintf('Failed to import survey file %s', $surveyFile));
        }
    }

    /**
     * Get all question inside current survey, key is question code
     * @return array[]
     */
    public function getAllSurveyQuestions()
    {
        if (empty(self::$surveyId)) {
            throw new Exception('getAllSurveyQuestions call without survey.');
        }
        $survey = \Survey::model()->findByPk(self::$surveyId);
        if (empty($survey)) {
            throw new Exception('getAllSurveyQuestions call with an invalid survey.');
        }
        $questions = [];
        foreach ($survey->groups as $group) {
            $questionObjects = $group->questions;
            foreach ($questionObjects as $q) {
                $questions[$q->title] = $q;
            }
        }
        return $questions;
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Make sure we have permission to delete survey.
        \Yii::app()->session['loginID'] = 1;

        if (self::$testSurvey) {
            // Clear database cache.
            \Yii::app()->db->schema->refresh();
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

    /**
     * Helper install and activate plugins by name
     * @param string $pluginName
     * @return void
     */
    public static function installAndActivatePlugin($pluginName)
    {
        $plugin = \Plugin::model()->findByAttributes(array('name' => $pluginName));
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = $pluginName;
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }

        return $plugin;
    }

    /**
     * Helper dactivate plugins by name
     * @param string $pluginName
     * @return void
     */
    public static function deActivatePlugin($pluginName)
    {
        $plugin = \Plugin::model()->findByAttributes(array('name' => $pluginName));
        if ($plugin) {
            $plugin->active = 0;
            $plugin->save();
        }
    }

    /**
     * Helper dispatch evento to specific plugin
     * @param string $pluginName
     * @param \PluginEvent $eventName
     * @param array $eventValues
     * @return void
     */
    public static function dispatchPluginEvent($pluginName, $eventName, $eventValues)
    {
        $oEvent = (new \PluginEvent($eventName));
        foreach ($eventValues as $key => $value) {
            $oEvent->set($key, $value);
        }
        \Yii::app()->getPluginManager()->dispatchEvent($oEvent, $pluginName);

        return $oEvent;
    }

    protected static function createUserWithPermissions(array $userData, array $permissions = [])
    {
        if (!empty($userData['users_name'])) {
            \User::model()->deleteAllByAttributes([
                'users_name' => $userData['users_name']
            ]);
        }
        if ($userData['password'] != ' ') {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        $oUser = new \User();
        $oUser->setAttributes($userData);

        if (!$oUser->save()) {
            throw new Exception(
                "Could not save user: "
                . print_r($oUser->getErrors(), true)
            );
        };

        $newUserId = $oUser->uid;
        \Permissiontemplates::model()->clearUser($newUserId);
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

            $aPermissionData = \Permission::getGlobalPermissionData($permissionKey);

            $results[$permissionKey] = [
                'descriptionData' => $aPermissionData,
                'success' => $permission->save(),
                'storedValue' => $permission->attributes
            ];
        }
        return $results;
    }

    /**
     * @param string $pluginName
     * @return iPlugin
     */
    protected static function loadTestPlugin($pluginName)
    {
        require_once self::$dataFolder . "/plugins/{$pluginName}.php";
        $plugin = \Plugin::model()->findByAttributes(['name' => $pluginName]);
        if (!$plugin) {
            $plugin = new \Plugin();
            $plugin->name = $pluginName;
            $plugin->active = 1;
            $plugin->save();
        } else {
            $plugin->active = 1;
            $plugin->save();
        }

        $plugin = App()->getPluginManager()->loadPlugin($pluginName, $plugin->id);
        if (is_null($plugin)) {
            throw new Exception(sprintf('Failed to load test plugin %s', $pluginName));
        }
        return $plugin;
    }
}
