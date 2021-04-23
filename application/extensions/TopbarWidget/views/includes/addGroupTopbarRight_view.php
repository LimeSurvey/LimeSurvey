<!-- Save -->
<a id="save-button" class="btn btn-success" role="button">
    <i class="fa fa-floppy-o"></i>
    <?php eT("Save");?>
</a>

<!-- Save and new group -->
<a class="btn btn-default" id='save-and-new-button' role="button">
    <span class="fa fa-plus-square"></span>
    <?php eT("Save & add new group"); ?>
</a>

<!-- Save and add question -->
<a class="btn btn-default" id='save-and-new-question-button' role="button">
    <span class="fa fa-plus"></span>
    <?php eT("Save & add new question"); ?>
</a>

<!-- Close -->
<a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>