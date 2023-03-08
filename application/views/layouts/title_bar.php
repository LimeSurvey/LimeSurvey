<?php

if (!isset($oQuestion)) {
    $oQuestion = isset($qid) ? @Question::model()->find('qid=:qid', ['qid' => $qid]) : null;
}

if (!isset($oQuestionGroup)) {
    $oQuestionGroup = isset($gid) ? @QuestionGroup::model()->find('gid=:gid', ['gid' => $gid]) : null;
}
App()->getController()->widget('ext.BreadcrumbWidget.BreadcrumbWidget', [
    'breadCrumbConfigArray' => [
        'oSurvey' => $oSurvey ?? Survey::model()->findByPk((int)$surveyid),
        'oQuestion' => $oQuestion,
        'oQuestionGroup' => $oQuestionGroup,
        'sSubaction' => $subaction ?? null,
        'sSimpleSubaction' => $title_bar['subaction'] ?? null,
        'module_subaction' => $title_bar['module_subaction'] ?? null,
        'module_subaction_url' => $title_bar['module_subaction_url'] ?? null,
        'module_current_action' => $title_bar['module_current_action'] ?? null,
        'token' => $title_bar['token'] ?? null,
        'active' => $title_bar['active'] ?? null,
        'title' => $title_bar['title'] ?? ' ',
        'extraClass' => "title-bar-breadcrumb",
    ],
    'htmlOptions' => [
        'id' => 'breadcrumb-container',
        'class' => "ls-ba",

    ],
]);
?>

