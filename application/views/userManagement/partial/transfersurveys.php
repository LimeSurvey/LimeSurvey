<div class="modal-header">
    <h5 class="modal-title"><?php eT("Delete user"); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <?= TbHtml::formTb(null, App()->createUrl('userManagement/deleteUser'), 'post',
        ["id" => "UserManagement--modalform"]) ?>
    <div class="row ls-space margin top-5">
        <label for="transfer_surveys_to"><?php eT("Transfer the surveys of this user to: "); ?></label>
        <select id='transfer_surveys_to' name='transfer_surveys_to' class='form-select'>
            <?php
            if (count($users) > 0) {
                foreach ($users as $user) {
                    $intUid = $user['uid'];
                    $sUsersName = $user['users_name'];
                    $selected = '';
                    if ($intUid == $current_user) {
                        $selected = ' selected="selected"';
                    }

                    if ($postuserid != $intUid) {
                        ?>
                        <option
                            value="<?php echo $intUid; ?>" <?php echo $selected; ?>> <?php echo $sUsersName; ?></option>;
                        <?php
                    }
                }
            }
            ?>
        </select>
        <input type="hidden" name="userid" value="<?php echo $postuserid; ?>"/>
        <input type="hidden" name="user" value="<?php echo $postuser; ?>"/>
    </div>
    <div class="row ls-space margin top-35">
        <button role="button" type="submit" class="btn btn-primary btn-ok col-3 col-5" id="submitForm">
            <?php eT("Delete User"); ?>
        </button>
    </div>
    <?= CHtml::endForm() ?>
    <script>
        window.LS.UserManagement.wireForm();
    </script>
</div>
