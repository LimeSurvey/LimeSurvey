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

<p class="question answer-item text-item <?php echo $extraclass; ?>">

    <!-- Label -->
    <label for='answer<?php echo $name; ?>' class='hide label'>
        <?php eT('Your answer'); ?>
    </label>

    <!-- Prefix -->
    <?php echo $prefix; ?>

    <!-- Input -->
    <input
        class="text <?php echo $kpclass;?>"
        type="text"
        size="<?php echo $tiwidth; ?>"
        name="<?php echo $name; ?>"
        id="answer<?php echo $name;?>"
        value="<?php echo $dispVal; ?>"
        <?php echo $maxlength; ?>
        onkeyup="<?php echo $checkconditionFunction; ?>"
    />

    <!-- Suffix -->
    <?php echo $suffix; ?>
</p>
