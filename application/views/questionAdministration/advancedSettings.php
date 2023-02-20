<div id="advanced-options-container">
<?php foreach ($advancedSettings as $category => $settings) : ?>
    <div class="accordion-item panel-advancedquestionsettings border-top-0 rounded-0" id="<?= CHtml::getIdByName($category); ?>">
        <h2 class="accordion-header" id="<?= CHtml::getIdByName($category); ?>-heading">
            <button
                class="selector--questionEdit-collapse accordion-button collapsed rounded-0"
                id="button-collapse-<?= CHtml::getIdByName($category); ?>"
                role="button"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse-<?= CHtml::getIdByName($category); ?>"
                data-bs-parent="#accordion"
                href="#collapse-<?= CHtml::getIdByName($category); ?>"
                aria-expanded="false"
                aria-controls="collapse-<?= CHtml::getIdByName($category); ?>"
            >
                <?= gT($category); ?>
            </button>
        </h2>
        <div
            id="collapse-<?= CHtml::getIdByName($category); ?>"
            class="accordion-collapse collapse"
            data-bs-parent="#accordion"
            role="tabpanel"
            aria-labelledby="<?= CHtml::getIdByName($category); ?>-heading"
        >
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
</div>
