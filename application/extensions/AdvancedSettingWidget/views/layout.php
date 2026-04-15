<?php
$labelAttr = in_array($this->setting['inputtype'], AdvancedSettingWidget::SINGLEINPUTTYPE) && !$this->setting['i18n'] ? 'for="' : 'id="label-';
$labelAttr .= CHtml::getIdByName($inputBaseName) . '"';
?>

<div class="mb-3 <?= $this->setting['hidden'] ? 'd-none' : '' ?>">
    <div class="question-option-general-setting-block">
        <div class="col-12">
            <label <?= $labelAttr ?>>
                <?= gT($this->setting['caption']) ?>
            </label>
        <!-- TODO: Object method $setting->isLocalized(). -->
        <?php if ($this->setting['i18n']): ?>
            <i
                class="ri-earth-fil"
                data-bs-toggle="tooltip"
                title="<?= gT("This setting is localized") ?>"
            ></i>
        <?php endif; ?>
        <?php if ($this->setting['help']): ?>
            <a
                role="button"
                data-bs-toggle="collapse"
                href="#help-<?= CHtml::getIdByName($inputBaseName); ?>"
            ><i
                class="ri-information-fill"
                data-bs-toggle="tooltip"
                data-bs-html="true"
                title="<?= $this->setting['help'] ?>"
            > </i><span class="visually-hidden"><?= gT("Show help"); ?></span> </a>
            <div class="help-block collapse" id="help-<?= CHtml::getIdByName($inputBaseName); ?>" aria-expanded="false"><?= $this->setting['help']; ?></div>
        <?php endif; ?>
        </div>
        <?= $content ?>
    </div>
</div>
<script>
function getNames(){
    return 'testing';
}
</script>
