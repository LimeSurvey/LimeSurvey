<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="h3 modal-title"><?php eT("Delete user"); ?></div>
</div>
<div class="modal-body">
    <?=TbHtml::formTb(null, App()->createUrl('userManagement/deleteUser'), 'post', ["id"=>"UserManagement--modalform"])?>
        <div class="container-center">
            <div class="row ls-space margin top-5">
                <label for="transfer_surveys_to"><?php eT("Transfer the surveys of this user to: "); ?></label>
                <select id='transfer_surveys_to' name='transfer_surveys_to' class='form-control'>
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
                <input type="hidden" name="userid" value="<?php echo $postuserid; ?>" />
                <input type="hidden" name="user" value="<?php echo $postuser; ?>" />
            </div>
            <div class="row ls-space margin top-35">
                <button class="btn btn-primary btn-ok col-sm-3 col-xs-5" id="submitForm"><?php eT("Delete User"); ?></button>
            </div>
        </div>
    </form>
    <script>
        window.LS.UserManagement.wireForm();
    </script>
</div>
