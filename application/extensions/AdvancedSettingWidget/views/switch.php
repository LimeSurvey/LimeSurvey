<div class="inputtype--toggle-container">
    <div class="btn-group" role="group">
        <?php if ($this->setting['value'] == "1") : ?>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="1"
                checked
                />
            <label
                for="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                class="btn btn-outline-primary"
            >
                <?= gT('On'); ?>
            </label>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="0"
                />
            <label
                for="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                class="btn btn-outline-primary">
                <?= gT('Off'); ?>
            </label>
        <?php else : ?>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="1"
                />
            <label
                for="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_1"
                class="btn btn-outline-primary">
                <?= gT('On'); ?>
            </label>
            <input
                class="btn-check"
                type="radio"
                id="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                value="0"
                checked
                />
            <label
                for="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]_0"
                class="btn btn-outline-primary">
                <?= gT('Off'); ?>
            </label>
        <?php endif; ?>
    </div>
</div>
