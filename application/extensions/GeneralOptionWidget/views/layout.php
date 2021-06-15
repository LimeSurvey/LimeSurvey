<div class="form-group">
    <div class="question-option-general-setting-block">
        <label class="form-label">
            <?= $this->generalOption->title; ?>
        </label>
        <i
            role="button"
            class="fa fa-question-circle text-success"
            onclick="jQuery('#general-setting-help-<?= $this->generalOption->name; ?>').slideToggle()"
            data-toggle="tooltip"
            title="<?= gT("See help"); ?>"
            ></i>
        <?= $content; ?>
        <div
            id="general-setting-help-<?= $this->generalOption->name; ?>"
            class="question-option-help well"
            >
            <?= $this->generalOption->formElement->help; ?>
        </div>
    </div>
</div>
