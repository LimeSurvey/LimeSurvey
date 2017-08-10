<?php if (strlen(trim((string)$textfrom)) > 0) : ?>
    <?php if (extension_loaded('tidy')) : ?>
        <?=tidy_repair_string($translateFields,array(),'utf8')?>
    <?php else:?>
        <?=$translateFields;?>
    <?php endif;?>
<?php else: ?>
    <input type='hidden' name='<?php echo $type; ?>_newvalue[<?php echo $i; ?>]' value='<?php echo $textto; ?>' />
<?php endif;?>
<?php // Display translation fields ?>
