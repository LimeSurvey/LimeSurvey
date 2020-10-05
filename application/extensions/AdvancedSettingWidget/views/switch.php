<div class="inputtype--toggle-container">
    <div class="btn-group" role="group" data-toggle="buttons">
        <label class="btn btn-default">
            <input
                type="radio"
                name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                value="Y"
                />
            <?= gT('On'); ?>
        </label>
        <label class="btn btn-default">
            <input
                type="radio"
                name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                value="N"
                />
            <?= gT('Off'); ?>
        </label>
    </div>
</div>
