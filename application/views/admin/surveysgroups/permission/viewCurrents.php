    <div class="well">
        <p><?= gT("User listed here can see groups in lists and view descriptions and other settings. It's the minial right, youhave to use delete action to remve this right.") ?></p>
        <p><?= gT("This surveys group are shown in list for user with rigths on survey group, user with rigths an a survey inside this group and if this groups was set as available.") ?></p>
    </div>
    <?php if(!empty($oExistingUsers)) {
        $this->renderPartial('surveysgroups/permission/currentUsersList',array(
            'model'=>$model,
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
        ));
    } ?>
    <?php if(Permission::model()->hasSurveyGroupPermission($model->primaryKey, 'permission', 'update')) : ?>
        <h2 class="pagetitle h3"><?php eT('Add permissions:');?></h2>
        <?php if(!empty($oAddUserList)) {
            $this->renderPartial('surveysgroups/permission/addUserForm',array('model'=>$model,'oAddUserList'=>$oAddUserList));
        } ?>
        <?php if(!empty($oAddGroupList)) {
            $this->renderPartial('surveysgroups/permission/addGroupForm',array('model'=>$model,'oAddGroupList'=>$oAddGroupList));
        } ?>
    <?php endif; ?>
