<?php
/**
 * List with comment, list style, Html
 *
 * @var $sRows         : the list of radio, generated with the view answer_row.php
 *
 * @var $id
 * @var $hint_comment
 * @var $kpclass
 * @var $name
 * @var $tarows
 * @var $has_comment_saved
 * @var $comment_saved
 * @var $java_name
 * @var $java_id
 * @var $java_value
 */
?>
<!-- List with comment, list style, -->

<!-- answer -->
<div class="<?php echo $coreClass; ?> row" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <div class="answers-list radio-list col-sm-6 col-xs-12">
        <ul class="list-unstyled" role="radiogroup">
            <?php
                // rows/row.php
                echo $sRows;
            ?>
        </ul>
        <?php
        /* Value for expression manager javascript (use id) ; no need to submit */
        echo \CHtml::hiddenField($java_name,$java_value,array(
            'id' => $java_id,
            'disabled' => true,
        ));
        ?>
    </div>

    <div class="form-group answer-item text-item col-sm-6 col-xs-12">
        <label class="control-label" for="<?php echo $id; ?>">
            <?php echo $hint_comment;?>:
        </label>
        <?php
        echo \CHtml::textArea($name,$comment_saved,array(
            'id' => $id,
            'class' => "form-control {$kpclass}",
            'rows' => $tarows
        ));
        ?>
    </div>
</div>


<!-- end of answer -->
