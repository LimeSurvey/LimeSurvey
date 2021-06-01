<div id="advanced-options-container" class="custom custom-margin top-5">
    <?php foreach ($advancedSettings as $category => $settings) : ?>
        <div class="panel panel-default panel-advancedquestionsettings col-12" id="<?= CHtml::getIdByName($category); ?>">
            <div class="panel-heading" id="<?= CHtml::getIdByName($category); ?>-heading">
                <a class="panel-title h4 selector--questionEdit-collapse" id="button-collapse-<?= CHtml::getIdByName($category); ?>" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?= CHtml::getIdByName($category); ?>" aria-expanded="false" aria-controls="collapse-<?= CHtml::getIdByName($category); ?>">
                    <?= gT($category); ?>
                </a>
            </div>
            <div id="collapse-<?= CHtml::getIdByName($category); ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?= CHtml::getIdByName($category); ?>-heading">
                <div class="panel-body">
                    <?php foreach ($settings as $setting) : ?>
                        <?php $this->widget(
                            'ext.AdvancedSettingWidget.AdvancedSettingWidget',
                            ['setting' => $setting, 'survey' => $oSurvey]
                        ); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>