<!-- The button -->
<div id="trigger_<?=$this->widgetsJsName?>_button">
    <button
        type="button"
        class="btn btn-block <?= implode(" ", $this->buttonClasses) ?>"
        data-target="#selector__<?=$this->widgetsJsName?>-modal"
        data-toggle="modal"
        aria-haspopup="true"
        aria-expanded="false"
        <?php
        if ($this->survey_active) {
            echo 'disabled';
        }
        ?>
        >
        <?php if ($this->iconPosition === 'front') : ?>
            <i class="fa fa-folder-open"></i>&nbsp;&nbsp;
        <?php endif; ?>
        <span class="buttontext" id="selector__<?=$this->widgetsJsName?>--buttonText">
            <?= $this->currentSelected ?>
            <?php if (YII_DEBUG) :?>
                <em class="small">
                  <?= gT($this->debugKeyCheck)?> <?=$this->value?>
                </em>
            <?php  endif;?>
        </span>
        <?php if ($this->iconPosition === 'back') : ?>
            &nbsp;&nbsp;&nbsp;
            <i class="fa fa-folder-open"></i>           
        <?php endif; ?>
    </button>
</div>
