<p>
    <?php echo $errormsg; ?><br />
    <?php echo $maxattempts; ?><br /><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/login"); ?>'><?php $clang->eT("Try again"); ?></a><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/forgotpassword"); ?>'><?php $clang->eT("Forgot your password?"); ?></a><br />
</p>
