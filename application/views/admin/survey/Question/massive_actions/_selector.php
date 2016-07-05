<?php
/**
 * Render the selector for question massive actions.
 *
 */
?>
<div class="col-sm-4 pull-left dropup listActions" data-pk="sid" data-grid-id="survey-grid" id="surveyListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('Selected question(s)...');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="surveyListActions">
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/question/sa/deleteMultiple/');?>"
            data-action="delete"
            data-action-title="<?php eT('Delete questions'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete all those questions?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Mandatory state");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/question/sa/setMultipleMandatory/');?>"
            data-action="set-mandatory"
            data-modal-warning-title="<?php eT('Set questions to mandatory ');?>"
            data-modal-warning-text="<?php eT('This will make all those question mandatories.');?> <?php eT('Continue?');?>">

            <span class="fa fa-asterisk text-danger"></span>

            <?php eT("Mandatory");?>
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/question/sa/setMultipleNonMandatory/');?>"
            data-action="set-non-mandatory"
            data-modal-warning-title="<?php eT('Set questions to non mandatory ');?>"
            data-modal-warning-text="<?php eT('This will make all those question NON mandatories.');?> <?php eT('Continue?');?>">

            <span class="fa-stack">
              <i class="fa fa-asterisk fa-stack-1x"></i>
              <i class="fa fa-ban fa-stack-2x text-danger"></i>
            </span>
            <?php eT("Non mandatory");?>
            </a>
        </li>
    </ul>
</div>
