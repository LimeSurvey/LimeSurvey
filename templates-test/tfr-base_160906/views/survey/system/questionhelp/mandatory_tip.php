<?php
/**
 * render the error text when user submit a page without filling a mandatory question
 * called from em_manager_helper
 */
?>
<!-- views/survey/system/mandatory_tip -->
<p class="errormandatory text-danger" role="alert">
    <span class="icon icon-error" aria-hidden="true"></span>
    <?php echo $sMandatoryText; ?>
</p>
