<?php
    $this->renderPartial(
        'super/permissions/addUserForm',
        array(
            'action' => array("admin/surveysgroups/sa/permissionsAddUser", 'id'=>$model->gsid),
            'oAddUserList' => $oAddUserList
        )
    );
?>
