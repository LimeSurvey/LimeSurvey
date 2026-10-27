<?php

   /**
    * This view displays the sidemenu on the left side, containing the question explorer
    *
    */

?>
<?php
    //todo: change urls after refactoring the controllers
    $getQuestionsUrl = $this->createUrl("/surveyAdministration/getAjaxQuestionGroupArray/", ["surveyid" => $surveyid]);
    $getMenuUrl = $this->createUrl("/surveyAdministration/getAjaxMenuArray/", ["surveyid" => $surveyid]);
    $createQuestionGroupLink = $this->createUrl('/questionGroupsAdministration/add/', ["surveyid" => $surveyid]);
    $createQuestionLink =  $this->createUrl('questionAdministration/create/', ["surveyid" => $surveyid]);
    $unlockLockOrganizerUrl = $this->createUrl("admin/user/sa/togglesetting/", ['surveyid' => $surveyid]);

    $updateOrderLink =  $this->createUrl("questionGroupsAdministration/updateOrder/", ["surveyid" =>  $surveyid]);

    $createPermission = Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create');
if ($activated || !$createPermission) {
    $createQuestionGroupLink = "";
    $createQuestionLink = "";
}
    $menuObjectArray =  [
        "side" => [],
        "collapsed" => [],
        "top" => [],
        "bottom" => [],
    ];
    foreach ($menuObjectArray as $position => $arr) {
        $menuObjectArray[$position] = Survey::model()->findByPk($surveyid)->getSurveyMenus($position);
    }

    Yii::app()->getClientScript()->registerScript(
        'SideBarGlobalObject',
        '
        window.SideMenuData = {
            getQuestionsUrl: "' . $getQuestionsUrl . '",
            getMenuUrl: "' . $getMenuUrl . '",
            createQuestionGroupLink: "' . $createQuestionGroupLink . '",
            createQuestionLink: "' . $createQuestionLink . '",
            buttonDisabledTooltipQuestions: "' . gT('It is not possible to add questions to an active survey.') . '",
            buttonDisabledTooltipGroups: "' . gT('It is not possible to add groups to an active survey.') . '",
            gid: ' . ($gid ?? 'null') . ',
            options: [],
            surveyid: ' . $surveyid . ',
            isActive: ' . (Survey::model()->findByPk($surveyid)->isActive ? "true" : "false") . ',
            basemenus: ' . json_encode($menuObjectArray) . ',
            updateOrderLink: "' . $updateOrderLink . '",
            unlockLockOrganizerUrl: "' . $unlockLockOrganizerUrl . '",
            allowOrganizer: ' . (SettingsUser::getUserSettingValue('lock_organizer') ? '0' : '1') . ',
            translate: '
            . json_encode(
                [
                    "settings" => gT("Settings"),
                    "structure" => gT("Structure"),
                    "createPage" => gT("Add group"),
                    "createQuestion" => gT("Add question"),
                    "lockOrganizerTitle" => gT("Lock question organizer"),
                    "unlockOrganizerTitle" => gT("Unlock question organizer"),
                    "collapseAll" => gT("Collapse all question groups"),
                    "expandAll" => gT("Expand all question groups"),
                    "collapseGroup" => gT("Collapse group"),
                    "expandGroup" => gT("Expand group"),
                    "surveyLogicFile" => gT("Survey logic overview"),
                    "groupNumber" => gT("Group #%d"),
                    "deactivateSurvey" => gT("Deactivate your survey to enable this functionality"),
                    "pageActionsMenu" => gT("Page actions menu"),
                    "questionActionsMenu" => gT("Question actions menu"),
                    "moveUp" => gT("Move up"),
                    "moveDown" => gT("Move down"),
                    "movedToPosition" => gT('Moved to position %1$s of %2$s.', 'unescaped'),
                    "movedToGroup" => gT('Moved to group "%1$s", position %2$s of %3$s.', 'unescaped'),
                ]
            )
        . '};',
        LSYii_ClientScript::POS_HEAD
    );
    ?>

<?php // Filled by the adminsidepanel script ?>
<div class="simpleWrapper ls-flex" id="vue-sidebar-container"></div>
