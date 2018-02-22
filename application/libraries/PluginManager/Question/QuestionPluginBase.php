<?php
    namespace LimeSurvey\PluginManager;

    abstract class QuestionPluginBase extends PluginBase
    {




        /**
         * Lists the question objects supported by the plugin.
         * Use dot notation for indicating subdirectories.
         * Example 1: 'subdirectory.questionobject'
         * Example 2: 'questionobject'
         * @var array of string
         */
        protected $questionTypes = array(
        );


        protected $storage = 'DbStorage';
        /**
         *
         * @param string $id
         */

        /**
         * 
         *  
         * @param PluginManager $manager
         * @param integer $id
         */
        public function __construct(PluginManager $manager, $id)
        {
            parent::__construct($manager, $id);
            $this->subscribe('listQuestionPlugins');
        }

        /**
         * @param PluginEvent $event
         */
        public function listQuestionPlugins(PluginEvent $event)
        {
            if (!empty($this->questionTypes)) {
                $event->set('questionplugins.'.get_class($this), $this->questionTypes);
            }
        }



        /**
         * This function registers a javascript file to be included in the page.
         * $fileName can be either:
         * - Fully qualified url, will be used as is. (containing //)
         * - Limesurvey relative path, relative to limesurvey root. (starting with a single /)
         * - Local relative path, will be used as path relative inside the plugins' path.
         * -
         * @param string $fileName
         */
        protected function registerJs($fileName)
        {
            App()->getClientScript()->registerScriptFile($this->publish($fileName));

        }

        protected function registerCss($fileName)
        {
            App()->getClientScript()->registerCssFile($this->publish($fileName));
        }




    }
