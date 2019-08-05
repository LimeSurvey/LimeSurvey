<?php

namespace LimeSurvey\PluginManager;

use Yii;
use User;
use PluginDynamic;
use SurveyDynamic;
use Template;

/**
 * Class exposing a Limesurvey API to plugins.
 * This class is instantiated by the plugin manager,
 * plugins can obtain it by calling getAPI() on the plugin manager.
 */
class LimesurveyApi
{
    /**
     * Read a key from the application config, and when not set
     * return the default value
     *
     * @param string $key          The key to search for in the application config
     * @param mixed  $defaultValue Value to return when not found, default is false
     * @return string
     */
    public function getConfigKey($key, $defaultValue = false)
    {
        return App()->getConfig($key, $defaultValue);
    }

    /**
     * Generates the real table name from plugin and tablename.
     * @param iPlugin $plugin
     * @param string $tableName
     */
    protected function getTableName(iPlugin $plugin, $tableName)
    {
        return App()->getDb()->tablePrefix.strtolower($plugin->getName())."_$tableName";
    }

    /**
     * Sets a flash message to be shown to the user.
     * @param string $message
     * @param string $key
     * @return void
     */
    public function setFlash($message, $key = 'api')
    {
        // @todo Remove direct session usage.
        \Yii::app()->user->setFlash($key, $message);
    }

    /**
     * Builds and executes a SQL statement for creating a new DB table.
     * @param PluginBase $plugin The plugin object, id or name.
     * @param string $sTableName the name of the table to be created. The name will be properly quoted and prefixed by the method.
     * @param array $aColumns the columns (name=>definition) in the new table.
     * @param string $sOptions additional SQL fragment that will be appended to the generated SQL.
     * @return integer number of rows affected by the execution.
     */
    public function createTable($plugin, $sTableName, $aColumns, $sOptions = null)
    {
        if (null !== $sTableName = $this->getTableName($plugin, $sTableName)) {
            return App()->getDb()->createCommand()->createTable($sTableName, $aColumns, $sOptions);
        }
        return false;
    }

    /**
     * Builds and executes a SQL statement for dropping a DB table.
     * @param mixed $plugin The plugin object, id or name.
     * @param string $sTableName the name of the table to be created. The name will be properly quoted and prefixed by the method.
     */
    public function dropTable($plugin, $sTableName)
    {
        if (null !== $sTableName = $this->getTableName($plugin, $sTableName)) {
            return App()->getDb()->createCommand()->dropTable($sTableName);
        }
        return false;
    }

    public function createUrl($route, array $params)
    {
        return App()->createAbsoluteUrl($route, $params);
    }

    /**
     * Gets an activerecord object associated to the table.
     * @param iPlugin $plugin
     * @param string $sTableName Name of the table.
     * @param string $bPluginTable True if the table is plugin specific.
     * @return \Plugin|null
     */
    public function getTable(iPlugin $plugin, $sTableName, $bPluginTable = true)
    {
        if ($bPluginTable) {
            $table = $this->getTableName($plugin, $sTableName);
        } else {
            $table = $sTableName;
        }
        if (isset($table)) {
            return \PluginDynamic::model($table);
        }
    }

    /**
     * @see http://www.yiiframework.com/doc/api/1.1/CWebUser#checkAccess-detail
     * @param string $operation
     * @param array $params
     * @param boolean $allowCaching
     * @return boolean
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        return App()->user->checkAccess($operation, $params, $allowCaching);
    }
    /**
     * Creates a new active record object instance.
     * @param iPlugin $plugin
     * @param string $sTableName
     * @param string $scenario
     * @param string $bPluginTable True if the table is plugin specific.
     * @return PluginDynamic
     */
    public function newModel(iPlugin $plugin, $sTableName, $scenario = 'insert', $bPluginTable = true)
    {
        if ($bPluginTable) {
            $table = $this->getTableName($plugin, $sTableName);
        } else {
            $table = $sTableName;
        }
        if (isset($table)) {
            return new \PluginDynamic($table, $scenario);
        }
    }

    public function removeResponse($surveyId, $responseId)
    {
        return \Response::model($surveyId)->deleteByPk($responseId);
    }
    /**
     * Check if a table does exist in the database
     * @param iPlugin $plugin
     * @param string $sTableName Table name to check for (without dbprefix!))
     * @return boolean True or false if table exists or not
     */
    public function tableExists(iPlugin $plugin, $sTableName)
    {
        $sTableName = $this->getTableName($plugin, $sTableName);
        return isset($sTableName) && in_array($sTableName, App()->getDb()->getSchema()->getTableNames());
    }

