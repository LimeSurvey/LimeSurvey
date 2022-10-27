<?php
/**
 * @var string $backUrl
 */

?>

<!-- Save -->
<button id="save-button" class="btn btn-success float-end" type="button">
    <i class="ri-check-fill"></i>
    <?php eT('Save'); ?>
</button>
<!-- Back Button -->
<a class="btn btn-outline-secondary" href="<?php echo $backUrl ?>">
    <span class="ri-rewind-fill"></span>
    &nbsp;&nbsp;
    <?php eT('Back') ?>
</a>
<!-- Save and add question -->
<button role="button" class="btn btn-outline-secondary" id='save-and-new-question-button' type="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add question'); ?>
</button>
<!-- Save and new group -->
<button role="button" class="btn btn-outline-secondary" id='save-and-new-button' type="button">
    <span class="fa fa-plus"></span>
    <?php eT('Save & add group'); ?>
</button>
