<div class="btn-group col-12" role="group" data-toggle="buttons">
    <label class="btn btn-default">
        <input
            type="radio"
            name="question[<?= $this->generalOption->name; ?>]"
            value="Y"
            />
        <?= gT('On'); ?>
    </label>
    <label class="btn btn-default">
        <input
            type="radio"
            name="question[<?= $this->generalOption->name; ?>]"
            value="N"
            />
        <?= gT('Off'); ?>
    </label>
</div> 