    /**
     * Evaluates an expression via Expression Manager
     * Uses the current context.
     * @param string $expression
     * @return string
     */
    public function EMevaluateExpression($expression)
    {
        $result = \LimeExpressionManager::ProcessString($expression);
        return $result;
    }

    /**
     * Get the current request object
     *
     * @return \LSHttpRequest
     */
    public function getRequest()
    {
        return App()->getRequest();
    }

    /**
     * Returns an array of all available template names - does a basic check if the template might be valid
     * @return array
     */
    public function getTemplateList()
    {
        return Template::getTemplateList();
    }

    /**
     * Gets a survey response from the database.
     *
     * @param int $surveyId
     * @param int $responseId
     * @param bool $bMapQuestionCodes
     * @return array
     */
    public function getResponse($surveyId, $responseId, $bMapQuestionCodes = true)
    {
        $survey = \Survey::model()->findByPk($surveyId);
        $response = \SurveyDynamic::model($surveyId)->findByPk($responseId);
        if (!$bMapQuestionCodes) {
            return $response;
        }

        if (isset($response)) {
            // Now map the response to the question codes if possible, duplicate question codes will result in the
            // old sidXgidXqid code for the second time the code is found
            $fieldmap = createFieldMap($survey, 'full', null, false, $response->attributes['startlanguage']);
            $output = array();
            foreach ($response->attributes as $key => $value) {
                $newKey = $key;
                if (array_key_exists($key, $fieldmap)) {
                    if (array_key_exists('title', $fieldmap[$key])) {
                        $code = $fieldmap[$key]['title'];
                        // Add subquestion code if needed
                        if (array_key_exists('aid', $fieldmap[$key]) && isset($fieldmap[$key]['aid']) && $fieldmap[$key]['aid'] != '') {
                            $code .= '_'.$fieldmap[$key]['aid'];
                        }
                        // Only add if the code does not exist yet and is not empty
                        if (!empty($code) && !array_key_exists($code, $output)) {
                            $newKey = $code;
                        }
                    }
                }
                $output[$newKey] = $value;
            }

            // And return the mapped response, to further enhance we could add a method to the api that provides a
            // simple sort of fieldmap that returns qcode index array with group, question, subquestion,
            // possible answers, maybe even combined with relevance info so a plugin can handle display of the response
            return $output;
        }
    }

    /**
     * Get the current Response
     * @param integer $surveyId
     * @return \Response|null
     */
    public function getCurrentResponses($surveyId = null)
    {
        if(empty($surveyId)) {
            $surveyId = \LimeExpressionManager::getLEMsurveyId();
        }
        if(empty($surveyId)) {
            return;
        }
        $sessionSurvey = Yii::app()->session["survey_{$surveyId}"];
        if(empty($sessionSurvey['srid'])) {
            return;
        }
        return \Response::model($surveyId)->findByPk($sessionSurvey['srid']);
    }

    /**
     * @return \Response[]|null
     */
    public function getResponses($surveyId, $attributes = array(), $condition = '', $params = array())
    {
        return \Response::model($surveyId)->findAllByAttributes($attributes, $condition, $params);
    }

    /**
     * @return \Token|null
     */
    public function getToken($surveyId, $token)
    {
        return \Token::model($surveyId)->findByAttributes(array('token' => $token));
    }

    /**
     * Return a token object from a token id and a survey id
     *
     * @param integer $iSurveyId
     * @param integer $iTokenId
     * @return \Token Token
     */
    public function getTokenById($iSurveyId, $iTokenId)
    {
        return \Token::model($iSurveyId)->findByAttributes(array('tid' => $iTokenId));
    }

    /**
     * Gets a key value list using the group name as value and the group id
     * as key.
     * @param boolean $surveyId
     * @return type
     */
    public function getGroupList($surveyId)
    {
        $result = \QuestionGroup::model()->findListByAttributes(array('sid' => $surveyId), 'group_name');
        return $result;
    }

    /**
     * Retrieves user details for the currently logged in user
     * Returns false if the user is not logged and returns null if the user does not exist anymore for some reason (should not really happen)
     * @return User
     */
    public function getCurrentUser()
    {
        if (\Yii::app()->user->id) {
            return \User::model()->findByPk(\Yii::app()->user->id);
        }
        return false;
    }

