<div class="btn-group col-12" role="group" data-toggle="buttons">
    <?php foreach ($this->setting['aFormElementOptions']['options']['option'] as $option) : ?>
        <label class="btn btn-default">
            <input 
                type="radio" 
                name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
                value="<?= $option['value']; ?>"
            />
            <?= $option['text']; ?>
        </label>
    <?php endforeach; ?>
</div>
