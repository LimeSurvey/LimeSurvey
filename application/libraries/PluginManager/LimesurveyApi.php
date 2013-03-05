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
         * Loads token information into EM.
         * @param int $surveyId
         * @param string $token
         */
        
        public function EMloadTokenInformation($surveyId, $token = null)
        {
            LimeExpressionManager::singleton()->loadTokenInformation($surveyId, $token);
        }
        
        /**
         * Gets a response from the database.
         * @param int $surveyId
         * @param int $responseId
         */
        public function getResponse($surveyId, $responseId)
        {
            return Survey_dynamic::model($surveyId)->findByPk($responseId)->attributes;
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