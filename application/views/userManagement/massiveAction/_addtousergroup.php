<?php
    $aUsergoups = UserGroup::model()->findAll();
?>

<div class="modal-body selector--edit-usergroup-container">
    <div class="container-center form">
        <?php if ($aUsergoups) : ?>
            <div class="form-group">
                <label for="addtousergroup"><?= gT("Select user group to add users to") ?></label>
                <select class="form-control select post-value" name="addtousergroup" id="addtousergroup" required>
                    <?php foreach ($aUsergoups as $oUsergroup) {
                        echo "<option value='" . $oUsergroup->ugid . "'>" . $oUsergroup->name . "</option>";
                    } ?>
                </select>
            </div>
        <?php else : ?>
            <?php
            echo "<p>" . gT("No user groups found.") . "</p>";
            echo CHtml::link('<i class="fa fa-plus-circle text-success"></i> ' . gT('Add new user group'), array('userGroup/addGroup'), array('class' => 'btn btn-default'));
            ?>
        <?php endif; ?>
    </div>
</div>
