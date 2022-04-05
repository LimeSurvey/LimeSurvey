    <?php foreach ($advancedSettings as $category => $settings) : ?>
        <div class="accordion-item panel-advancedquestionsettings col-12" id="<?= CHtml::getIdByName($category); ?>">
            <h2 class="accordion-header bg-primary" id="<?= CHtml::getIdByName($category); ?>-heading">
                <button
                    class="selector--questionEdit-collapse accordion-button"
                    id="button-collapse-<?= CHtml::getIdByName($category); ?>"
                    role="button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target=""
                    data-parent="#accordion"
                    href="#collapse-<?= CHtml::getIdByName($category); ?>"
                    aria-expanded="false"
                    aria-controls="collapse-<?= CHtml::getIdByName($category); ?>"
                >
                    <?= gT($category); ?>
                </button>
            </h2>
            <div id="collapse-<?= CHtml::getIdByName($category); ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?= CHtml::getIdByName($category); ?>-heading">
                <div class="accordion-body">
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
