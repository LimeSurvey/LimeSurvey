<div class="inputtype--toggle-container">
    <div class="btn-group" role="group" data-toggle="buttons">
        <?php if ($this->setting['formElementValue'] == 'Y') : ?>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="Y"
                    checked
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
        <?php else : ?>
            <label class="btn btn-default">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="Y"
                    />
                <?= gT('On'); ?>
            </label>
            <label class="btn btn-default active">
                <input
                    type="radio"
                    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="N"
                    checked
                    />
                <?= gT('Off'); ?>
            </label>
        <?php endif; ?>
    </div>
</div>
