<div class="form-row">
    <i class="fa fa-question pull-right"></i>
    <label class="form-label">
        <?= $this->generalOption->title; ?>
    </label>
    <div class="input-group col-12">
        <div class="input-group-addon">
            <?= $this->generalOption->formElement->options['inputGroup']['prefix']; ?>
        </div>
        <input
            type="text"
            class="form-control"
            name="<?= $this->generalOption->name; ?>" 
            id="<?= $this->generalOption->name; ?>"
        />
        <?php if (isset($this->generalOption->formElement->options['inputGroup']['suffix'])): ?>
            <div class="input-group-addon">
                <?= $this->generalOption->formElement->options['inputGroup']['suffix']; ?>
            </div>
        <?php endif; ?>
    </div>
    <div id="general-setting-help-<?= $this->generalOption->name; ?>" class="question-option-help well" /><?= $this->generalOption->formElement->help; ?></div>
</div>
