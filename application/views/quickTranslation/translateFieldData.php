<?php
/* @var     $surveyId int */
/* @var     $gid  int */
/* @var     $qid int */
/* @var     $type */
/* @var     $amTypeOptions */
/* @var     $textfrom , */
/* @var     $textto */
/* @var     $j */
/* @var     $rowfrom */
/* @var     $nrows float */

$translateoutput = "<tr>";
$value1 = (!empty($amTypeOptions["id1"])) ? $rowfrom[$amTypeOptions["id1"]] : "";
$value2 = (!empty($amTypeOptions["id2"])) ? $rowfrom[$amTypeOptions["id2"]] : "";
$iScaleID = (!empty($amTypeOptions["scaleid"])) ? $rowfrom[$amTypeOptions["scaleid"]] : "";
// Display text in original language
// Display text in foreign language. Save a copy in type_oldvalue_i to identify changes before db update
if ($type == 'answer') {
    //$translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars($rowfrom['answer']) . " (" . $rowfrom['qid'] . ") </td>";
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars($rowfrom['question_title'] . " / " . $rowfrom['code']) . " (" . $rowfrom['aid'] . ") </td>";
}
if ($type == 'question_help' || $type == 'question') {
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars($rowfrom['title']) . " ({$rowfrom['qid']}) </td>";
} elseif ($type == 'subquestion') {
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars($rowfrom['parent']['title']) . " ({$rowfrom['parent']['qid']}) </td>";
}

$translateoutput .= "<td class='_from_ col-sm-5' id='" . $type . "_from_" . $j . "'><div class='question-text-from'>"
    . showJavaScript($textfrom)
    . "</div></td>";

$translateoutput .= "<td class='col-sm-5'>";

$translateoutput .= CHtml::hiddenField("{$type}_id1_{$j}", $value1);
$translateoutput .= CHtml::hiddenField("{$type}_id2_{$j}", $value2);
if (is_numeric($iScaleID)) {
    $translateoutput .= CHtml::hiddenField("{$type}_scaleid_{$j}", $iScaleID);
}
$translateoutput .= CHtml::hiddenField("{$type}_oldvalue_{$j}", $textto);

$aDisplayOptions = array(
    'class' => 'col-sm-10',
    'cols' => '75',
    'rows' => $nrows,
    'readonly' => !Permission::model()->hasSurveyPermission($surveyId, 'translations', 'update')
);
if ($type == 'group') {
    $aDisplayOptions['maxlength'] = 100;
}

$translateoutput .= CHtml::textArea("{$type}_newvalue_{$j}", $textto, $aDisplayOptions);
$htmleditor_data = array(
    "edit" . $type,
    $type . "_newvalue_" . $j,
    htmlspecialchars($textto),
    $surveyId,
    $gid,
    $qid,
    "translate" . $amTypeOptions["HTMLeditorType"]
);
$translateoutput .= $this->loadEditor($amTypeOptions, $htmleditor_data);

$translateoutput .= "</td>";
$translateoutput .= "</tr>";

echo $translateoutput;
