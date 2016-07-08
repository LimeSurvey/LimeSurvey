<?php
/**
 * Render the selector for question massive actions.
 *
 */
?>
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
