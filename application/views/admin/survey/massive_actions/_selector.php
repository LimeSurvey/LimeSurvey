<?php
/**
 * Render the selector for surveys massive actions.
 *
 */
?>
<div class="col-sm-4 pull-left dropup" id="surveyListActions">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php eT('With selected survey:');?>
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="surveyListActions">
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/survey/sa/deleteMultipleSurveys/');?>"
            data-action="delete"
            data-action-title="<?php eT('Delete surveys'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('Are you sure you want to delete all those surveys?');?>">
                <span class="text-danger glyphicon glyphicon-trash"></span>
                <?php eT('Delete');?>
            </a>
        </li>
        <li role="separator" class="divider"></li>
        <li class="dropdown-header"> <?php eT("Export...");?></li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/export/sa/exportMultipleArchiveSurveys/');?>"
            data-action="export"
            data-action-title="<?php eT('Export surveys archives'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('This will export the survey archives (.lsa) for all selected active surveys. They will be provided in a single zip archive. Are you sure you want to continue?');?>">
            <span class="icon-export" ></span>
            <?php eT("Survey archive (.lsa)");?>
            </a>
        </li>
        <li>
            <a href="#"
            data-url="<?php echo App()->createUrl('/admin/export/sa/exportMultipleStructureSurveys/');?>"
            data-action="export"
            data-action-title="<?php eT('Export surveys structures'); ?>"
            data-modal-warning-title="<?php eT('Warning');?>"
            data-modal-warning-text="<?php eT('This will export the survey structure (.lss) for all selected active surveys. They will be provided in a single zip archive. Are you sure you want to continue?');?>">
            <span class="icon-export" ></span>
            <?php eT("Survey structure (.lss)");?>
            </a>
        </li>
    </ul>
</div>
