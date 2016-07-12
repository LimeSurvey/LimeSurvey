<?php
/**
 * Render the selector for question massive actions.
 * @var $model      The question model
 * @var $oSurvey    The survey object
 */
?>

<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
            'pk'          => 'id',
            'gridid'      => 'question-grid',
            'dropupId'    => 'questionListActions',
            'dropUpText'  => gT('Selected question(s)...'),

            'aActions'    => array(

                // Download header
                array(

                    // li element
                    'type' => 'dropdown-header',
                    'text' => gT("General"),
                ),

                // Delete
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'delete',
                    'url'         => App()->createUrl('/admin/questions/sa/deleteMultiple/'),
                    'iconClasses' => 'text-danger glyphicon glyphicon-trash',
                    'text'        =>  gT('Delete'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'keepopen'      => 'yes',
                    'sModalTitle'   => gT('Delete questions'),
                    'htmlModalBody' => gT('Are you sure you want to delete all those questions??'),
                ),

                // Set question and group
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'set-group-position',
                    'url'         => App()->createUrl('/admin/questions/sa/setMultipleQuestionGroup/'),
                    'iconClasses' => 'fa fa-folder-open',
                    'text'        =>  gT('Set question group and position'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'keepopen'      => 'no',
                    'yes'           => gT('apply'),
                    'no'            => gT('cancel'),
                    'sModalTitle'   => gT('Set question group'),
                    'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_question_group_position', array('model'=>$model, 'oSurvey'=>$oSurvey), true),
                ),

                // Set question and group
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'set-mandatory',
                    'url'         => App()->createUrl('/admin/questions/sa/setMultipleMandatory/'),
                    'iconClasses' => 'fa fa-asterisk text-danger',
                    'text'        =>  gT('Set "mandatory" state'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'yes'           => gT('apply'),
                    'no'            => gT('cancel'),
                    'keepopen'      => 'no',
                    'sModalTitle'   => gT('Set mandatory state'),
                    'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_questions_mandatory', array('model'=>$model, 'oSurvey'=>$oSurvey), true),
                ),

                // Separator
                array(

                    // li element
                    'type'  => 'separator',
                ),

                // Download header
                array(

                    // li element
                    'type' => 'dropdown-header',
                    'text' => gT("Advanced").' '.'('.gT("only apply to certain question types").')',
                ),


                // Set other
                // DEPEND IF SURVEY IS ACTIVE !!!!
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'set-other',
                    'url'         => App()->createUrl('/admin/questions/sa/setMultipleOther/'),
                    'iconClasses' => 'fa fa-dot-circle-o',
                    'text'        =>  gT('Set "other" state'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'yes'           => gT('apply'),
                    'no'            => gT('cancel'),
                    'keepopen'      => 'yes',
                    'sModalTitle'   => gT('Set "other" state'),
                    'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_questions_other', array('model'=>$model, 'oSurvey'=>$oSurvey), true),
                ),

                // Set CSS Class
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'set-css',
                    'url'         => App()->createUrl('/admin/questions/sa/setMultipleCSS/'),
                    'iconClasses' => 'fa fa-css3',
                    'text'        =>  gT('Set "CSS" class'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'yes'           => gT('apply'),
                    'no'            => gT('cancel'),
                    'keepopen'      => 'no',
                    'sModalTitle'   => gT('Set "CSS" class'),
                    'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_css_class', array('model'=>$model, 'oSurvey'=>$oSurvey), true),
                ),

                // Set subquestions/answers sort options
                array(
                    // li element
                    'type'        => 'action',
                    'action'      => 'set-subquestions-answers-sort',
                    'url'         => App()->createUrl('/admin/questions/sa/setMultipleSubQuestionOrAnswerOrder/'),
                    'iconClasses' => 'fa fa-sort',
                    'text'        =>  gT('Present subquestions/answer options in random order'),
                    'grid-reload' => 'yes',

                    // modal
                    'actionType'    => 'modal',
                    'modalType'     => 'yes-no',
                    'yes'           => gT('apply'),
                    'no'            => gT('cancel'),
                    'keepopen'      => 'false',
                    'sModalTitle'   => gT('Present subquestions/answer options in random order'),
                    'htmlModalBody' => $this->renderPartial('./survey/Question/massive_actions/_set_subquestansw_order', array('model'=>$model, 'oSurvey'=>$oSurvey), true),
                    // for question types : !ABCEFHKLMOPQRWZ1:;
                ),

            ),

    ));
?>


<!--
    Some widgets in the modals need to be reloaded after grid update
