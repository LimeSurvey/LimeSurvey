<?php
    $this->renderPartial(
        '/admin/super/permissions/addGroupForm',
        array(
            'action' => array("surveysGroupsPermission/addUserGroup", 'id'=>$model->gsid),
            'oAddGroupList' => $oAddGroupList
        )
    );
?>
