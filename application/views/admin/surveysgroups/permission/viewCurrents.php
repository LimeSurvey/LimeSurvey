    <?php if(!empty($oExistingUsers)) {
        $this->renderPartial('surveysgroups/permission/currentUsersList',array(
            'model'=>$model,
            'aDefinitionPermissions' => $aDefinitionPermissions,
            'oExistingUsers' => $oExistingUsers,
            'aCurrentsUserRights' => $aCurrentsUserRights,
        ));
    } ?>
    <?php if(Permission::model()->hasSurveyGroupPermission($model->primaryKey, 'permission', 'create')) : ?>
        <h2 class="pagetitle h3"><?php eT('Add permissions:');?></h2>
        <?php if(!empty($oAddUserList)) {
            $this->renderPartial('surveysgroups/permission/addUserForm',array('model'=>$model,'oAddUserList'=>$oAddUserList));
        } ?>
        <?php if(!empty($oAddGroupList)) {
            $this->renderPartial('surveysgroups/permission/addGroupForm',array('model'=>$model,'oAddGroupList'=>$oAddGroupList));
        } ?>
    <?php endif; ?>
