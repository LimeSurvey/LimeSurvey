<?php
/**
 * Multiple Choice Html : item row
 *
 * @var $hiddenfield
 * @var $name
 * @var $title
 * @var $question
 * @var $checkedState
 * @var $sCheckconditionFunction
 * @var $myfname
 * @var $sValue
 */
?>

<!-- answer_row -->
<div class="col-sm-12">
        <div id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group <?php echo $extra_class; ?> checkbox' <?php echo $sDisplayStyle; ?> >
            <input
                class="checkbox"
                type="checkbox"
                name="<?php echo $name.$title; ?>"
                id="answer<?php echo $name.$title; ?>"
                value="Y"
                <?php echo $checkedState; ?>
                onclick='cancelBubbleThis(event); <?php echo $sCheckconditionFunction; ?>'
                aria-labelledby="label-answer<?php echo $name.$title; ?>"
            />

            <label for="answer<?php echo $name.$title; ?>" class="answertext"></label>

            <!--
                 The label text is provided inside a div,
                 so final user can add paragraph, div, or whatever he wants in the subquestion text
                 This field is related to the input thanks to attribute aria-labelledby
            -->
            <div class="label-text label-clickable" id="label-answer<?php echo $name.$title; ?>">
                    <?php echo $question; ?>
            </div>

            <input
                type="hidden"
                name="java<?php echo $myfname; ?>"
                id="java<?php echo $myfname; ?>"
                value="<?php echo $sValue; ?>"
            />
        </div>
</div>
<!-- end of answer_row -->
