<?php
/* @var $this PermissiontemplatesController */
/* @var $data Permissiontemplates */
?>

<div class="modal-header">
    <h5 class="modal-title" id="modalTitle-addedit"><?= sprintf(gT('Permission role %s'), CHtml::encode($oModel->name)); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="col-12 card-body">
                    <?= CHtml::encode($oModel->description) ?>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-4">
            <?= gT('Users assigned to this role') ?>
        </div>
        <div class="col-8">
            <ul class="list-group">
                <?php foreach ($oModel->connectedUserobjects as $oUser) {
                    echo sprintf('<li class="list-group-item">%s - %s (%s)</li>', $oUser->uid, $oUser->full_name,
                        $oUser->users_name);
                } ?>
            </ul>
        </div>
    </div>
</div>
<div class="modal-footer modal-footer-buttons">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
        <?php eT("Close"); ?>
    </button>
</div>
