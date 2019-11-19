<?php
/**
 * expressionQuestionHelp : add QCODE.help for expression Manager
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 Denis Chenu <http://www.sondages.pro>
 * @license GPL version 3
 * @version 1.0.0
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
class expressionQuestionHelp extends PluginBase
{
    static protected $description = 'Add .help to properties of questions.';
    static protected $name = 'expressionQuestionHelp';

    
    public function init()
    {
        $this->subscribe('setVariableExpressionEnd','addQuestionHelp');
    }

    /**
     * Add the question.help if exist in existing knowVars
     * @link https://manual.limesurvey.org/ExpressionManagerStart
     */
    public function addQuestionHelp()
    {
        $knownVars = $this->event->get('knownVars');
        $language = $this->event->get('language');
        foreach($knownVars as $var => $values) {
            if(isset($values['question']) && isset($values['qid'])) {
                $oQuestionL10n = QuestionL10n::model()->find('qid = :qid and language = :language',array(":qid"=>$values['qid'],":language"=>$language));
                if($oQuestionL10n) {
                    $knownVars[$var]['help'] = $oQuestionL10n->help;
                }
            }
        }
        $this->event->set('knownVars',$knownVars);
        $this->event->append('newExpressionSuffixes',['help']);
    }
}
