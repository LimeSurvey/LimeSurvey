<div class="form-row">
    <div class="list-group-item question-option-general-setting-block">
        <i
            role="button"
            class="fa fa-question pull-right"
            onclick="jQuery('#general-setting-help-<?= $this->generalOption->name; ?>').slideToggle()"
            data-toggle="tooltip"
            title="<?= gT("See help"); ?>"
            ></i>
        <label class="form-label">
            <?= $this->generalOption->title; ?>
        </label>
        <?= $content; ?>
        <div
            id="general-setting-help-<?= $this->generalOption->name; ?>"
            class="question-option-help well"
            >
            <?= $this->generalOption->formElement->help; ?>
        </div>
    </div>
</div>
