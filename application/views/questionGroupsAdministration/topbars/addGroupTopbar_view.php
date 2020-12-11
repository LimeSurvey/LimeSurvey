<?php
/**
 * Question group editor bar
 * 
 * Copied from LS 3
 * 
 * Used in Group add
 */

 /**  @var string $closeBtnUrl */

?>

<!-- questiongroupbar -->
<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid row-button-margin-bottom'>
        <!-- Left Buttons -->
        <div class="col-md-4">
            <!-- Import -->
            <a class="btn btn-default" href="<?php echo Yii::App()->createUrl("questionGroupsAdministration/importview/surveyid/".$surveyid); ?>" role="button">
                <span class="icon-import"></span>
                <?php eT('Import group'); ?>
            </a>
        </div>

        <!-- Right Buttons -->
        <div class="col-sm-4 pull-right text-right">

            <!-- Save -->
            <a id="save-button" class="btn btn-success" role="button">
                <i class="fa fa-floppy-o"></i>
                <?php eT("Save");?>
            </a>

            <!-- Save and new group -->
            <a class="btn btn-default" id='save-and-new-button' role="button">
                <span class="fa fa-plus-square"></span>
                <?php eT("Save and new group"); ?>
            </a>

            <!-- Save and add question -->
            <a class="btn btn-default" id='save-and-new-question-button' role="button">
                <span class="fa fa-plus"></span>
                <?php eT("Save and add question"); ?>
            </a>

            <!-- Close -->
            <a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
                <span class="fa fa-close"></span>
                <?php eT("Close");?>
            </a>
        </div>
    </div>
</div>

