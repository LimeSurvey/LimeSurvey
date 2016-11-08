<?php
/**
 * Numerical Html
 *
 * @var $extraclass
 * @var $id          $ia[1]
 * @var $prefix
 * @var $answertypeclass
 * @var $tiwidth
 * @var $fValue
 * @var $checkconditionFunction
 * @var $integeronly
 * @var $maxlength
 * @var $suffix
 */
?>
<!-- Numerical -->

<!-- answer -->
<?php if($withColumn): ?>
<div class='<?php echo $coreClass; ?> row'>
    <div class="<?php echo $extraclass; ?>">
<?php else: ?>
<div class='<?php echo $coreClass; ?> <?php echo $extraclass; ?>'>
<?php endif; ?>
    <label for='answer<?php echo $id;?>' class='control-label sr-only'>
            <?php eT('Your answer'); ?>
    </label>

    <?php if ($prefix !== '' || $suffix !== ''): ?>
        <div class="ls-input-group">
    <?php endif; ?>
    <!-- Prefix -->
    <?php if ($prefix !== ''): ?>
        <div class='ls-input-group-extra prefix-text prefix text-right'><?php echo $prefix; ?></div>
    <?php endif; ?>

    <input
        class='form-control <?php echo $answertypeclass; ?>'
        type="text"  <?php // Want to use HTML5 number type? Think again: Doesn't work as we want with locale: http://stackoverflow.com/questions/13412204/localization-of-input-type-number | Shnoulle 20161005 : but type=number is localized by default :) ?>
        name="<?php echo $id;?>"
        title="<?php echo eT('Only numbers may be entered in this field.');?>"
        id="answer<?php echo $id;?>"
        value="<?php echo $fValue;?>"
        <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
        <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
        data-number='1'
        data-integer='<?php echo $integeronly; ?>'
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
<!-- end of answer -->
