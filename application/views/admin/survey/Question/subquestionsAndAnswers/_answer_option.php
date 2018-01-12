<?php
/**
 * answer option row view
 *
 * @var $position
 * @var $first
 * @var $assessmentvisible
 * @var $scale_id
 * @var Answer $oAnswer
 *
 *
 * NB : !!! If you edit this view, remember to check if subquestion row view need also to be updated !!!
 */
?>

<tr class='row-container row_<?php echo $position; ?>' id='row_<?= $oAnswer->language;?>_<?php echo $position; ?>_<?= $oAnswer->question->qid; ?>_<?php echo $scale_id; ?>' data-common-id="<?php echo $position; ?>_<?= $oAnswer->question->qid; ?>_<?php echo $scale_id; ?>">

    <?php if ( $first ): // If survey is not activated and first language ?>

        <?php $sPattern = ($oAnswer->code)?"^([a-zA-Z0-9]*|{$oAnswer->code})$":"^[a-zA-Z0-9]*$"; ?>

        <!-- Move icon -->
        <td class="move-icon" >
            <span class="fa fa-bars bigIcons"></span>
        </td>

        <!-- Code (title) -->
        <td  class="code-title" style="vertical-align: middle;">

            <?php if($oldCode): ?>
            <input
                type='hidden'
                class='oldcode code-title'
                id='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?= $oAnswer->code; ?>"
            />
            <?php endif; ?>

            <input
                type='text'
                class="code form-control input code-title"
                id='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?= $oAnswer->code; ?>"
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
            <?= $oAnswer->code; ?>
        </td>
    <?php endif; ?>


    <!-- Assessment Value -->
    <?php if ($assessmentvisible && $first): ?>
        <td class="assessment-value">
            <input
                type='text'
                class='assessment form-control input'
                id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                value="<?= $oAnswer->assessment_value; ?>"
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
                value="<?= $oAnswer->assessment_value; ?>" maxlength='5' size='5'
                onkeypress="return goodchars(event,'-1234567890')"
            />
        </td>
    <?php elseif ($assessmentvisible): ?>
        <td class="assessment-value">
            <?= $oAnswer->assessment_value; ?>
        </td>
    <?php else: ?>
        <td style='display:none;' class="assessment-value">
        </td>
    <?php endif; ?>

    <!-- Answer (Subquestion Text) -->
    <td  class="subquestion-text" style="vertical-align: middle;">
        <div class="input-group">
            <input
                    type='text'
                    size='20'
                    class='answer form-control input'
                    id='answer_<?= $oAnswer->language;?>_<?= $oAnswer->sortorder;?>_<?php echo $scale_id; ?>'
                    name='answer_<?= $oAnswer->language;?>_<?= $oAnswer->sortorder;?>_<?php echo $scale_id; ?>'
                    placeholder='<?php eT("Some example answer option","js") ?>'
                    value="<?= $oAnswer->answer;?>"
                    onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('save-button').click(); return false;}"
            />
            <span class="input-group-addon">
                <?php echo  getEditor("editanswer","answer_".$oAnswer->language."_".$oAnswer->qid."_{$scale_id}", "[".gT("Subquestion:", "js")."](".$oAnswer->language.")",$surveyid,$oAnswer->question->gid,$oAnswer->qid,'editanswer'); ?>
            </span>
        </div>
    </td>

    <!-- No relevance equation for answer options -->


    <!-- Icons edit/delete -->
    <td style="vertical-align: middle;" class="subquestion-actions">
        <?php if ( $first):?>
            <button class="btn btn-default btn-sm btnaddanswer"><i class="icon-add text-success " data-assessmentvisible='<?php echo $assessmentvisible;?>' data-position="<?php echo $position; ?>" data-code="<?php echo $title; ?>" data-scale-id="<?php echo $scale_id; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Insert a new answer option after this one") ?>"></i></button>
            <button class="btn btn-default btn-sm btndelanswer"><i class="fa fa-trash text-danger " data-toggle="tooltip" data-placement="bottom"  title="<?php eT("Delete this answer option") ?>"></i></button>
        <?php endif; ?>
    </td>
</tr>
