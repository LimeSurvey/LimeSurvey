<?php
/**
 * answer option row view
 *
 * @var $position
 * @var $first
 * @var $assessmentvisible
 * @var $scale_id
 * @var $title
 * @var $surveyid
 * @var $gid
 * @var $qid
 * @var $language
 * @var $assessment_value
 * @var $sortorder
 * @var $answer
 *
 *
 * NB : !!! If you edit this view, remember to check if subquestion row view need also to be updated !!!
 */
?>

<tr class='row-container row_<?php echo $position; ?>' id='row_<?php echo $language; ?>_<?php echo $position; ?>_<?php echo $qid; ?>_<?php echo $scale_id; ?>' data-common-id="<?php echo $position; ?>_<?php echo $qid; ?>_<?php echo $scale_id; ?>">

    <?php if ( $first ): // If survey is not activated and first language ?>

        <?php $sPattern = ($title)?"^([a-zA-Z0-9]*|{$title})$":"^[a-zA-Z0-9]*$"; ?>

        <!-- Move icon -->
        <td class="move-icon" >
            <span class="glyphicon glyphicon-move"></span>
        </td>

        <!-- Code (title) -->
        <td  class="code-title" style="vertical-align: middle;">

            <?php if($oldCode): ?>
            <input
                type='hidden'
                class='oldcode code-title'
                id='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?php echo $title; ?>"
            />
            <?php endif; ?>

            <input
                type='text'
                class="code form-control input-lg"
                id='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                class='code code-title'
                name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?php echo $title; ?>"
                maxlength='5' 
                pattern='<?php echo $sPattern; ?>'
                required='required'
            />
        </td>
        <?php // If survey is not active, and it's not the first language : no move button, code not editable ?>
    <?php else:?>

        <!-- Move icon -->
        <td class="move-icon-disable">
            &nbsp;
        </td>

        <!-- Code (title) -->
        <td  class="code-title" style="vertical-align: middle;">
            <?php echo $title; ?>
        </td>
    <?php endif; ?>


    <!-- Assessment Value -->
    <?php if ($assessmentvisible && $first): ?>
        <td class="assessment-value">
            <input
                type='text'
                class='assessment form-control input-lg'
                id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?php echo $assessment_value; ?>"
                maxlength='5'
                size='5'
                onkeypress="return goodchars(event,'-1234567890')"
            />
        </td>
    <?php elseif ( $first): ?>
        <td style='display:none;' class="assessment-value">
            <input
                type='text'
                class='assessment'
                id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?php echo $assessment_value; ?>" maxlength='5' size='5'
                onkeypress="return goodchars(event,'-1234567890')"
            />
        </td>
    <?php elseif ($assessmentvisible): ?>
        <td class="assessment-value">
            <?php echo $row['assessment_value']; ?>
        </td>
    <?php else: ?>
        <td style='display:none;' class="assessment-value">
        </td>
    <?php endif; ?>

    <!-- Answer (Subquestion Text) -->
    <td  class="subquestion-text" style="vertical-align: middle;">
        <input
            type='text'
            size='20'
            class='answer form-control input-lg'
            id='answer_<?php echo $language; ?>_<?php echo $sortorder; ?>_<?php echo $scale_id; ?>'
            name='answer_<?php echo $language; ?>_<?php echo $sortorder; ?>_<?php echo $scale_id; ?>'
            placeholder='<?php eT("Some example answer option","js") ?>'
            value="<?php echo $answer; ?>"
            onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('save-button').click(); return false;}"
        />
    </td>

    <!-- No relevance equation for answer options -->


    <!-- Icons edit/delete -->
    <td style="vertical-align: middle;" class="subquestion-actions">

        <?php echo  getEditor("editanswer","answer_".$language."_".$qid."_{$scale_id}", "[".gT("Subquestion:", "js")."](".$language.")",$surveyid,$gid,$qid,'editanswer'); ?>

        <?php if ( $first):?>
            <span class="icon-add text-success btnaddanswer" data-assessmentvisible='<?php echo $assessmentvisible;?>' data-position="<?php echo $position; ?>" data-code="<?php echo $title; ?>" data-scale-id="<?php echo $scale_id; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Insert a new answer option after this one") ?>"></span>
            <span class="glyphicon glyphicon-trash text-danger btndelanswer" data-toggle="tooltip" data-placement="bottom"  title="<?php eT("Delete this answer option") ?>"></span>
        <?php endif; ?>
    </td>
</tr>
