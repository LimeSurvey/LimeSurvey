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
    <?php if ($prefix !== '' || $suffix !== ''): ?>
        <div class="ls-input-group">
    <?php endif; ?>
    <!-- Prefix -->
    <?php if ($prefix !== ''): ?>
        <div class='ls-input-group-extra prefix-text prefix'><?php echo $prefix; ?></div>
    <?php endif; ?>
    <?php
    // Want to use HTML5 number type? Think again: Doesn't work as we want with locale: http://stackoverflow.com/questions/13412204/localization-of-input-type-number
    // type=number is localized by default : broke API, (disable survey settings, but surely better)
    echo \CHtml::textField($id,$fValue,array(
        'id' => "answer{$id}",
        'class' => "form-control {$answertypeclass}",
        'title' => gT('Only numbers may be entered in this field.'),
        'size' => ($inputsize ? $inputsize : null),
        'maxlength' => ($maxlength ? $maxlength : null),
        'data-number' => 1,
        'data-integer' => $integeronly,
        'aria-labelledby' => "ls-question-text-{$basename}"
    ));
    ?>
    <!-- Suffix -->
    <?php if ($suffix !== ''): ?>
        <div class='ls-input-group-extra suffix-text suffix'><?php echo $suffix; ?></div>
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
