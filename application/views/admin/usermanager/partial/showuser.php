<div class="modal-header">
    <?=gT('User').' '.gT('detail')?>
</div>
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
        <div class="row ls-space margin top-35">
            <button id="exitForm" class="btn btn-default">
                <?=gT('Close')?></button>
        </div>
    </div>
</div>
