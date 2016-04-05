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
        <div id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group <?php echo $extra_class; ?>' <?php echo $sDisplayStyle; ?> >
                <label for="answer<?php echo $name.$title; ?>" class="answertext hidden">
                    <?php echo $question; ?>
                </label>

                <input
                    type="hidden"
                    name="java<?php echo $myfname; ?>"
                    id="java<?php echo $myfname; ?>"
                    value="<?php echo $sValue; ?>"
                />

                <label for="answer<?php echo $name.$title; ?>" class="answertext">
                    <input
                    class="checkbox"
                    type="checkbox"
                    name="<?php echo $name.$title; ?>"
                    id="answer<?php echo $name.$title; ?>"
                    value="Y"
                    <?php echo $checkedState; ?>
                    onclick='cancelBubbleThis(event); <?php echo $sCheckconditionFunction; ?>'
                    />
                    <?php echo $question; ?>
                </label>
        </div>
</div>
<!-- end of answer_row -->
