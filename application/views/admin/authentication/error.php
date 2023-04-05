<div class="jumbotron message-box">
        <h2><?php echo $errormsg; ?></h2>
        <p class="lead"><?php echo $maxattempts; ?></p>
        <p><?php echo eT($sMessage); ?></p>
        <p>
            <a class="btn btn-lg btn-primary" href="<?php echo $this->createUrl("admin/"); ?>" role="button"><?php eT("Ok"); ?></a>
            <a class="btn btn-lg btn-outline-secondary"  href='<?php echo $this->createUrl("/admin/authentication/sa/login"); ?>'><?php eT("Try again"); ?></a><br />
            <a class="btn btn-lg btn-outline-secondary"  href='<?php echo $this->createUrl("/admin/authentication/sa/forgotpassword"); ?>'><?php eT("Forgot your password?"); ?></a><br />            
        </p>
</div>
