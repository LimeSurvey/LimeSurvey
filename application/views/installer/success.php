<div class="form-horizontal">
    <legend><?=gT("Administrator credentials"); ?></legend>
    <div class="form-group">
        <label class="control-label  col-sm-4"><?=gT("Username"); ?></label>
        <div class="col-sm-8">
            <div class="form-control">
                <?=$user ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-4"><?=gT("Password"); ?></label>
        <div class="col-sm-8">
            <div class="form-control">
                <?=$pwd ?>
            </div>
        </div>
    </div>
    <div class="btn-group pull-right">
        <?=TbHtml::linkButton(gT("Administration"), ['url' => ['admin/index'], 'color' => TbHtml::BUTTON_COLOR_SUCCESS]); ?>
    </div>
</div>