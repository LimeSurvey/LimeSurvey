<?php
$labelAttr = in_array($this->generalOption->inputType, GeneralOptionWidget::SINGLEINPUTTYPE) ? 'for="' : 'id="label-';
$labelAttr .= CHtml::getIdByName($this->generalOption->name) . '"';
?>
<div class="mb-3">
    <div class="question-option-general-setting-block">
        <div class="col-12">
            <label <?= $labelAttr; ?>>
                <?= $this->generalOption->title; ?>
            </label>
        <?php if ($this->generalOption->formElement->help): ?>
            <a
                role="button"
                data-bs-toggle="collapse"
                href="#help-<?= CHtml::getIdByName($this->generalOption->name); ?>"
            ><i
                class="ri-information-fill"
                data-bs-toggle="tooltip"
                title="<?= CHtml::encode(strip_tags((string) $this->generalOption->formElement->help)) ?>"
            > </i><span class="visually-hidden"><?= gT("Show help"); ?></span> </a>
            <div class="help-block collapse" id="help-<?= CHtml::getIdByName($this->generalOption->name); ?>" aria-expanded="false"><?= $this->generalOption->formElement->help; ?></div>
        <?php endif; ?>
        </div>
        <?= $content; ?>
    </div>
</div>
