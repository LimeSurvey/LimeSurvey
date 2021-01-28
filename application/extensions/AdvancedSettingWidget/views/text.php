<div class="input-group col-12">
<?php /*
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['prefix'])) : ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['prefix']; ?>
        </div>
    <?php endif; ?>
 */ ?>

    <?php if (isset($this->setting['i18n']) && $this->setting['i18n'] == 1): ?>
        <?php foreach ($this->survey->allLanguages as $lang): ?>
            <div class="lang-hide lang-<?= $lang; ?>">
                <input
                    type="text"
                    class="form-control"
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>][<?= $lang; ?>]"
                    id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>][<?= $lang; ?>]"
                    value="<?= CHtml::encode($this->setting[$lang]['value']); ?>"
                />
            </div>
        <?php endforeach; ?>
    <?php else: ?>
      <input
          type="text"
          class="form-control"
          name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
          id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
          value="<?= CHtml::encode($this->setting['value']); ?>"
      />
    <?php endif; ?>

    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['suffix'])) : ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
