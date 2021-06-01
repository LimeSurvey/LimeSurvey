<div class="panel panel-default question-option-general-container col-12" id="general-settings">
    <div class="panel-heading" id="general-setting-heading">
        <a class="panel-title h4 selector--questionEdit-collapse" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-question" aria-expanded="true" aria-controls="collapse-question">
            <?= gT('General Settings'); ?>
        </a>
    </div>
    <div id="collapse-question" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="general-setting-heading">
        <div class="panel-body">
            <?php foreach ($generalSettings as $generalOption) : ?>
                <?php $this->widget(
                    'ext.GeneralOptionWidget.GeneralOptionWidget',
                    ['generalOption' => $generalOption]
                ); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>