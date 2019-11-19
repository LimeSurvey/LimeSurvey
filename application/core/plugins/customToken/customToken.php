<?php
class customToken extends PluginBase {

    protected $storage = 'DbStorage';
    static protected $name = 'customToken';
    static protected $description = 'Numeric, non-ambiguous or CAPITAL tokens';

    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('afterGenerateToken', 'generateCustomToken');

        // Provides survey specific settings.
        $this->subscribe('beforeSurveySettings');

        // Saves survey specific settings.
        $this->subscribe('newSurveySettings');

        // Clean up on deactivate
        $this->subscribe('beforeDeactivate');
    }

    /**
     * The custom generate function
     */
    public function generateCustomToken()
    {
        $event = $this->getEvent();
        $iSurveyID=$event->get('surveyId');
        $iTokenLength = $event->get('iTokenLength');
        $token = "";
        if ($this->get('customToken', 'Survey', $iSurveyID) == 0) {
            // 0 = No custom function for this survey: return without changes in $event
            return;
        }
        else if ($this->get('customToken', 'Survey', $iSurveyID) == 1) {
            // 1 = Numeric tokens
            $token = randomChars($iTokenLength, '123456789');
        }
        else if ($this->get('customToken', 'Survey', $iSurveyID) == 2) {
            // 2 = Without ambiguous characters including 'hard to manually enter'
            // https://github.com/LimeSurvey/LimeSurvey/commit/154e026fbe6e53037e46a8c30f2b837459235acc
            $token = str_replace(
                array('~','_','0','O','1','l','I'),
                array('a','z','7','P','8','k','K'), Yii::app()->securityManager->generateRandomString($iTokenLength));
        }
        else if ($this->get('customToken', 'Survey', $iSurveyID) == 3) {
            // 3 = CAPITALS ONLY
            if (function_exists('crypto_rand_secure')) {
                /**
                 * Adjusted from Yii::app()->securityManager->generateRandomString($length=32)
                 * https://github.com/LimeSurvey/LimeSurvey/blob/master/application/core/web/LSYii_SecurityManager.php#L71
                 * Use crypto_rand_secure($min, $max) defined in application/helpers/common_helper.php
                 */
                $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                for($i=0;$i<$iTokenLength;$i++){
                    $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
                }
            } else {
                /**
                 * Secure enough, although not cryptographically secure
                 * https://www.php.net/manual/en/function.rand.php
                 */
                for($i=0;$i<$iTokenLength;$i++){
                    $token .= chr(64+rand(1, 26));
                }
            }
        }
        $event->set('token', $token);
    }

    /**
    * This event is fired by the administration panel to gather extra settings
    * available for a survey. Example URL in LS 3.17:
    * /index.php/admin/survey/sa/rendersidemenulink/subaction/plugins/surveyid/46159
    */
    public function beforeSurveySettings()
    {
        $pluginsettings = $this->getPluginSettings(true);

        $event = $this->getEvent();
        $iSurveyID=$event->get('iSurveyID');
        $event->set("surveysettings.{$iSurveyID}", array(
            'name' => get_class($this),
            'settings' => array(
                'customToken' => array(
                    'type' => 'select',
                    'options'=>array(
                        0=>'No custom function for this survey',
                        1=>'Numeric tokens',
                        2=>'Without ambiguous characters',
                        3=>'CAPITALS ONLY'
                        ),
                    'default' => 0,
                    'label' => 'Custom token:',
                    'current' => $this->get('customToken', 'Survey', $event->get('survey'))
                )
            )
        ));
    }

    /**
     * Save the settings
     */
    public function newSurveySettings()
    {
        $event = $this->getEvent();
        foreach ($event->get('settings') as $name => $value)
        {
                $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }
    
    /**
     * Clean up the plugin settings table
     */
    public function beforeDeactivate()
    {
        $sDBPrefix = Yii::app()->db->tablePrefix;
        $sql = "DELETE FROM {$sDBPrefix}plugin_settings WHERE `key` LIKE :key";
        Yii::app()->db->createCommand($sql)->execute(array(':key' => "customToken"));
    }

}
