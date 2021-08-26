<?php
/**
 * @var string $backUrl
 */

?>

<!-- Save -->
<a id="save-button" class="btn btn-success pull-right" role="button">
    <i class="fa fa-check"></i>
    <?php eT('Save'); ?>
</a>
<!-- Back Button -->
<a class="btn btn-default" href="<?php echo $backUrl ?>" role="button">
    <span class="fa fa-backward"></span>
    &nbsp;&nbsp;
    <?php eT('Back') ?>
</a>
<!-- Save and add question -->
<a class="btn btn-default" id='save-and-new-question-button' role="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add question'); ?>
</a>
<!-- Save and new group -->
<a class="btn btn-default" id='save-and-new-button' role="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add group'); ?>
</a>
