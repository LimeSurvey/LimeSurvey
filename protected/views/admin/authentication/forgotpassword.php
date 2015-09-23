<div class='header'><?php eT('Recover your password'); ?></div>
    <p><?php eT('To receive a new password by email you have to enter your user name and original email address.'); ?>
    </p>
    <p>&nbsp;</p>
<?php echo CHtml::form(array("admin/authentication/sa/forgotpassword"), 'post', array('class'=>'form44','id'=>'forgotpassword','name'=>'forgotpassword'));?>

    <ul>
        <li><label for="user"><?php eT('User name'); ?></label><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></li>
        <li><label for="email"><?php eT('Email'); ?></label><input name="email" id="email" type="email" size="60" maxlength="60" value="" /></li>
    </ul>
        <p>
            <input type="hidden" name="action" value="forgotpass" />
            <input class="action" type="submit" value="<?php eT('Check data'); ?>" />
        </p>
        <p><a href="<?php echo $this->createUrl("/admin"); ?>"><?php eT('Main Admin Screen'); ?></a></p>
</form>
<p>&nbsp;</p>
