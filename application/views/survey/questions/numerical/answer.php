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
<p class='question answer-item text-item numeric-item <?php echo $extraclass;?>'>
    <label for='answer<?php echo $id;?>' class='hide label'>
            <?php eT('Your answer'); ?>
    </label>

    <?php echo $prefix; ?>

    <input
        class='form-control text <?php echo $answertypeclass; ?>'
        type="text"  <?php // Want to use HTML5 number type? Think again: Doesn't work as we want with locale: http://stackoverflow.com/questions/13412204/localization-of-input-type-number ?>
        size="<?php echo $tiwidth;?>"
        name="<?php echo $id;?>"
        title="<?php echo eT('Only numbers may be entered in this field.');?>"
        id="answer<?php echo $id;?>"
        value="<?php echo $fValue;?>"
        onkeyup="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type,'onchange', <?php echo $integeronly; ?>);"
        <?php echo $maxlength; ?>
        />
        <?php echo $suffix;?>
</p>
<!-- end of answer -->
