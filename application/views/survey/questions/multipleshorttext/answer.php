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
<ul class="<?php echo $coreClass?> list-unstyled form-horizontal" role="group" aria-describedby="ls-question-text-<?php echo $sgq; ?>">
    <?php
        echo $sRows;
    ?>
</ul>
<!-- end of answer -->
