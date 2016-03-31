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

<div class="question answer-item text-item <?php echo $extraclass; ?>">

    <div class='form-group'>

        <!-- Label -->
        <label class='control-label col-xs-12 col-sm-2 hide label' for='answer<?php echo $name; ?>' >
            <?php eT('Your answer'); ?>
        </label>

        <!-- Prefix -->
        <?php echo $prefix; ?>

        <!-- Input -->
        <div class='col-xs-12 col-sm-10'>
            <input
                class="form-control text <?php echo $kpclass;?>"
                type="text"
                size="<?php echo $tiwidth; ?>"
                name="<?php echo $name; ?>"
                id="answer<?php echo $name;?>"
                value="<?php echo $dispVal; ?>"
                <?php echo $maxlength; ?>
                onkeyup="<?php echo $checkconditionFunction; ?>"
            />
        </div>

        <!-- Suffix -->
        <?php echo $suffix; ?>

    </div>
</div>
