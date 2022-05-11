<?php
/**
 * @var string $backUrl
 */

?>

<!-- Save -->
<button id="save-button" class="btn btn-success pull-right" type="button">
    <i class="fa fa-check"></i>
    <?php eT('Save'); ?>
</button>
<!-- Back Button -->
<a class="btn btn-default" href="<?php echo $backUrl ?>" role="button">
    <span class="fa fa-backward"></span>
    &nbsp;&nbsp;
    <?php eT('Back') ?>
</a>
<!-- Save and add question -->
<button class="btn btn-default" id='save-and-new-question-button' type="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add question'); ?>
</button>
<!-- Save and new group -->
<button class="btn btn-default" id='save-and-new-button' type="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add group'); ?>
</button>
