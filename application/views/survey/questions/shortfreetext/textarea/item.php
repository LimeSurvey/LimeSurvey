<?php
/**
 * Shortfreetext, textarea style, item Html
 *
 * @var $freeTextId                 answer{$ia[1]}
 * @var $extraclass
 * @var $labelText                  gT('Your answer')
 * @var $name                       $ia[1]
 * @var $drows
 * @var $tiwidth
 * @var $checkconditionFunction      $checkconditionFunction.'(this.value, this.name, this.type)
 * @var $dispVal
 */
?>

<!-- Short free text, textarea item -->
<div class="question answer-item text-item <?php echo $extraclass; ?> form-horizontal short-free-text row">

    <div class='col-sm-<?php echo $col; ?>'>
        <!-- Label -->
        <label class='control-label sr-only' for='answer<?php echo $name; ?>' >
            <?php eT('Your answer'); ?>
        </label>
        <?php if ($prefix !== '' || $suffix !== ''): ?>
            <div class="input-group">
        <?php endif; ?>
            <!-- Prefix -->
            <?php if ($prefix !== ''): ?>
                <div class='ls-input-group-extra prefix-text prefix text-right'><?php echo $prefix; ?></div>
            <?php endif; ?>

            <textarea
                class="form-control textarea <?php echo $kpclass; ?>"
                name="<?php echo $name;?>"
                id="<?php echo $freeTextId;?>"
                rows="<?php echo $drows; ?>"
                cols="<?php echo $inputsize; ?>"
                <?php echo $maxlength; ?>
            ><?php echo $dispVal; ?></textarea>

            <!-- Suffix -->
            <?php if ($suffix !== ''): ?>
                <div class='ls-input-group-extra suffix-text suffix text-left'><?php echo $suffix; ?></div>
            <?php endif; ?>
        <?php if ($prefix !== '' || $suffix !== ''): ?>
            </div>
        <?php endif; ?>
    </div>
</div>
