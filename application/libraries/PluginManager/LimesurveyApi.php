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
            
    }

?>