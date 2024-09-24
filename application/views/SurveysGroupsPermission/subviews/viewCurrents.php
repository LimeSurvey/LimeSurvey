<div class="well">
    <p><?= gT("Users listed here can see groups in lists, and view group descriptions & settings. This is the minimal permission - you have to use the delete action to remove this permission.") ?></p>
    <p><?= gT("This survey group is shown for users with any permission to the survey group, user with any permission to one survey inside this group, or if this group was configured to be available."
        ) ?></p>
</div>
<?php if (!empty($oExistingUsers)) {
    $this->renderPartial('/SurveysGroupsPermission/subviews/currentUsersList',
        array(
            'model' => $model,
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
        )
    );
} ?>
<?php if ($model->hasPermission('permission', 'create')) : ?>
    <h2 class="pagetitle h3"><?php eT('Add permissions:'); ?></h2>
    <?php if (!empty($oAddUserList)) {
        $this->renderPartial('/SurveysGroupsPermission/subviews/addUserForm', array('model' => $model, 'oAddUserList' => $oAddUserList));
    } ?>
    <?php if (!empty($oAddGroupList)) {
        $this->renderPartial('/SurveysGroupsPermission/subviews/addGroupForm', array('model' => $model, 'oAddGroupList' => $oAddGroupList));
    } ?>
<?php endif; ?>
