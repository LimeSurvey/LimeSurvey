<div class="form-group">
    <div class="question-option-general-setting-block">
        <?php if (in_array($this->generalOption->inputType, GeneralOptionWidget::SINGLEINPUTTYPE)): ?>
            <label class="form-label" for="<?= CHtml::getIdByName($this->generalOption->name); ?>">
                <?= $this->generalOption->title; ?>
            </label>
        <?php else: ?>
            <strong class="form-label" id="label-<?= CHtml::getIdByName($this->generalOption->name); ?>">
                <?= $this->generalOption->title; ?>
            </strong>
        <?php endif; ?>
        <?php if ($this->generalOption->formElement->help): ?>
            <a
                role="button"
                data-toggle="collapse"
                href="#help-<?= CHtml::getIdByName($this->generalOption->name); ?>"
            ><i
                class="fa fa-question-circle text-info"
                data-toggle="tooltip"
                title="<?= CHtml::encode(strip_tags($this->generalOption->formElement->help)) ?>"
            > </i><span class="sr-only"><?= gT("Show help"); ?></span> </a>
            <div class="help-block collapse" id="help-<?= CHtml::getIdByName($this->generalOption->name); ?>" aria-expanded="false"><?= $this->generalOption->formElement->help; ?></div>
        <?php endif; ?>
        <?= $content; ?>
    </div>
</div>
