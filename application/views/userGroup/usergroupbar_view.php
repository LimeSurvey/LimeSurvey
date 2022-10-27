<?php
/** @var AdminController $this */
/** @var UserGroup $userGroup */
?>
<?php
App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>
<!-- User Group Bar -->
<div class='menubar surveybar' id="usergroupbar">
    <div class="container-fluid">
        <div class='row'>
            <!-- Left side -->
            <div class="col">

                <!-- Add -->
                <?php if (Permission::model()->hasGlobalPermission('usergroups', 'create') && isset($usergroupbar['returnbutton']['url'])) : ?>
                    <a class="btn btn-outline-secondary"
                       href="<?php echo $this->createUrl("userGroup/addGroup"); ?>"
                       title="<?php eT('Add a new user group'); ?>">
                        <span class="ri-add-circle-fill text-success"></span>
                        <?php eT("Add user group"); ?>
                    </a>
                <?php endif; ?>

                <!-- Mail to all Members -->
                <?php if (isset($usergroupbar['edit'])) : ?>
                    <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl("userGroup/mailToAllUsersInGroup/ugid/" . $userGroup->ugid); ?>">
                        <span class="icon-invite text-success"></span>
                        <?php eT("Mail to all Members"); ?>
                    </a>
                <?php endif; ?>

                <!-- Edit current user group -->
                <?php if (isset($usergroupbar['edit']) && (Yii::app()->session['loginID'] == $userGroup->owner_id || Permission::model()->hasGlobalPermission('superadmin',
                            'read'))) : ?>
                    <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl("userGroup/edit/ugid/" . $userGroup->ugid); ?>">
                        <span class="ri-pencil-fill text-success"></span>
                        <?php eT("Edit current user group"); ?>
                    </a>
                <?php endif; ?>

                <!-- Delete current user group -->
                <?php if (isset($usergroupbar['edit']) && (Yii::app()->session['loginID'] == $userGroup->owner_id || Permission::model()->hasGlobalPermission('superadmin',
                            'read'))) : ?>
                    <a class="btn btn-danger"
                       href='#'
                       onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?",
                           "js"); ?>")) { <?php echo convertGETtoPOST($this->createUrl('userGroup/deleteGroup?ugid=' . $userGroup->ugid)); ?>}'>
                        <span class="ri-delete-bin-fill"></span>
                        <?php eT("Delete current user group"); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right side -->
            <div class="col-md-auto text-end">

                <!-- Close -->
            <?php if (isset($usergroupbar['closebutton']['url'])) : ?>
                <a class="btn btn-danger"
                   href="<?php echo $usergroupbar['closebutton']['url']; ?>">
                    <span class="ri-close-fill"></span>
                    <?php eT("Close"); ?>
                </a>
            <?php endif; ?>

                <!-- Back -->
            <?php if (isset($usergroupbar['returnbutton']['url'])) : ?>
                <a class="btn btn-outline-secondary"
                   style="margin-left:5px;"
                   href="<?php echo $this->createUrl($usergroupbar['returnbutton']['url']); ?>">
                    <span class="ri-rewind-fill"></span>
                        &nbsp;&nbsp;
                        <?php echo $usergroupbar['returnbutton']['text']; ?>
                </a>
            <?php endif; ?>

                <!-- Reset -->
            <?php if (isset($usergroupbar['resetbutton']['form'])) : ?>
                <button
                    class="btn btn-warning"
                    type="reset"
                    role="button"
                    form="<?php echo $usergroupbar['resetbutton']['form'] ?>"
                    id="reset-form-button"
                    		value="Reset">
                        <span class="ri-refresh-line"></span>
                        <?php echo $usergroupbar['resetbutton']['text']; ?>
                    </button>
                <?php endif; ?>

                <!-- Save -->
            <?php if (isset($usergroupbar['savebutton']['form'])) : ?>
                <a class="btn btn-primary"
                   type="submit"
                   href="#"
                   id="save-form-button"
                   data-form-id="<?php echo $usergroupbar['savebutton']['form']; ?>">
                    <i class="fa fa-save"></i>
                    <?php echo $usergroupbar['savebutton']['text']; ?>
                </a>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
