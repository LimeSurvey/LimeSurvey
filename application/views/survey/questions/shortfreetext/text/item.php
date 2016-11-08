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

<?php if($withColumn): ?>
<div class='<?php echo $coreClass; ?> row'>
    <div class="<?php echo $extraclass; ?>">
<?php else: ?>
<div class='<?php echo $coreClass; ?> <?php echo $extraclass; ?>'>
<?php endif; ?>
    <!-- Label -->
    <label class='control-label sr-only' for='answer<?php echo $name; ?>' >
        <?php eT('Your answer'); ?>
    </label>
    <?php if ($prefix !== '' || $suffix !== ''): ?>
        <div class="ls-input-group">
    <?php endif; ?>
        <!-- Prefix -->
        <?php if ($prefix !== ''): ?>
            <div class='ls-input-group-extra prefix-text prefix text-right'><?php echo $prefix; ?></div>
        <?php endif; ?>

        <!-- Input -->
        <input
            class="form-control <?php echo $kpclass;?>"
            type="text"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name;?>"
            value="<?php echo $dispVal; ?>"
            <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
            <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
        />

        <!-- Suffix -->
        <?php if ($suffix !== ''): ?>
            <div class='ls-input-group-extra suffix-text suffix text-left'><?php echo $suffix; ?></div>
        <?php endif; ?>
    <?php if ($prefix !== '' || $suffix !== ''): ?>
        </div>
    <?php endif; ?>
<?php if($withColumn): ?>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>
