<?php
/**
 * Allow to add script to question.
 * @todo Show (and update/add) the settings according to XSS
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016 Denis Chenu <http://www.sondages.pro>
 * @copyright 2016 LimeSurvey <http://www.limesurvey.org>
 * @license GPL v3
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
 */
class addScriptToQuestion extends PluginBase
{

  static protected $name = 'addScriptToQuestion';
  static protected $description = 'Allow to add easily script to question.';

    /**
    * Add function to be used in beforeQuestionRender event and to attriubute
    */
    public function init()
    {
      $this->subscribe('beforeQuestionRender','addScript');
      $this->subscribe('newQuestionAttributes','addScriptAttribute');
    }

  public function addScript()
  {
    $oEvent=$this->getEvent();
    $aAttributes=QuestionAttribute::model()->getQuestionAttributes($oEvent->get('qid'));
    if(trim($aAttributes['javascript'])){
      $aReplacement=array(
        'QID'=>$oEvent->get('qid'),
        'SGQ'=>$oEvent->get('sgq'),
      );
      $script=LimeExpressionManager::ProcessString($aAttributes['javascript'], $oEvent->get('qid'), $aReplacement, false, 1, 1, false, false, true);
      App()->getClientScript()->registerScript("scriptAttribute{$oEvent->get('qid')}",$script,$aAttributes['scriptPosition']);
    }
  }

  public function addScriptAttribute()
  {
    $scriptAttributes = array(
      'javascript'=>array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>gT('Script'), /* Workaround ? Tony Partner :)))) ? */
        'sortorder'=>1, /* If leave in own category */
        'inputtype'=>'textarea',
        'default'=>'', /* not nbeeded (it's already the default) */
        'expression'=>1,/* As static */
        'help'=>gT('You don\'t have to add script tag, script is register by LimeSurvey. You can use Expression, this expression is static (no update during runtime).'),
        'caption'=>gT('Javascript for this question'),
        'readonly'=>!(!Yii::app()->getConfig('filterxsshtml') || Permission::model()->hasGlobalPermission('superadmin','read')), /* This only set the readonly part: must rework on the system of readonly, and user still allowed to import js */
      ),
      'scriptPosition'=>array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>gT('Script'),
        'sortorder'=>1,
        'inputtype'=>'singleselect',
        'options'=>array(
          CClientScript::POS_HEAD=>"The script is inserted in the head section right before the title element (POS_HEAD).",
          CClientScript::POS_BEGIN=>"The script is inserted at the beginning of the body section (POS_BEGIN).",
          CClientScript::POS_END=>"The script is inserted at the end of the body section (POS_END).",
          CClientScript::POS_LOAD=>"the script is inserted in the window.onload() function (POS_LOAD).",
          CClientScript::POS_READY=>"the script is inserted in the jQuery's ready function (POS_READY).",
        ),
        'default'=>CClientScript::POS_END, /* This is really the best solution */
        'help'=>gT('Set the position of the script, see http://www.yiiframework.com/doc/api/1.1/CClientScript#registerScript-detail .'),
        'caption'=>gT('Position for the script'),
        'readonly'=>Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin','read'), /* This only set the readonly part: must rework on the system of readonly, and user still allowed to import js */
      ),
    );
    $this->getEvent()->append('questionAttributes', $scriptAttributes);
  }

}
