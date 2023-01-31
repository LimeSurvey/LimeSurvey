<?php if (!empty($closeBtnUrl)) : ?>
<!-- Close -->
<a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>">
    <span class="ri-close-fill"></span>
    <?php eT("Close");?>
</a>
<?php endif; ?>

<!-- White Close button -->
<?php if (!empty($showWhiteCloseButton)) : ?>
    <a class="btn btn-outline-secondary" href="<?php echo $closeUrl ?>">
        <span class="ri-close-fill"></span>
        <?php eT("Close"); ?>
    </a>
<?php endif; ?>

<!-- Save and close -->
<a id="save-and-close-button" class="btn btn-outline-secondary">
    <i class="ri-checkbox-fill"></i>
    <?php eT("Save and close");?>
</a>

<!-- Save -->
<a id="save-button" class="btn btn-primary" role="button">
    <i class="ri-check-fill"></i>
    <?php eT("Save");?>
</a>
