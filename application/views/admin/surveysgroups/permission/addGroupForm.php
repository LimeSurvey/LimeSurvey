<?php
    $this->renderPartial(
        'super/permissions/addGroupForm',
        array(
            'action' => array("admin/surveysgroups/sa/permissionsAddUserGroup", 'id'=>$model->gsid),
            'oAddGroupList' => $oAddGroupList
        )
    );
?>
