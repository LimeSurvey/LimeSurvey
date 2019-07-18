<?php
/* @var $this PermissiontemplatesController */
/* @var $data Permissiontemplates */
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle-addedit">
        <?=sprintf(gT('Permission role %s'), $oModel->name);?>
    </h4>
</div>
<div class="modal-body">
    <div class="container-center">
        <div class="row">
            <div class="col-xs-12 well">
                <?=$oModel->description?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-12">
                <?=gT('Connected users')?>
            </div>
            <div class="col-md-8 col-sm-12">
                <ul class="list-group">
                    <?php foreach( $oModel->connectedUserobjects as $oUser) {
                        echo sprintf('<li class="list-group-item">%s - %s (%s)</li>', $oUser->uid, $oUser->full_name, $oUser->users_name);
                    } ?>
                </ul>
            </div>
        </div>
    </div>
</div>