<?php
$aUsergoups = UserGroup::model()->findAll();
?>

<div class="selector--edit-usergroup-container">
    <div class="form">
        <?php if ($aUsergoups) : ?>
            <div class="mb-3">
                <label for="addtousergroup"><?= gT("Select user group to add users to") ?></label>
                <select class="form-select select post-value" name="addtousergroup" id="addtousergroup" required>
                    <?php foreach ($aUsergoups as $oUsergroup) {
                        if (
                            Permission::model()->hasGlobalPermission('usergroups', 'update')
                            || $oUsergroup->requestEditGroup($oUsergroup->ugid, Yii::app()->session['loginID']) 
                        ) {
                            echo "<option value='" . $oUsergroup->ugid . "'>" . $oUsergroup->name . "</option>";
                        }
                    } ?>
                </select>
            </div>
        <?php else : ?>
            <?php
            echo "<p>" . gT("No user groups found.") . "</p>";
            echo CHtml::link('<i class="ri-add-circle-fill"></i> ' . gT('Add new user group'), array('userGroup/addGroup'), array('class' => 'btn btn-outline-secondary'));
            ?>
        <?php endif; ?>
    </div>
</div>
