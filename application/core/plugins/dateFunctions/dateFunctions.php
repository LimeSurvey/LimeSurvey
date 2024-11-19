<?php

class dateFunctions extends PluginBase
{
    protected static $description = 'Core: Date related Expression Manager functions';
    protected static $name = 'dateFunctions';

    public function init()
    {
        $this->subscribe('ExpressionManagerStart');
    }

    public function ExpressionManagerStart()
    {
        Yii::setPathOfAlias(get_class($this), dirname(__FILE__));
        $newFunctions = array(
            'localize_date' => array(
                '\dateFunctions\EMFunctions::localize_date',
                null, // No javascript function : set as static function
                $this->gT("Formats a date according to the Survey's date format for the specified language. Example: localize_date(VALIDUNTIL, TOKEN:LANGUAGE)"), // Description for admin
                'string localize_date(date [, language])', // Extra description
                'https://manual.limesurvey.org/', // Help url
                1, 2 // Number of arguments : 1 or 2 (language is optional)
            )
        );
        $this->getEvent()->append('functions', $newFunctions);

        /**
         * TODO: Update the manual URL
         */
    }
}
