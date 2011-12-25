<p>
    <?php echo $errormsg; ?><br />
    <?php echo $maxattempts; ?><br /><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/login"); ?>'><?php echo $clang->gT("Try again"); ?></a><br />
    <a href='<?php echo $this->createUrl("/admin/authentication/forgotpassword"); ?>'><?php echo $clang->gT("Forgot your password?"); ?></a><br />
</p>
