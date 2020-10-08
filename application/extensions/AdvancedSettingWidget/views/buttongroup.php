<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option): ?>
        <?php if ($this->setting['formElementValue'] == $option['value']): ?>
            <label class="btn btn-default active">
                <input 
                    type="radio" 
                    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="<?= $option['value']; ?>"
                    checked
                />
                <?= $option['text']; ?>
            </label>
        <?php else: ?>
            <label class="btn btn-default">
                <input 
                    type="radio" 
                    name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                    value="<?= $option['value']; ?>"
                />
                <?= $option['text']; ?>
            </label>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
