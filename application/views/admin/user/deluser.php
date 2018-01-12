<div class='header'><?php eT("Delete user"); ?></div>
<div class='messagebox'>
    <?php echo CHtml::form(array("admin/user/sa/deluser"), 'post', array('name'=>'deluserform', 'id'=>'deluserform')); ?>
        <?php eT("Transfer the surveys of this user to: "); ?>
        <select name='transfer_surveys_to'>
            <?php
                if (count($users) > 0)
                {
                    foreach ($users as $user)
                    {
                        $intUid = $user['uid'];
                        $sUsersName = $user['users_name'];
                        $selected = '';
                        if ($intUid == $current_user)
                            $selected = ' selected="selected"';

                        if ($postuserid != $intUid)
                        {
                        ?>
                        <option value="<?php echo $intUid; ?>" <?php echo $selected; ?>> <?php echo $sUsersName; ?></option>;
                        <?php
                        }
                    }
                }
            ?>
        </select>
        <input type="hidden" name="uid" value="<?php echo $postuserid; ?>" />
        <input type="hidden" name="user" value="<?php echo $postuser; ?>" />
        <input type="hidden" name="action" value="finaldeluser" />
        <input type="submit" value="<?php eT("Delete User"); ?>" />
    </form>
</div>
