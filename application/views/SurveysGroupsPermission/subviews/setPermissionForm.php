<?php
    echo CHtml::beginForm(
    array( "surveysGroupsPermission/save", 'id'=>$model->gsid),
    'post',
    array('class'=>'setPermissionsForm', 'id'=> 'permissionsSave')
); ?>
    <h2 class="pagetitle h3"><?php if($type == 'user') {
        printf(gT("Set permission for user: %s"),"<em>".CHtml::encode($oUser->users_name)."</em>");
    }else {
        printf(gT("Set permission for user group: %s"),"<em>".CHtml::encode($oUserGroup->name)."</em>");
    } ?></h2>
    <?php $this->widget(
        'ext.UserPermissionsWidget.UserPermissionsWidget',
        ['aPermissions' => $aPermissions]
    ); ?>
    <!-- Hidden inputs -->
    <?php
        if($type == 'user') {
            echo CHtml::hiddenField('uid',$oUser->uid);
        } else {
            echo CHtml::hiddenField('ugid',$oUserGroup->ugid);
            echo CHtml::hiddenField('type','group');
        }
        echo CHtml::htmlButton(
            gT("Save"),
            array(
                'type' => 'submit',
                'name' => 'save',
                'value' => 'save',
                'class' => 'd-none'
            )
        );
    ?>
</form>
