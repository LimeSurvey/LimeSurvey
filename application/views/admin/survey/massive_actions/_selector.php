<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<div class="col-sm-4 pull-left dropup listActions" data-pk="sid" data-grid-id="survey-grid" id="surveyListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('Selected survey(s)...');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="surveyListActions">
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/survey/sa/deleteMultiple/');?>"
            data-action="delete"
            data-action-title="<?php eT('Delete surveys'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete all those surveys?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Export as...");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/export/sa/exportMultipleArchiveSurveys/');?>"
            data-action="export"
            data-modal-warning-title="<?php eT('Export survey archive');?>"
            data-modal-warning-text="<?php eT('This will export the survey archive (.lsa) for all selected active surveys. They will be provided in a single ZIP archive.');?> <?php eT('Continue?');?>">
            <span class="icon-export" ></span>
            <?php eT("Survey archive (.lsa)");?>
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/export/sa/exportMultipleStructureSurveys/');?>"
            data-action="export"
            data-modal-warning-title="<?php eT('Export survey structure');?>"
            data-modal-warning-text="<?php eT('This will export the survey structure (.lss) for all selected active surveys. They will be provided in a single ZIP archive.');?> <?php eT('Continue?');?>">
            <span class="icon-export" ></span>
            <?php eT("Survey structure (.lss)");?>
            </a>
        </li>
    </ul>
</div>
