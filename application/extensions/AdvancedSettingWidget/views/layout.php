    <div class="form-row">
<div class="col-lg-5">
    <div class="list-group-item question-option-general-setting-block">
        <i
            role="button"
            class="fa fa-question pull-right"
            onclick="jQuery('#general-setting-help-<?= $this->setting['name']; ?>').slideToggle()"
            data-toggle="tooltip"
            title="<?= gT("See help"); ?>"
        ></i>
        <!-- TODO: Object method $setting->isLocalized(). -->
        <?php if (isset($this->setting['i18n']) && $this->setting['i18n'] == 1): ?>
            <i
                class="fa fa-globe pull-right"
                data-toggle="tooltip"
                title="<?= gT("This setting is localized"); ?>"
            ></i>
        <?php endif; ?>
        <label class="form-label" :for="elId">
            <?= gT($this->setting['caption']); ?>
        </label>
        <?= $content; ?>
        <div
            id="general-setting-help-<?= $this->setting['name']; ?>"
            class="question-option-help well"
            >
            <?= gT($this->setting['help']); ?>
        </div>
    </div>
</div>
</div>
