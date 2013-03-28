<?php 
    /**
    * Class exposing a Limesurvey API to plugins.
    * This class is instantiated by the plugin manager,
    * plugins can obtain it by calling getAPI() on the plugin manager.
    */
    class LimesurveyApi
    {

        /**
        * Sets a flash message to be shown to the user.
        * @param html $message
        */
        public function setFlash($message, $key ='api')
        {
            // @todo Remove direct session usage.
            Yii::app()->user->setFlash($key, $message);
        }

        /**
        * Builds and executes a SQL statement for creating a new DB table.
        *
        * @param string $table the name of the table to be created. The name will be properly quoted and prefixed by the method.
        * @param array $columns the columns (name=>definition) in the new table.
        * @param string $options additional SQL fragment that will be appended to the generated SQL.
        * @return integer number of rows affected by the execution.
        */        
        public function createTable($sTableName, $aColumns, $sOptions=null)
        {
            Yii:app()->loadHelper('database');
            return createTable('{{'.$sTableName.'}}', $aColumns, $sOptions=null);
        }

        /**
        * Check if a table does exist in the database
        *
        * @param string $sTableName Table name to check for (without dbprefix!))
        * @return boolean True or false if table exists or not
        */
        public function tableExists($sTableName) {
            return tableExists($sTableName);
        }

        /**
        * Evaluates an expression via Expression Manager
        * Uses the current context.
        * @param string $expression
        * @return string
        */
        public function EMevaluateExpression($expression)
        {
            $result = LimeExpressionManager::ProcessString($expression);
            return $result;
        }

        /**
        * Gets a response from the database.
        * 
        * @param int $surveyId
        * @param int $responseId
        */
        public function getResponse($surveyId, $responseId)
        {
            $response = Survey_dynamic::model($surveyId)->findByPk($responseId)->attributes;

            // Now map the response to the question codes if possible, duplicate question codes will result in the
            // old sidXgidXqid code for the second time the code is found
            $fieldmap = createFieldMap($surveyId, 'full',null, false, $response['startlanguage']);
            $output = array();
            foreach($response as $key => $value)
            {
                $newKey = $key;
                if (array_key_exists($key, $fieldmap)) {
                    if (array_key_exists('title', $fieldmap[$key]))
                    {
                        $code = $fieldmap[$key]['title'];
                        // Add subquestion code if needed
                        if (array_key_exists('aid', $fieldmap[$key]) && !empty($fieldmap[$key]['aid'])) {
                            $code .= '_' . $fieldmap[$key]['aid'];
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

        /**
        * Gets a key value list using the group name as value and the group id
        * as key.
        * @param type $surveyId
        * @return type
        */
        public function getGroupList($surveyId)
        {
            $result = Groups::model()->findListByAttributes(array('sid' => $surveyId), 'group_name');
            return $result;
        }
        
        /**
        * Retrieves user details for the currently logged in user
        * Returns false if the user is not logged and returns null if the user does not exist anymore for some reason (should not really happen)
        * @return User
        */
        public function getCurrentUser(){
            if (Yii::app()->session['loginID'])
            {
                return User::model()->findByPk(Yii::app()->session['loginID']);
            }
            return false;
        }

        /**
        * Retrieves user details for a user
        * Returns null if the user does not exist anymore for some reason (should not really happen)
        * @return User
        */
        public function getUser($iUserID){
            return User::model()->findByPk($iUserID);
        }

        
    }

?>