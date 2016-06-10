<?php
/**
 * Lon free text question, item Html
 *
 * @var $extraclass
 * @var $name
 * @var $drows
 * @var $tiwidth
 * @var $maxlength
 * @var $checkconditionFunction
 * @var $dispVal
 */
?>
<!-- Long Free Text -->

<!-- answer -->
<p class='question answer-item text-item <?php echo $extraclass; ?>'>
    <label for='answer<?php echo $name; ?>' class='hide label'>
        <?php eT('Your answer'); ?>
    </label>

<textarea
    class="form-control textarea <?php echo $kpclass; ?>"
    name="<?php echo $name; ?>"
    id="answer<?php echo $name; ?>"
    rows="<?php echo $drows; ?>"
    cols="<?php echo $tiwidth; ?>"
    <?php echo $maxlength; ?>
    onkeyup="<?php echo $checkconditionFunction;?>"
>
<?php echo $dispVal;?>
</textarea>
</p>
<!-- end of answer -->