-->
<script>
$(document).ready(function() {
    $('#mandatory').on('switchChange.bootstrapSwitch', function(event, state) {
        $('#mandatory').attr('value', state);
    });

    $('#other').on('switchChange.bootstrapSwitch', function(event, state) {
        $('#other').attr('value', state);
        console.log(state);
    });

    $('#question-grid').on('actions-updated', function(){
        loadPositionWidget();
        $('#mandatory').bootstrapSwitch();

        $('#mandatory').on('switchChange.bootstrapSwitch', function(event, state) {
            $('#mandatory').attr('value', state);
        });

        $('#other').on('switchChange.bootstrapSwitch', function(event, state) {
            $('#other').attr('value', state);
        });
    });
});
</script>

<?php /*
<div class="col-sm-4 pull-left dropup listActions" data-pk="id"  data-grid-id="question-grid" id="questionListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('Selected question(s)...');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="questionListActions">

        <li class="dropdown-header"> <?php eT("General");?></li>

        <li>
            <a
                href                     = "#"
                data-url                 = "<?php echo App()->createUrl('/admin/questions/sa/deleteMultiple/');?>"
                data-action              = "delete"
                data-action-title        = "<?php eT('Delete questions'); ?>"
                data-modal-warning-title = "<?php eT('Warning');?>"
                data-modal-warning-text  = "<?php eT('Are you sure you want to delete all those questions?');?>"
            >

                <span class="fa-stack small">
                  <i class="text-danger glyphicon glyphicon-trash fa-stack-1x "></i>
                  <i class="fa fa-circle-o fa-stack-2x hidden"></i>
                </span>
                <?php eT('Delete');?>
            </a>
        </li>

        <li>
            <a
                href               = "#"
                data-url           = "<?php echo App()->createUrl('/admin/questions/sa/setMultipleQuestionGroup/');?>"
                data-action        = "set-group"
                data-custom-modal  = "setquestiongroup"
                data-keepopen      = "no"
            >
            <span class="fa-stack small">
              <i class="fa fa-folder-open fa-stack-1x small"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>
            <?php eT("Set question group and position");?>
            </a>
        </li>

        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/questions/sa/setMultipleMandatory/');?>"
            data-action="set-mandatory"
            data-modal-warning-title="<?php eT('Mandatory option');?>"
            data-modal-warning-text="<?php eT('blablabla');?> <?php eT('Continue?');?>">

            <span class="fa-stack small">
              <i class="fa fa-asterisk fa-stack-1x small text-danger"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>

            <?php eT("Set mandatory option (on/off)");?>
            </a>
        </li>

        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/questions/sa/setMultipleStats/');?>"
            data-action="set-group"
            data-modal-warning-title="<?php eT('Set statistics options for those question(s))');?>"
            data-modal-warning-text="<?php eT('This will CCCCC.');?> <?php eT('Continue?');?>">

            <span class="fa-stack small">
              <i class="fa fa-bar-chart fa-stack-1x small"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>
            <?php eT("Set statistics options for those question(s)");?>
            </a>
        </li>

        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Advanced");?></li>

        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/questions/sa/setMultipleOther/');?>"
            data-action="set-other"
            data-modal-warning-title="<?php eT('Set questions "other" option ');?>"
            data-modal-warning-text="<?php eT('This will make AAAAAAAAAAAAAAAAA.');?> <?php eT('Continue?');?>">

            <span class="fa-stack small">
              <i class="fa fa-dot-circle-o fa-stack-1x small"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>

            <?php eT("Set 'Other' options");?> <!-- mandatory, text, etc -->
            </a>
        </li>

        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/questions/sa/setMultipleOther/');?>"
            data-action="set-other"
            data-modal-warning-title="<?php eT('Set questions "other" option ');?>"
            data-modal-warning-text="<?php eT('This will make AAAAAAAAAAAAAAAAA.');?> <?php eT('Continue?');?>">

            <span class="fa-stack small">
              <i class="fa fa-css3 fa-stack-1x small"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>

            <?php eT("CSS class(es)");?> <!-- mandatory, text, etc -->
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/questions/sa/setMultipleOther/');?>"
            data-action="set-other"
            data-modal-warning-title="<?php eT('Set questions "other" option ');?>"
            data-modal-warning-text="<?php eT('This will make AAAAAAAAAAAAAAAAA.');?> <?php eT('Continue?');?>">

            <span class="fa-stack small">
              <i class="fa fa-sort fa-stack-1x small"></i>
              <i class="fa fa-circle-o fa-stack-2x hidden"></i>
            </span>

            <?php eT("Answers/Subquestions sort options");?> <!-- mandatory, text, etc -->
            </a>
        </li>


        <!-- Random order, CSS class(es),Sort answers alphabetically -->
    </ul>
</div>
*/?>
