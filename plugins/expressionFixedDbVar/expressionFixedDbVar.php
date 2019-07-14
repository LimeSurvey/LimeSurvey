<?php
/**
 * expressionFixedDbVar : add some fixed DB var : SEED, STARTDATE … 
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 Denis Chenu <http://www.sondages.pro>
 * @license GPL version 3
 * @version 0.0.0
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
    static protected $description = 'Add SEED and other DB var in Expression Manager.';
    static protected $name = 'expressionFixedDbVar';

    /**
    * @var array[] the settings
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
            'label' => 'Add SUBMITDATE variable',
            'default' => '0',
            'column' => 'startdate',
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
    );

    public function init()
    {
        /* Core plugin : add an update variables */
        $this->subscribe('setVariableExpressionEnd','addFixedDbVar');
        $this->subscribe('afterResponseSave','afterResponseSave');
        $this->subscribe('afterSurveyDynamicSave','afterResponseSave');
    }

    /**
     * Add the fixed know var to valid variables
     * @link https://manual.limesurvey.org/ExpressionManagerStart
     */
    public function addFixedDbVar()
    {
        $knownVarsToCreate = $this->_getAddedVars($this->event->get('surveyId'));
        if(empty($knownVarsToCreate)) {
            return;
        }
        $newKnowVars = array();
        foreach($knownVarsToCreate as $var) {
            $newKnowVars[$var] = array(
                'code'=>"", // We don't have it if we don't have Response
                'jsName_on'=>'',
                'jsName'=>'',
                'readWrite'=>'N',
            );
        }
        tracevar($newKnowVars);
        $this->getEvent()->append('knownVars', $newKnowVars);
    }

    public function afterResponseSave()
    {
        $knownVarsToCreate = $this->_getAddedVars($this->event->get('surveyId'));
        if(empty($knownVarsToCreate)) {
            return;
        }
        $oReponse = $this->getEvent()->get('model');
        foreach($knownVarsToCreate as $var) {
            $column = $this->settings[$var]['column'];
            if(isset($oReponse->$column)) {
                LimeExpressionManager::setValueToKnowVar($var,$oReponse->$column);
            }
        }
    }

    /**
     * get the fiuxed var to be added for this survey
     * @param integer $surveyId
     * @return string[]
     */
    private function _getAddedVars($surveyId)
    {
        $addedvars = array();
        foreach($this->settings as $var => $params)
        {
            if(isset($this->settings[$var]['column'])) {
                if( !empty($this->get($var,'Survey',$surveyId)) ) {
                    $addedvars[] = $var;
                } elseif($this->get($var,null,null,$this->settings[$var]['default']) ) {
                    // INHERIT
                    $addedvars[] = $var;
                }
            }
        }
        return $addedvars;
    }
}
