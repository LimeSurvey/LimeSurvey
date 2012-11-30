<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'>
        <?php echo $errormsg; ?><br /><br />
        <?php echo $maxattempts; ?>
    </div><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/sa/login"); ?>'><?php $clang->eT("Try again"); ?></a><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/sa/forgotpassword"); ?>'><?php $clang->eT("Forgot your password?"); ?></a><br />
</div>
