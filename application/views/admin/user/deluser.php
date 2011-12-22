<?php echo $clang->gT("Transfer the user's surveys to: "); ?>

<form method="post" name="deluserform" action="<?php echo $this->createUrl("admin/user/deluser"); ?>">
    <select name='transfer_surveys_to'>
        <?php
        if ($result->num_rows() > 0)
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
                    <option value="<?php echo $intUid; ?>" <?php echo $user['selected']; ?>> <?php echo $sUsersName; ?></option>;
<?php
                }

            }
        }
        ?>
    </select>
    <input type="hidden" name="uid" value="<?php echo $postuserid; ?>" />
    <input type="hidden" name="user" value="<?php echo $postuser; ?>" />
    <input type="hidden" name="action" value="finaldeluser" /><br /> <br />
    <input type="submit" value="<?php echo $clang->gT("Delete User"); ?>" />
</form>
