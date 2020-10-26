    <h2 class="pagetitle h3"><?php eT('Current permissions:');?></h2>
    <?php if(Permission::model()->hasSurveyGroupPermission($model->primaryKey, 'permission', 'create')) : ?>
        <h3><?php eT('Add permissions:');?></h3>
        <?php if(!empty($oAddUserList)) {
            $this->renderPartial('surveysgroups/permission/addUserForm',array('model'=>$model,'oAddUserList'=>$oAddUserList));
        } ?>
        <?php if(!empty($oAddGroupList)) {
            $this->renderPartial('surveysgroups/permission/addGroupForm',array('model'=>$model,'oAddGroupList'=>$oAddGroupList));
        } ?>
    <?php endif; ?>
