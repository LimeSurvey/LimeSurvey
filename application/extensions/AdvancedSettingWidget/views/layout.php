    <div class="form-row">
<div class="col-lg-5">
    <div class="list-group-item question-option-general-setting-block">
        <i
            role="button"
            class="fa fa-question pull-right"
            onclick="jQuery('#general-setting-help-<?= $this->setting['name']; ?>').slideToggle()"
            data-toggle="tooltip"
            title="<?= gT("See help"); ?>"
            ></i>
        <label class="form-label" :for="elId">
            <?= $this->setting['title']; ?>
        </label>
        <?= $content; ?>
        <div
            id="general-setting-help-<?= $this->setting['name']; ?>"
            class="question-option-help well"
            >
            <?= $this->setting['formElementHelp']; ?>
        </div>
    </div>
</div>
</div>
