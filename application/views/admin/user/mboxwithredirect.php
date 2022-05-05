<div class="jumbotron message-box">
    <h2><?php echo $title; ?></h2>
    <p class="lead <?php echo $classMsg; ?>"><?php echo $message; ?></p>
    <p><?php echo $extra; ?></p>
    <p>
        <?php echo CHtml::form($url, 'post'); ?>
        <input type="submit" class="btn btn-outline-secondary" value="<?php echo $urlText; ?>"/>
        <?php
        if (!empty($hiddenVars)) {
            foreach ($hiddenVars as $key => $value) {
                ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
                <?php
            }
        }
        ?>
        <?php echo CHtml::endForm(); ?>
    </p>
</div>

