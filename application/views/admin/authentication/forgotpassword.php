<div class='header'><?php $clang->eT('Recover your password'); ?></div>
    <p><?php $clang->eT('To receive a new password by email you have to enter your user name and original email address.'); ?>
    </p>
    <p>&nbsp;</p>
<form class="form44" name="forgotpassword" id="forgotpassword" method="post" action="<?php echo $this->createUrl("admin/authentication/forgotpassword"); ?>" >

    <ul>
        <li><label for="user"><?php $clang->eT('User name'); ?></label><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></li>
        <li><label for="email"><?php $clang->eT('Email'); ?></label><input name="email" id="email" type="email" size="60" maxlength="60" value="" /></li>
    </ul>
        <p>
            <input type="hidden" name="action" value="forgotpass" />
            <input class="action" type="submit" value="<?php $clang->eT('Check data'); ?>" />
        </p>
        <p><a href="<?php echo $this->createUrl("/admin"); ?>"><?php $clang->eT('Main Admin Screen'); ?></a></p>
</form>
<p>&nbsp;</p>
