<?php
/**
 * render the error text when user submit a page without filling a mandatory question
 * called from em_manager_helper
 */
?>
<div class='ls-question-mandatory text-danger' role='alert'>
    <span class='fa fa-exclamation-circle' aria-hidden="true"></span>
    <?php echo $sMandatoryText; ?>
</div>
