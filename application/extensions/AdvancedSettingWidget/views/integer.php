<div class="input-group col-12">
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['prefix'])): ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['prefix']; ?>
        </div>
    <?php endif; ?>
        <!--
        max=""
        min=""
        -->
    <input 
        type="number" 
        class="form-control" 
        name="<?= $this->setting['name']; ?>"
        id="<?= $this->setting['name']; ?>"
    />
    <?php if (isset($this->setting['aFormElementOptions']['inputGroup']['suffix'])): ?>
        <div class="input-group-addon">
            <?= $this->setting['aFormElementOptions']['inputGroup']['suffix']; ?>
        </div>
    <?php endif; ?>
</div>
