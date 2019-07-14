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
    static protected $description = 'Add SEED and other DB var in Expression Manager.';
    static protected $name = 'expressionFixedDbVar';

    /**
    * @var array[] the settings
    */
    protected $settings = array(); // @todo : add DB var to be added

    public function init()
    {
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
        $knownVars = $this->event->get('knownVars');
        $language = $this->event->get('language');
        $newKnowVars = array(
            'SEED' => array(
                'code'=>"SEED",
                'jsName_on'=>'',
                'jsName'=>'',
                'readWrite'=>'N',
            ),
        );
        $this->event->set('knownVars',$knownVars);
    }

    public function afterResponseSave()
    {
        $oReponse = $this->getEvent()->get('model');
        if(!empty($oReponse->seed)) {
            LimeExpressionManager::setValueToKnowVar('SEED',$oReponse->seed);
        }
    }
}
