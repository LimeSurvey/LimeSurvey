<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('User').' '.gT('detail')]
);
?>

<div class="modal-body">
    <div class="container-center list-group">
        <div class="row list-group-item">
            <div class="col-sm-4"><?=gT('User groups')?>:</div>
            <div class="col-sm-8"><?=join(', ',$usergroups)?></div>
        </div>
        <div class="row list-group-item">
            <div class="col-sm-4"><?=gT('Created by')?>:</div>
            <div class="col-sm-8"><?=$oUser->parentUser['full_name']?></div>
        </div>
        <div class="row list-group-item">
            <div class="col-sm-4"><?=gT('Survey created')?>:</div>
            <div class="col-sm-8"><?=$oUser->surveysCreated?></div>
        </div>
        <div class="row list-group-item">
            <div class="col-sm-4"><?=gT('Last login')?>:</div>
            <div class="col-sm-8"><?=$oUser->lastloginFormatted?></div>
        </div>
        <div class="row ls-space margin top-15 bottom-15">
        </div>

    </div>
</div>

<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-default">
        <?=gT('Close')?></button>
</div>
