<div class="panel panel-default question-option-general-container col-12" id="general-settings">
    <div class="panel-heading"> 
        <?= gT('General Settings'); ?>
        <button class="pull-right btn btn-default btn-xs" @click="collapsedMenu=true">
            <i class="fa fa-chevron-right" /></i>
        </button>
    </div>
    <div class="panel-body">
        <div class="list-group">
            <?php foreach ($generalSettings as $generalOption) : ?>
                <?php $this->widget(
                    'ext.GeneralOptionWidget.GeneralOptionWidget',
                    ['generalOption' => $generalOption]
                ); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
