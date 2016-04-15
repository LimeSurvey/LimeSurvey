<?php
/**
 * render the error text when user submit a page without filling a mandatory question
 * called from em_manager_helper
 */
?>
<p class='errormandatory text-danger' role='alert'>
    <span class='glyphicon glyphicon-exclamation-sign'></span>
    <?php echo $sMandatoryText; ?>
</p>
