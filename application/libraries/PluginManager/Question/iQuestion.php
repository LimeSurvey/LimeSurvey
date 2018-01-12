<?php
namespace LimeSurvey\PluginManager;
    interface iQuestion
    {
        
        
        /**
         * @param integer $questionId
         * @param integer $responseId
         *
         * @return void
         */
        public function __construct(iPlugin $plugin, LimesurveyApi $api, $questionId = null, $responseId = null);
        
        /**
         * Function that returns meta data for the available attributes
         * for the question type.
         * 
         */
        public function getAttributes($language = null);
        
        /**
         * Returns the number of question this question contains.
         * Defaults to 1, can be set to 0 if this question should not be counted
         * like in case for display only or equation questions.
         * @return int 
         */
        public function getCount();
        
        /**
         * This function derives a unique identifier for identifying a question type.
         * @return string
         */
        public static function getGUID();
        
        /**
         * Returns the variables exposed by this question. 
         * The returned array contains a key for each variable name and the value is an array with meta data.
         * @return array
         */
        public function getVariables();
        /**
         * @param bool $return If true, return the content instead of outputting it.
         */
        public function render($name, $language, $return = false);
        
        
        /**
         * This function must save the custom question attributes for a question.
         * The default implementation just iterates over the array and saves each property.
         * @param $attributes A array containing the value for each attribute filled in.
         * @return boolean True on success, false on failure(s).
         */
        public function saveAttributes(array $attributes, $qid = null);
        
        
        
    }
