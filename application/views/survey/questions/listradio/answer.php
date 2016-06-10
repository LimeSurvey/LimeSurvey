<?php
/**
 * List Radio Html
 *
 * @var $name
 * @var $value
 */
?>

<!-- List Radio -->

<!-- answer -->
<div class="list-unstyled radio-list answers-list">
    <?php echo $sTimer; ?>

    <?php
        // rows/answer_row.php
        echo $sRows;
    ?>

    <input
        type="hidden"
        name="java<?php echo $name; ?>"
        id="java<?php echo $name; ?>"
        value="<?php echo $value;?>"
    />
</div>
<!-- end of answer -->
