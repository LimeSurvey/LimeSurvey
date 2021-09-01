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
    <div class='row'>
        <!-- Left side -->
        <div class="col-lg-6 col-sm-8">

            <!-- Add -->
            <?php if (Permission::model()->hasGlobalPermission('usergroups', 'create') && isset($usergroupbar['returnbutton']['url'])) :?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("userGroup/addGroup"); ?>" role="button" title="<?php eT('Add a new user group'); ?>">
                    <span class="icon-add text-success"></span>
                    <?php eT("Add user group"); ?>
                </a>
            <?php endif; ?>

            <!-- Mail to all Members -->
            <?php if (isset($usergroupbar['edit'])) : ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("userGroup/mailToAllUsersInGroup/ugid/" . $userGroup->ugid); ?>" role="button">
                    <span class="icon-invite text-success"></span>
                    <?php eT("Mail to all Members"); ?>
                </a>
            <?php endif;?>

            <!-- Edit current user group -->
            <?php if (isset($usergroupbar['edit']) &&  (Yii::app()->session['loginID'] == $userGroup->owner_id || Permission::model()->hasGlobalPermission('superadmin', 'read'))) :?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("userGroup/edit/ugid/" . $userGroup->ugid); ?>" role="button">
                    <span class="fa fa-pencil text-success"></span>
                    <?php eT("Edit current user group"); ?>
                </a>
            <?php endif;?>

            <!-- Delete current user group -->
            <?php if (isset($usergroupbar['edit']) &&  (Yii::app()->session['loginID'] == $userGroup->owner_id || Permission::model()->hasGlobalPermission('superadmin', 'read'))) :?>
                <a class="btn btn-default" href='#' onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")) { <?php echo convertGETtoPOST($this->createUrl('userGroup/deleteGroup?ugid=' . $userGroup->ugid)); ?>}'>
                    <span class="fa fa-trash text-success"></span>
                    <?php eT("Delete current user group"); ?>
                </a>
            <?php endif;?>
        </div>

        <!-- Right side -->
        <div class="col-lg-6 col-sm-4 text-right">

            <!-- Close -->
            <?php if (isset($usergroupbar['closebutton']['url'])) :?>
                <a class="btn btn-danger" href="<?php echo $usergroupbar['closebutton']['url']; ?>" role="button">
                    <span class="fa fa-close" ></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <!-- Back -->
            <?php if (isset($usergroupbar['returnbutton']['url'])) :?>
                <a class="btn btn-default" style="margin-left:5px;" href="<?php echo $this->createUrl($usergroupbar['returnbutton']['url']); ?>" role="button">
                    <span class="fa fa-backward" ></span>
                    &nbsp;&nbsp;
                    <?php echo $usergroupbar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>

            <!-- Reset -->
            <?php if (isset($usergroupbar['resetbutton']['form'])) :?>
                <button class="btn btn-warning" type="reset" role="button" form="<?php echo $usergroupbar['resetbutton']['form'] ?>" id="reset-form-button" value="Reset">
                    <span class="fa fa-refresh"></span>
                    <?php echo $usergroupbar['resetbutton']['text']; ?>
                </button>
            <?php endif; ?>

            <!-- Save -->
            <?php if (isset($usergroupbar['savebutton']['form'])) :?>
                <a class="btn btn-primary" type="submit" href="#" role="button" id="save-form-button" data-form-id="<?php echo $usergroupbar['savebutton']['form']; ?>">
                    <span class="fa fa-envelope" ></span>
                    <?php echo $usergroupbar['savebutton']['text'];?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
