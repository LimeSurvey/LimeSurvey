<?php if (strlen(trim((string)$textfrom)) > 0) : ?>
	<?=tidy_repair_string($translateFields)?>
<?php else: ?>
    <input type='hidden' name='<?php echo $type; ?>_newvalue[<?php echo $i; ?>]' value='<?php echo $textto; ?>' />
<?php endif;?>
<?php // Display translation fields ?>
