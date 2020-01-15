<?php
/**
 * This view render the needed variables for the ajax process of the creation of a new question
 */

App()->getClientScript()->registerScript("EditQuestionView_basic_variables" ,"
    var attr_url = '".$this->createUrl('admin/questions', array('sa' => 'ajaxquestionattributes'))."';
    var get_question_template_options_url = '".$this->createUrl('admin/questions', array('sa' => 'ajaxGetQuestionTemplateList'))."';
    var imgurl = '".Yii::app()->getConfig('imageurl')."';
    var validateUrl = '".$sValidateUrl."';
    var questionTypeArray = ".$qTypeOutput.";
    var selectormodeclass = '".$selectormodeclass."';"
    , LSYii_ClientScript::POS_BEGIN );
?>
