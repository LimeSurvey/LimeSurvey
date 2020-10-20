<?php
/**
 * This subview render the javaScript variables for subQuestion_view and answerOptions_view
 * @var $jsVariableType  Define which type of javascript variables should be render
 */

// Common variables between subquestions / answers options
$scriptVariables = [
    'check'            => true,
    'langs'            => implode(';', $anslangs),
    'languagecount'    => count($anslangs),
    'cancel'           => gT('Cancel'),
    'lafail'           => gT('Sorry, the request failed!'),
    'lasuccess'        => gT('The records have been saved successfully!'),
    'lsbrowsertitle'   => gT('Label set browser','js'),
    'ok'               => gT('OK'),
    'saveaslabletitle' => gT('Save as label set','js'),
    'sLabelSetName'    => gT('Label set name','js'),
    'strcode'          => gT('Code','js'),
    'strlabel'         => gT('Label','js'),
    'strNoLabelSet'    => gT('There are no label sets which match the survey default language','js'),
    'lanameurl'        => Yii::app()->createUrl('/admin/labels/sa/getAllSets'),
    'lasaveurl'        => Yii::app()->createUrl('/admin/labels/sa/ajaxSets'),
    'lsdetailurl'      => Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetdetails'),
    'lspickurl'        => Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetpicker'),
    'sCheckLabelURL'   => Yii::app()->createUrl('/admin/questions/sa/ajaxchecklabel'),
    'sImageURL'        => Yii::app()->getConfig('adminimageurl'),
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
    ]
];

?>

<input type="hidden" name="translation-strings-json" value="<?= json_encode($scriptVariables); ?>" />
