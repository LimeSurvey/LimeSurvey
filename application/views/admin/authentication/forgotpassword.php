<form class="form44" name="forgotpassword" id="forgotpassword" method="post" action="<?php echo $this->createUrl("admin/authentication/forgotpassword"); ?>" >
    <p><strong><?php $clang->eT('You have to enter user name and email.'); ?></strong></p>

    <ul>
        <li><label for="user"><?php $clang->eT('Username'); ?></label><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></li>
        <li><label for="email"><?php $clang->eT('Email'); ?></label><input name="email" id="email" type="text" size="60" maxlength="60" value="" /></li>
        <p>
            <input type="hidden" name="action" value="forgotpass" />
            <input class="action" type="submit" value="<?php $clang->eT('Check Data'); ?>" />
        </p>
        <p><a href="<?php echo $this->createUrl("/admin"); ?>"><?php $clang->eT('Main Admin Screen'); ?></a></p>
    </ul>
</form>
<p>&nbsp;</p>
