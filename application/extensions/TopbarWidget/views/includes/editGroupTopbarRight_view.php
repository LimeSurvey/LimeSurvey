<?php if (!empty($closeBtnUrl)) : ?>
<!-- Close -->
<a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>
<?php endif; ?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <a class="btn btn-outline-secondary" href="<?php echo $closeUrl ?>">
        <span class="fa fa-close"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>

<!-- Save and close -->
<a id="save-and-close-button" class="btn btn-outline-secondary">
    <i class="fa fa-check-square"></i>
    <?php eT("Save and close");?>
</a>

<!-- Save -->
<a id="save-button" class="btn btn-success" role="button">
    <i class="fa fa-check"></i>
    <?php eT("Save");?>
</a>
