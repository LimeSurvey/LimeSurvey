<div class="inputtype--toggle-container">
    <div class="btn-group" role="group"
    aria-labelledby="label-<?= CHtml::getIdByName($inputBaseName); ?>"
    <?= ($this->setting['help']) ? 'aria-describedby="help-' . CHtml::getIdByName($inputBaseName) . '"' : "" ?>
    >
        <?php if ($this->setting['value'] == "1") : ?>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                name="<?= $inputBaseName ?>"
                value="1"
                checked
                />
            <label
                for="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                class="btn btn-outline-secondary"
            >
                <?= gT('On'); ?>
            </label>
            <input
                class="btn-check"
                type="radio"
                name="<?= $inputBaseName ?>"
                id="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                value="0"
                />
            <label
                for="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                class="btn btn-outline-secondary">
                <?= gT('Off'); ?>
            </label>
        <?php else : ?>
            <input
                class="btn-check"
                type="radio"
                name="<?= $inputBaseName ?>"
                id="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                value="1"
                />
            <label
                for="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                class="btn btn-outline-secondary">
                <?= gT('On'); ?>
            </label>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                name="<?= $inputBaseName ?>"
                value="0"
                checked
                />
            <label
                for="advancedSettings[<?= strtolower((string) $this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                class="btn btn-outline-secondary">
                <?= gT('Off'); ?>
            </label>
        <?php endif; ?>
    </div>
</div>
