<div class="form-row">
    <i class="fa fa-question pull-right"></i>
    <label class="form-label"><?= gT($this->generalOption->title); ?></label>
    <div class="inputtype--toggle-container">
        <input
            type="checkbox"
            name="<?= $this->generalOption->name; ?>"
            id="<?= $this->generalOption->name; ?>"
            />
    </div> 
    <div id="general-setting-help-<?= $this->generalOption->name; ?>" class="question-option-help well" /><?= $this->generalOption->formElement->help; ?></div>
</div>
