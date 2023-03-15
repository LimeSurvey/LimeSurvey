<?php
/**
 * Render the selector for question massive actions.
 */

/** @var AdminController $this */
/** @var Question $model */
/** @var Survey $oSurvey */

?>

<!-- Rendering massive action widget -->

<?php

    /**
     * Here are defined the different massive actions for question grid.
     * To add a new massive action, just create a new entry in the aActions array, and write its related method in questions controller.
     * If the action need a form inside the modal so the user can set some values, please, use a subview (e.g: _set_question_group_position.php)
     *
     * @see documentation: https://github.com/LimeSurvey/LimeSurvey/tree/master/application/extensions/admin/grid/MassiveActionsWidget/README.md
     */

    $aActions = [];
    // Download header
    $aActions[] = array(

        // li element
        'type' => 'dropdown-header',
        'text' => gT("General"),
    );
    if(!$oSurvey->isActive) {
        // Delete
        $aActions[] = array(
            // li element
            'type' => 'action',
            'action' => 'delete',
            'url' => App()->createUrl('questionAdministration/deleteMultiple/'),
            'iconClasses' => 'ri-delete-bin-fill text-danger',
            'text' => gT('Delete'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'cancel-delete',
            'keepopen' => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('questionAdministration/renderItemsSelected/'),
            'sModalTitle' => gT('Delete question(s)'),
            'htmlModalBody' => gT('Deleting these questions will also delete their corresponding answer options and subquestions. Are you sure you want to continue??'),
        );
    }
    if(!$oSurvey->isActive) {
        // Set question and group
        $aActions[] = array(
            // li element
            'type' => 'action',
            'action' => 'set-group-position',
            'url' => App()->createUrl('questionAdministration/setMultipleQuestionGroup/'),
            'iconClasses' => 'ri-folder-line',
            'text' => gT('Set question group and position'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'cancel-apply',
            'keepopen' => 'no',
            'yes' => gT('Apply'),
            'no' => gT('Cancel'),
            'sModalTitle' => gT('Set question group'),
            'htmlModalBody' => $this->renderPartial('/admin/survey/Question/massive_actions/_set_question_group_position', array('model' => $model, 'oSurvey' => $oSurvey), true),
        );
    }

    // Set mandatory
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-mandatory',
        'url' => App()->createUrl('questionAdministration/changeMultipleQuestionMandatoryState/'),
        'iconClasses' => 'ri-star-fill text-danger',
        'text' => gT('Set "Mandatory" state'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'cancel-apply',
        'keepopen' => 'no',
        'sModalTitle' => gT('Set "Mandatory" state'),
        'htmlModalBody' => $this->renderPartial('/admin/survey/Question/massive_actions/_set_questions_mandatory', ['model' => $model, 'oSurvey' => $oSurvey], true),
    );

    // Set CSS Class
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-css',
        'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
        'iconClasses' => 'ri-css3-fill',
        'text' => gT('Set CSS class'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'cancel-apply',
        'keepopen' => 'no',
        'sModalTitle' => gT('Set CSS class'),
        'htmlModalBody' => $this->renderPartial('/admin/survey/Question/massive_actions/_set_css_class', ['model' => $model], true),
    );

    // Set Statistics
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-statistics',
        'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
        'iconClasses' => 'ri-bar-chart-fill',
        'text' => gT('Set statistics options'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'cancel-apply',
        'keepopen' => 'no',
        'sModalTitle' => gT('Set statistics options'),
        'htmlModalBody' => $this->renderPartial('/admin/survey/Question/massive_actions/_set_statistics_options',  ['model' => $model], true),
    );

    // Separator
    $aActions[] = array(
        // li element
        'type' => 'separator',
    );

    // Download header
    $aActions[] = array(
        // li element
        'type' => 'dropdown-header',
        'text' => gT("Advanced") . ' ' . '(' . gT("only apply to certain question types") . ')',
    );

    if(!$oSurvey->isActive) {
        // Set other
        // DEPEND IF SURVEY IS ACTIVE !!!! (checked by questionEditor/changeMultipleQuestionOtherState )
        // TODO: don't show that action if survey is active
        $aActions[] = array(
            // li element
            'type' => 'action',
            'action' => 'set-other',
            'url' => App()->createUrl('questionAdministration/changeMultipleQuestionOtherState'),
            'iconClasses' => 'ri-record-circle-line',
            'text' => gT('Set "Other" state'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'cancel-apply',
            //'yes' => gT('Apply'),
            //'no' => gT('Cancel'),
            'keepopen' => 'no',
            'sModalTitle' => gT('Set "Other" state'),
            'htmlModalBody' => $this->renderPartial('/admin/survey/Question/massive_actions/_set_questions_other', ['model' => $model], true),
        );
    }    

    // Set subquestions/answers sort options
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-subquestions-answers-sort',
        'url' => App()->createUrl('questionAdministration/changeMultipleQuestionAttributes/'),
        'iconClasses' => 'ri-shuffle-line',
        'text' => gT('Present subquestions/answer options in random order'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'cancel-apply',
        //'yes' => gT('Apply'),
        //'no' => gT('Cancel'),
        'keepopen' => 'false',
        'sModalTitle' => gT('Present subquestions/answer options in random order'),
        'htmlModalBody' => $this->renderPartial(
                '/admin/survey/Question/massive_actions/_set_subquestansw_order',
                ['model' => $model],
                true
        ),
    );



    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'question-grid',
            'dropupId'    => 'questionListActions',
            'dropUpText'  => gT('Selected question(s)...'),
            'aActions'    => $aActions,
    ));
?>


<!--
    Some widgets in the modals need to be reloaded after grid update
-->
<?php App()->getClientScript()->registerScript("ListQuestions-massiveAction-1", "
    $(function(){
        $('#question-grid').on('actions-updated', function(){
            loadPositionWidget();
        });
    });
 ", LSYii_ClientScript::POS_END); ?>

