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
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars($rowfrom['question_title'] . " / " . $rowfrom['code']) . " (" . $rowfrom['aid'] . ") </td>";
}
if ($type == 'question_help' || $type == 'question') {
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars((string) $rowfrom['title']) . " ({$rowfrom['qid']}) </td>";
} elseif ($type == 'subquestion') {
    $translateoutput .= "<td class='col-sm-2'>" . htmlspecialchars((string) $rowfrom['parent']['title']) . " ({$rowfrom['parent']['qid']}) </td>";
}

$translateoutput .= "<td class='_from_ col-sm-5' id='" . $type . "_from_" . $j . "'>"
    . showJavaScript($textfrom)
    . "</td>";

$translateoutput .= "<td class='col-sm-5'>";

$translateoutput .= CHtml::hiddenField("{$type}_id1_{$j}", $value1);
$translateoutput .= CHtml::hiddenField("{$type}_id2_{$j}", $value2);
if (is_numeric($iScaleID)) {
    $translateoutput .= CHtml::hiddenField("{$type}_scaleid_{$j}", $iScaleID);
}
$translateoutput .= CHtml::hiddenField("{$type}_oldvalue_{$j}", $textto);


$translateoutput .= '<div class="row">';

$cols = 73;
if($amTypeOptions['HTMLeditorDisplay'] === 'Modal'){
    $translateoutput .= '<div class="col-sm-10">';
    if($type == 'question_help' || $type == 'question' || $type == 'subquestion' || $type == 'answer'){
        $cols = 50;
    }
}else{
    $translateoutput .= '<div class="col-sm-12">';
}
$aDisplayOptions = array(
    'cols' => $cols,
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
    htmlspecialchars((string) $textto),
    $surveyId,
    $gid,
    $qid,
    "translate" . $amTypeOptions["HTMLeditorType"]
);
$translateoutput .= '</div>';
$translateoutput .= '<div class="col-sm-1">';
$translateoutput .= $this->loadEditor($amTypeOptions, $htmleditor_data);
$translateoutput .= '</div>';
$translateoutput .= '</div>'; //close row again
$translateoutput .= "</td>";
$translateoutput .= "</tr>";

echo $translateoutput;
