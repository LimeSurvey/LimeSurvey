    <div class="well">
        <p><?= gT("User listed here can see groups in lists and view descriptions and other settings. This is the minimal permission, you have to use delete action to remove this permission.") ?></p>
        <p><?= gT("This surveys group are shown in list for user with any permission on survey group, user with any permission on one survey inside this group or if this groups was set as available.") ?></p>
    </div>
    <?php if(!empty($oExistingUsers)) {
        $this->renderPartial('/SurveysGroupsPermission/subviews/currentUsersList',array(
            'model'=>$model,
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
        ));
    } ?>
    <?php if(Permission::model()->hasSurveyGroupPermission($model->primaryKey, 'permission', 'update')) : ?>
        <h2 class="pagetitle h3"><?php eT('Add permissions:');?></h2>
        <?php if(!empty($oAddUserList)) {
            $this->renderPartial('/SurveysGroupsPermission/subviews/addUserForm',array('model'=>$model,'oAddUserList'=>$oAddUserList));
        } ?>
        <?php if(!empty($oAddGroupList)) {
            $this->renderPartial('/SurveysGroupsPermission/subviews/addGroupForm',array('model'=>$model,'oAddGroupList'=>$oAddGroupList));
        } ?>
    <?php endif; ?>
