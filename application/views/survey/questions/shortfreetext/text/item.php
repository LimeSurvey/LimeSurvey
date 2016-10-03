<?php
/**
 * Shortfreetext, input text style, item Html
 *
 * $extraclass
 * $name        $ia[1]
 * $prefix
 * $suffix
 * $kpclass
 * $tiwidth
 * $dispVal
 * $maxlength
 * $checkconditionFunction
 */
?>

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

            <!-- Input -->
            <input
                class="form-control text <?php echo $kpclass;?>"
                type="text"
                name="<?php echo $name; ?>"
                id="answer<?php echo $name;?>"
                value="<?php echo $dispVal; ?>"
                <?php echo $maxlength; ?>
                size="<?php echo $inputsize; ?>"
            />

            <!-- Suffix -->
            <?php if ($suffix !== ''): ?>
                <div class='ls-input-group-extra suffix-text suffix text-left'><?php echo $suffix; ?></div>
            <?php endif; ?>
        <?php if ($prefix !== '' || $suffix !== ''): ?>
            </div>
        <?php endif; ?>
    </div>
</div>
