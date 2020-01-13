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
            'url' => App()->createUrl('/admin/questions/sa/deleteMultiple/'),
            'iconClasses' => 'text-danger fa fa-trash',
            'text' => gT('Delete'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'yes-no',
            'keepopen' => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('/admin/questions/sa/renderItemsSelected/'),       
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
            'url' => App()->createUrl('/admin/questions/sa/setMultipleQuestionGroup/'),
            'iconClasses' => 'fa fa-folder-open',
            'text' => gT('Set question group and position'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'yes-no',
            'keepopen' => 'no',
            'yes' => gT('Apply'),
            'no' => gT('Cancel'),
            'sModalTitle' => gT('Set question group'),
            'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_question_group_position', array('model' => $model, 'oSurvey' => $oSurvey), true),
        );
    }

    // Set mandatory
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-mandatory',
        'url' => App()->createUrl('/admin/questions/sa/setMultipleMandatory/'),
        'iconClasses' => 'fa fa-asterisk text-danger',
        'text' => gT('Set "Mandatory" state'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'yes-no',
        'yes' => gT('Apply'),
        'no' => gT('Cancel'),
        'keepopen' => 'no',
        'sModalTitle' => gT('Set "Mandatory" state'),
        'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_questions_mandatory', ['model' => $model, 'oSurvey' => $oSurvey], true),
    );

    // Set CSS Class
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-css',
        'url' => App()->createUrl('/admin/questions/sa/setMultipleAttributes/'),
        'iconClasses' => 'fa fa-css3',
        'text' => gT('Set CSS class'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'yes-no',
        'yes' => gT('Apply'),
        'no' => gT('Cancel'),
        'keepopen' => 'no',
        'sModalTitle' => gT('Set CSS class'),
        'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_css_class', ['model' => $model], true),
    );

    // Set Statistics
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-statistics',
        'url' => App()->createUrl('/admin/questions/sa/setMultipleAttributes/'),
        'iconClasses' => 'fa fa-bar-chart',
        'text' => gT('Set statistics options'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'yes-no',
        'yes' => gT('Apply'),
        'no' => gT('Cancel'),
        'keepopen' => 'no',
        'sModalTitle' => gT('Set statistics options'),
        'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_statistics_options',  ['model' => $model], true),
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
        // DEPEND IF SURVEY IS ACTIVE !!!! (checked by /admin/questions/sa/setMultipleOther/ )
        // TODO: don't show that action if survey is active
        $aActions[] = array(
            // li element
            'type' => 'action',
            'action' => 'set-other',
            'url' => App()->createUrl('/admin/questions/sa/setMultipleOther/'),
            'iconClasses' => 'fa fa-dot-circle-o',
            'text' => gT('Set "Other" state'),
            'grid-reload' => 'yes',

            // modal
            'actionType' => 'modal',
            'modalType' => 'yes-no',
            'yes' => gT('Apply'),
            'no' => gT('Cancel'),
            'keepopen' => 'no',
            'sModalTitle' => gT('Set "Other" state'),
            'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_questions_other', ['model' => $model], true),
        );
    }    

    // Set subquestions/answers sort options
    $aActions[] = array(
        // li element
        'type' => 'action',
        'action' => 'set-subquestions-answers-sort',
        'url' => App()->createUrl('/admin/questions/sa/setMultipleAttributes/'),
        'iconClasses' => 'fa fa-sort',
        'text' => gT('Present subquestions/answer options in random order'),
        'grid-reload' => 'yes',

        // modal
        'actionType' => 'modal',
        'modalType' => 'yes-no',
        'yes' => gT('Apply'),
        'no' => gT('Cancel'),
        'keepopen' => 'false',
        'sModalTitle' => gT('Present subquestions/answer options in random order'),
        'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_subquestansw_order',  ['model' => $model], true),
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

