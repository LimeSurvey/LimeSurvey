<?php

/**
 * expressionFixedDbVar : add some fixed DB var : SEED, STARTDATE …
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019-2021 LimeSurvey - Denis Chenu
 * @license GPL version 3
 * @version 1.0.2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
class expressionFixedDbVar extends PluginBase
{
    protected $storage = 'DbStorage';
    protected static $description = 'Add SEED and other DB var in ExpressionScript Engine.';
    protected static $name = 'expressionFixedDbVar';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    /**
    * @inheritdoc
    */
    protected $settings = array(
        'SEED' => array(
            'type' => 'checkbox',
            'label' => 'Add SEED variable',
            'default' => '1',
            'column' => 'seed',
        ),
        'SUBMITDATE' => array(
            'type' => 'checkbox',
            'label' => 'Add SUBMITDATE variable',
            'default' => '1',
            'column' => 'submitdate',
        ),
        'STARTDATE' => array(
            'type' => 'checkbox',
            'label' => 'Add STARTDATE variable',
            'default' => '0',
            'column' => 'startdate',
        ),
        'DATESTAMP' => array(
            'type' => 'checkbox',
            'label' => 'Add DATESTAMP variable',
            'default' => '1',
            'column' => 'datestamp',
        ),
        'LASTPAGE' => array(
            'type' => 'checkbox',
            'label' => 'Add LASTPAGE variable',
            'default' => '0',
            'column' => 'lastpage',
        ),
        'STARTLANGUAGE' => array(
            'type' => 'checkbox',
            'label' => 'Add STARTLANGUAGE variable',
            'default' => '0',
            'column' => 'startlanguage',
        ),
        'IPADDR' => array(
            'type' => 'checkbox',
            'label' => 'Add IPADDR variable',
            'default' => '0',
            'column' => 'ipaddr',
        ),
        'REFURL' => array(
            'type' => 'checkbox',
            'label' => 'Add REFURL variable',
            'default' => '0',
            'column' => 'ipaddr',
        ),
        'QUOTA_EXIT' => array(
            'type' => 'checkbox',
            'label' => 'Add QUOTA_EXIT variable',
            'default' => '0',
            'column' => 'quota_exit',
        ),
    );

    public function init()
    {
        /* Core plugin : add variables */
        $this->subscribe('setVariableExpressionEnd', 'addFixedDbVar');
        /* Core plugin : update variables just before public views */
        $this->subscribe('getPluginTwigPath', 'beforeTwigViews');

        /* Option by survey */
        $this->subscribe('beforeSurveySettings');
        $this->subscribe('newSurveySettings');
    }

    /**
     * Add the fixed know var to valid variables
     * @link https://www.limesurvey.org/manual/ExpressionManagerStart
     */
    public function addFixedDbVar()
    {
        $knownVarsToCreate = $this->getAddedVars($this->event->get('surveyId'));
        if (empty($knownVarsToCreate)) {
            return;
        }
        $newKnowVars = array();
        foreach ($knownVarsToCreate as $var) {
            $newKnowVars[$var] = array(
                'code' => "", // We don't have it if we don't have Response
                'jsName_on' => '',
                'jsName' => '',
                'readWrite' => 'N',
            );
        }
        $this->getEvent()->append('knownVars', $newKnowVars);
    }

    /**
     * Update value just before views
     */
    public function beforeTwigViews()
    {
        static $updated = false;
        if ($updated) {
            return;
        }
        $updated = true;
        $surveyId = LimeExpressionManager::getLEMsurveyId();
        if (empty($surveyId)) {
            return;
        }
        $knownVarsToCreate = $this->getAddedVars($surveyId);
        if (empty($knownVarsToCreate)) {
            return;
        }
        $oResponse = $this->api->getCurrentResponses();
        if (empty($oResponse)) {
            return;
        }
        foreach ($knownVarsToCreate as $var) {
            $column = $this->settings[$var]['column'];
            if (isset($oResponse->$column)) {
                LimeExpressionManager::setValueToKnowVar($var, $oResponse->$column);
            }
        }
    }

    /**
     * Add the option inside survey settings
     * @return void
     */
    public function beforeSurveySettings()
    {
        $newSettings = array();
        foreach ($this->settings as $var => $params) {
            if (isset($this->settings[$var]['column'])) {
                $inherited = $this->get($var, null, null, $params['default']) ? gT("Yes") : gT("No");
                $newSettings[$var] = array(
                    'type' => 'select',
                    'options' => array(
                        '1' => gT("Yes"),
                        '0' => gT("No"),
                    ),
                    'htmlOptions' => array(
                        'empty' => gT("Inherit") . " [{$inherited}]",
                    ),
                    'label' => $this->gT($params['label']),
                    'current' => $this->get($var, 'Survey', $this->getEvent()->get('survey'), ''),
                );
            }
        }
        $this->getEvent()->set("surveysettings.{$this->id}", array(
              'name' => get_class($this),
              'settings' => $newSettings
        ));
    }

    /**
     * Save the survey settings
     * @return void
     */
    public function newSurveySettings()
    {
        $event = $this->event;
        foreach ($event->get('settings') as $name => $value) {
            $this->set($name, $value, 'Survey', $event->get('survey'));
        }
    }

    /**
     * @inheritdoc
     * Add translation for label
     */
    public function getPluginSettings($getValues = true)
    {
        /* Translation inside plugin ? */
        $this->settings['SEED']['label'] = $this->gT('Add SEED variable');
        $this->settings['SUBMITDATE']['label'] = $this->gT('Add SUBMITDATE variable');
        $this->settings['STARTDATE']['label'] = $this->gT('Add STARTDATE variable');
        $this->settings['DATESTAMP']['label'] = $this->gT('Add DATESTAMP variable');
        $this->settings['LASTPAGE']['label'] = $this->gT('Add LASTPAGE variable');
        $this->settings['STARTLANGUAGE']['label'] = $this->gT('Add STARTLANGUAGE variable');
        $this->settings['IPADDR']['label'] = $this->gT('Add IPADDR variable');
        $this->settings['REFURL']['label'] = $this->gT('Add REFURL variable');
        $this->settings['QUOTA_EXIT']['label'] = $this->gT('Add QUOTA_EXIT variable');
        return parent::getPluginSettings($getValues);
    }

    /**
     * get the fiuxed var to be added for this survey
     * @param integer $surveyId
     * @return string[]
     */
    private function getAddedVars($surveyId)
    {
        $addedvars = array();
        foreach ($this->settings as $var => $params) {
            if (isset($this->settings[$var]['column'])) {
                $current = $this->get($var, 'Survey', $surveyId, "");
                if ($current === "") {
                    // INHERIT
                    $current = $this->get($var, null, null, $this->settings[$var]['default']);
                }
                if (boolval($current)) {
                    $addedvars[] = $var;
                }
            }
        }
        return $addedvars;
    }
}
