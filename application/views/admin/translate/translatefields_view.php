<?php
if (strlen(trim((string)$textfrom)) > 0)
{
	// Display translation fields
	echo $translateFields;
}
else
{
?>
    <input type='hidden' name='<?php echo $type; ?>_newvalue[<?php echo $i; ?>]' value='<?php echo $textto; ?>' />
<?php
}
?>
