<?php if (!empty($closeBtnUrl)) : ?>
<!-- Close -->
<a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</a>
<?php endif; ?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <a class="btn btn-outline-secondary" href="<?php echo $closeUrl ?>" role="button">
        <span class="fa fa-close"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>

<!-- Save and close -->
<button type="button" id="save-and-close-button" class="btn btn-outline-secondary" role="button">
    <i class="fa fa-check-square"></i>
    <?php eT("Save and close");?>
</button>

<!-- Save -->
<button type="button" id="save-button" class="btn btn-success" role="button">
    <i class="fa fa-check"></i>
    <?php eT("Save");?>
</button>