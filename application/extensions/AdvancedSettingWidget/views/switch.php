<div class="inputtype--toggle-container">
    <div class="btn-group" role="group" data-toggle="buttons">
        <?php if ($this->setting['value'] == "1") : ?>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="1"
                    checked
                    />
                <?= gT('On'); ?>
            </label>
            <label class="btn btn-default">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="0"
                    />
                <?= gT('Off'); ?>
            </label>
        <?php else : ?>
            <label class="btn btn-default">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="1"
                    />
                <?= gT('On'); ?>
            </label>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="0"
                    checked
                    />
                <?= gT('Off'); ?>
            </label>
        <?php endif; ?>
    </div>
</div>
