<?php
/**
 * This subview render the javaScript variables for subQuestion_view and answerOptions_view
 * TODO: Move this view to questionAdministration folder.
 */

// Common variables between subquestions / answers options
$scriptVariables = [
    'langs'            => implode(';', $anslangs),
    'languagecount'    => count($anslangs),
    'cancel'           => gT('Cancel'),
    'ok'               => gT('OK'),
    'sLabelSetName'    => gT('Label set name','js'),
    'strNoLabelSet'    => gT('There are no label sets which match the survey default language','js'),
    'labelSetSuccess'  => gT('The records have been saved successfully!'),
    'labelSetFail'     => gT('Sorry, the request failed!'),
    'lanameurl'        => Yii::app()->createUrl('/admin/labels/sa/getAllSets'),
    'lasaveurl'        => Yii::app()->createUrl('/admin/labels/sa/ajaxSave'),
    'laupdateurl'      => Yii::app()->createUrl('/admin/labels/sa/ajaxUpdate'),
    'lsdetailurl'      => Yii::app()->createUrl('/questionAdministration/getLabelsetDetails'),
    'lspickurl'        => Yii::app()->createUrl('/questionAdministration/getLabelsetPicker'),
    'sCheckLabelURL'   => Yii::app()->createUrl('/questionAdministration/checkLabel'),
    'subquestions'     => [
        'newansweroption_text'     => gT('New subquestion','js'),
        'quickaddtitle'            => gT('Quick-add subquestion','js'),
        'strCantDeleteLastAnswer'  => gT('You cannot delete the last subquestion.','js'),
        'duplicatesubquestioncode' => gT('Error: You are trying to use duplicate subquestion codes.','js'),
        'clickToExpand'            => gT('Click to expand'),
    ],
    'answeroptions'    => [
        'newansweroption_text'    => gT('New answer option','js'),
        'quickaddtitle'           => gT('Quick-add answers','js'),
        'strCantDeleteLastAnswer' => gT('You cannot delete the last answer option.','js'),
        'assessmentvisible'       => ( $assessmentvisible ? 'true' : 'false' ),
        'duplicateanswercode'     => gT('Error: You are trying to use duplicate answer codes.','js'),
        'sAssessmentValue'        => gT('Assessment value','js'),
        'scalecount'              => $scalecount,
    ],
    'csrf' => [
        'tokenName'               => Yii::app()->request->csrfTokenName,
        'token'                   => Yii::app()->request->csrfToken,
    ],
    'checkQuestionValidateTitleURL' =>  Yii::app()->createUrl('questionAdministration/checkQuestionValidateTitle'),
    'checkSubquestionCodeIsUniqueURL' =>  Yii::app()->createUrl('questionAdministration/checkSubquestionCodeUniqueness'),
    'checkAnswerCodeIsUniqueURL' =>  Yii::app()->createUrl(''),
];

?>

<input type="hidden" name="translation-strings-json" value="<?= htmlentities(json_encode($scriptVariables)); ?>" />
