<?php
/**
 * Ranking question, item list header Html
 * @var $myfname
 * @var $labeltext
 */
?>
<li class="select-item">
    <label for="answer<?php echo $myfname;?>">
        <?php echo $labeltext;?>
    </label>

    <select  class='form-control' name="<?php echo $myfname;?>" id="answer<?php echo $myfname;?>">
