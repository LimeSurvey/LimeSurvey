<div class="form-group <?= isset($this->setting['hidden']) && $this->setting['hidden'] === '1' ? 'hidden' : '' ?>">
    <div class="question-option-general-setting-block">
        <label class="form-label" :for="elId">
            <?= gT($this->setting['caption']) ?>
        </label>
        <!-- TODO: Object method $setting->isLocalized(). -->
        <?php if (isset($this->setting['i18n']) && $this->setting['i18n'] == 1): ?>
            <i
                class="fa fa-globe"
                data-toggle="tooltip"
                title="<?= gT("This setting is localized") ?>"
            ></i>
        <?php endif; ?>
        <i
            role="button"
            class="fa fa-question-circle text-success"
            onclick="jQuery('#general-setting-help-<?= $this->setting['name'] ?>').slideToggle()"
            data-toggle="tooltip"
            title="<?= gT("See help") ?>"
        ></i>
        <?= $content ?>
        <div
            id="general-setting-help-<?= $this->setting['name'] ?>"
            class="question-option-help well"
            >
            <?= gT($this->setting['help']) ?>
        </div>
    </div>
</div>
