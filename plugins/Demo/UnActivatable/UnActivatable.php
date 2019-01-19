<?php
    /**
     * Example plugin that can not be activated.
     */
    class UnActivatable extends PluginBase
    {
        static protected $description = 'Demo: This plugin can not be activated';
        static protected $name = 'Unactivatable';

        public function init()
        {
            $this->subscribe('beforeActivate');
        }

        public function beforeActivate()
        {
            $event = $this->getEvent();
            $event->set('success', false);

            // Optionally set a custom error message.
            $event->set('message', 'Custom error message from plugin.');
        }
    }
?>
