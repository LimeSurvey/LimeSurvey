<?php
    $this->renderPartial(
        '/admin/super/permissions/addUserForm',
        array(
            'action' => array("surveysGroupsPermission/addUser", 'id'=>$model->gsid),
            'oAddUserList' => $oAddUserList
        )
    );
?>
