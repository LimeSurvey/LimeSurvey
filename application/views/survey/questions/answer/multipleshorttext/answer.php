<?php
/**
 * Multiple Short Text question Html
 *
 * @var $sRows      : the rows, generated with the views rows/answer_row*.php
 *
 */
?>
<!-- Multiple Shor Text -->

<!-- answer -->
<ul class="<?php echo $coreClass?> list-unstyled " role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <?php
        echo $sRows;
    ?>
</ul>
<!-- end of answer -->
