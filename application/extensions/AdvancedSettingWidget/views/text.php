<div class="input-group col-12">
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['prefix'])) : ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['prefix']; ?>
        </div>
    <?php endif; ?>
    <input
        type="text"
        class="form-control"
        name="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
        id="advancedSettings[<?= strtolower($this->setting['aFormElementOptions']['category']); ?>][<?= $this->setting['name']; ?>]"
        value="<?= $this->setting['formElementValue']; ?>"
    />
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['suffix'])) : ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
