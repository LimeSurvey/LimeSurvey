<?php if (!empty($closeBtnUrl)) : ?>
<!-- Close -->
<button class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" type="button">
    <span class="fa fa-close"></span>
    <?php eT("Close");?>
</button>
<?php endif; ?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <button class="btn btn-outline-secondary" href="<?php echo $closeUrl ?>" type="button">
        <span class="fa fa-close"></span>
        <?php eT("Close"); ?>
    </button>
<?php endif; ?>

<!-- Save and close -->
<button type="button" id="save-and-close-button" class="btn btn-outline-secondary" type="button">
    <i class="fa fa-check-square"></i>
    <?php eT("Save and close");?>
</button>

<!-- Save -->
<button type="button" id="save-button" class="btn btn-success" type="button" type="button">
    <i class="fa fa-check"></i>
    <?php eT("Save");?>
</button>