    /**
     * Gets the table name for responses for the specified survey id.
     * @param int $surveyId
     * @return string
     */
    public function getResponseTable($surveyId)
    {
        return App()->getDb()->tablePrefix.'survey_'.$surveyId;
    }

    /**
     * Gets an array of old response table names for a survey.
     * @param int $surveyId
     * @return string[]
     */
    public function getOldResponseTables($surveyId)
    {
        $tables = array();
        $base = App()->getDb()->tablePrefix.'old_survey_'.$surveyId;
        $timingbase = App()->getDb()->tablePrefix.'old_survey_'.$surveyId.'_timings_';
        foreach (App()->getDb()->getSchema()->getTableNames() as $table) {
            if (strpos($table, $base) === 0 && strpos($table, $timingbase) === false) {
                $tables[] = $table;
            }
        }
        return $tables;
    }

    /**
     * Retrieves user details for a user
     * Returns null if the user does not exist anymore for some reason (should not really happen)
     *
     * @param int $iUserID The userid
     * @return User
     */
    public function getUser($iUserID)
    {
        return \User::model()->findByPk($iUserID);
    }

    /**
     * Get the user object for a given username
     *
     * @param string $username
     * @return User|null Returns the user, or null when not found
     */
    public function getUserByName($username)
    {
        $user = \User::model()->findByAttributes(array('users_name' => $username));

        return $user;
    }

    /**
     * Get the user object for a given email
     *
     * @param string|null $email
     * @return User|null Returns the user, or null when not found
     */
    public function getUserByEmail($email)
    {
        $user = \User::model()->findByAttributes(array('email' => $email));

        return $user;
    }



    /**
     * Retrieves user permission details for a user
     * @param $iUserID int The User ID
     * @param  $iSurveyID int The related survey IF for survey permissions - if 0 then global permissions will be retrieved
     * Returns null if the user does not exist anymore for some reason (should not really happen)
     * @return User
     */
    public function getPermissionSet($iUserID, $iEntityID = null, $sEntityName = null)
    {
        return \Permission::model()->getPermissions($iUserID, $iEntityID, $sEntityName);
    }

    /**
     * Retrieves Participant data
     * @param $iParticipantID int The Participant ID
     * Returns null if the user does not exist anymore for some reason (should not really happen)
     * @return \Participant
     */
    public function getParticipant($iParticipantID)
    {
        return \Participant::model()->findByPk($iParticipantID);
    }

    /**
     * @param int $surveyId
     * @param string $language
     * $param array $conditions
     * @return \Question[]
     */
    public function getQuestions($surveyId, $language = 'en', $conditions = array())
    {
        $conditions['sid'] = $surveyId;
        $conditions['language'] = $language;
        return \Question::model()->with('subquestions')->findAllByAttributes($conditions);
    }

    /**
     * Gets the metadata for a table.
     * For details on the object check: http://www.yiiframework.com/doc/api/1.1/CDbTableSchema
     * @param string $table Table name.
     * @param boolean $forceRefresh False if cached information is acceptable; setting this to true could affect performance.
     * @return \CDbTableSchema Table schema object, NULL if the table does not exist.
     */
    public function getTableSchema($table, $forceRefresh = false)
    {
        return App()->getDb()->getSchema()->getTable($table);
    }

    /**
     * Returns true if a plugin exists with name $name (active or not)
     *
     * @param string $name Name of plugin
     * @return boolean
     * @throws InvalidArgumentException if $name is not a string
     */
    public function pluginExists($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('$name must be a string');
        }

        $plugin = \Plugin::model()->findByAttributes(array('name' => $name));

        return !empty($plugin);
    }

    /**
     * Returns true if plugin with name $name is active; otherwise false
     *
     * @param string $name Name of plugin
     * @return boolean
     * @throws InvalidArgumentException if $name is not a string
     * @throws Exception if no plugin with name $name is found
     */
    public function pluginIsActive($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('$name must be a string');
        }

        $plugin = \Plugin::model()->findByAttributes(array('name' => $name));

        if ($plugin) {
            return $plugin->active == 1;
        } else {
            throw new Exception("Can't find a plugin with name ".$name);
        }
    }

    /**
     * @param string $file
     * @param array $data
     * @return string
     */
    public function renderTwig($file, array $data)
    {
        return Yii::app()->twigRenderer->renderViewFromFile(
            $file,
            $data,
            // Don't echo.
            true,
            // Don't append application dir.
            false
        );
    }
}
