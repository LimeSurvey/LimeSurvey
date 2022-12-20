<div class="accordion-item question-option-general-container col-12" id="general-settings">
    <h2 class="accordion-header" id="general-setting-heading">
        <button
            class="accordion-button selector--questionEdit-collapse"
            type="button"
            role="button"
            data-bs-toggle="collapse"
            data-bs-parent="#accordion"
            href="#collapse-question"
            aria-expanded="true"
            aria-controls="collapse-question"
        >
            <?= gT('General Settings'); ?>
        </button>
    </h2>
    <div
        id="collapse-question"
        class="accordion-collapse collapse show"
        role="tabpanel"
        data-bs-parent="#accordion"
        aria-labelledby="general-setting-heading"
    >
        <div class="accordion-body collapse show">
            <?php foreach ($generalSettings as $generalOption) : ?>
                <?php $this->widget(
                    'ext.GeneralOptionWidget.GeneralOptionWidget',
                    ['generalOption' => $generalOption]
                ); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
