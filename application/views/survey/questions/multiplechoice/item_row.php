<?php
/**
 * Multiple Choice Html : item row
 *
 * @var $hiddenfield
 * @var $ia
 * @var $ansrow
 * @var $nbColLabelXs
 * @var $nbColLabelLg
 * @var $nbColInputLg
 * @var $nbColInputXs
 * @var $checkedState
 * @var $sCheckconditionFunction
 * @var $myfname
 * @var $sValue
 */
?>
<div id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group <?php echo $extra_class; ?>' <?php echo $sDisplayStyle; ?> >
    <div class="col-lg-<?php echo $nbColInputLg; ?> col-xs-<?php echo $nbColInputXs; ?>">
        <input
            class="checkbox"
            type="checkbox"
            name="<?php echo $ia[1].$ansrow['title']; ?>"
            id="answer<?php echo $ia[1].$ansrow['title']; ?>"
            value="Y"
            <?php echo $checkedState; ?>
            onclick='cancelBubbleThis(event); <?php echo $sCheckconditionFunction; ?>'
         />

         <label for="answer<?php echo $ia[1]{$ansrow['title']}; ?>" class="answertext hidden">
             <?php echo $ansrow['question']; ?>
         </label>

         <input type="hidden" name="java<?php echo $myfname; ?>" id="java<?php echo $myfname; ?>" value="<?php echo $sValue; ?>" />
    </div>
    <label for="answer<?php echo $ia[1]{$ansrow['title']}; ?>" class="control-label col-xs-<?php echo $nbColLabelXs; ?> col-lg-<?php echo $nbColLabelLg; ?> answertext">
        <?php echo $ansrow['question']; ?>
    </label>    
</div>
