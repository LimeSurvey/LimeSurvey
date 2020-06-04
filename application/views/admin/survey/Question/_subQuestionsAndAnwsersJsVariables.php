<?php
/**
 * This subview render the javaScript variables for subQuestion_view and answerOptions_view
 * @var $jsVariableType  Define which type of javascript variables should be render
 */


$scriptVariables = "
    // Common variables between subquestions / answers options
    var cancel                  = '".gT('Cancel')."';
    var check                   = true;
    var lafail                  = '".gT('Sorry, the request failed!')."';
    var lanameurl               = '".Yii::app()->createUrl('/admin/labels/sa/getAllSets')."';
    var langs                   = '".implode(';',$anslangs)."';
    var languagecount           =  ".count($anslangs).";
    var lasaveurl               = '".Yii::app()->createUrl('/admin/labels/sa/ajaxSets')."';
    var lasuccess               = '".gT('The records have been saved successfully!')."';
    var lsbrowsertitle          = '".gT('Label set browser','js')."';
    var lsdetailurl             = '".Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetdetails')."';
    var lspickurl               = '".Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetpicker')."';
    var ok                      = '".gT('OK')."';
    var saveaslabletitle        = '".gT('Save as label set','js')."';
    var sCheckLabelURL          = '".Yii::app()->createUrl('/admin/questions/sa/ajaxchecklabel')."';
    var sImageURL               = '".Yii::app()->getConfig('adminimageurl')."';
    var sLabelSetName           = '".gT('Label set name','js')."';
    var strcode                 = '".gT('Code','js')."';
    var strlabel                = '".gT('Label','js')."';
    var strNoLabelSet           = '".gT('There are no label sets which match the survey default language','js')."';
";
if ($viewType=='subQuestions') {
    $scriptVariables .= "
        // variables with different values in subqestions / answer options
        var newansweroption_text     = '".gT('New answer option','js')."';
        var quickaddtitle            = '".gT('Quick-add answers','js')."';
        var strCantDeleteLastAnswer  = '".gT('You cannot delete the last answer option.','js')."';
        var duplicatesubquestioncode = '".gT('Error: You are trying to use duplicate subquestion codes.','js')."';
        var clickToExpand            = '".gT('Click to expand')."';
    ";
} elseif($viewType=='answerOptions') {
    $scriptVariables .= "
        // variables with different values in subqestions / answer options
        var newansweroption_text    = '".gT('New answer option','js')."';
        var quickaddtitle           = '".gT('Quick-add answers','js')."';
        var strCantDeleteLastAnswer = '".gT('You cannot delete the last answer option.','js')."';

        // answer options variables
        var assessmentvisible       =  ".( $assessmentvisible ? 'true' : 'false' ).";
        var duplicateanswercode     = '".gT('Error: You are trying to use duplicate answer codes.','js')."';
        var sAssessmentValue        = '".gT('Assessment value','js')."';
        var scalecount              =  ".$scalecount.";

    ";
}

Yii::app()->getClientScript()->registerCssFile( Yii::app()->getConfig('publicstyleurl').'subquestionandansweroptions.css');
Yii::app()->getClientScript()->registerScript('SubquestionandAnswers-variables',  $scriptVariables, LSYii_ClientScript::POS_BEGIN );

echo PrepareEditorScript(true, $this);
