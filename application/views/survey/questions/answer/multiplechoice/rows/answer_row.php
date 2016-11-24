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
<li id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group <?php echo $extra_class; ?>' <?php echo $sDisplayStyle; ?> >
    <input
        type="checkbox"
        name="<?php echo $name.$title; ?>"
        id="answer<?php echo $name.$title; ?>"
        value="Y"
        <?php echo $checkedState; ?>
        onclick='cancelBubbleThis(event); <?php echo $sCheckconditionFunction; ?>'
    />

    <label for="answer<?php echo $name.$title; ?>" class="checkbox-label control-label"><?php echo $question; ?></label>
    <input
        type="hidden"
        name="java<?php echo $myfname; ?>"
        id="java<?php echo $myfname; ?>"
        value="<?php echo $sValue; ?>"
    />
</li>
<!-- end of answer_row -->
