<?php
/**
 * subquestion row view
 *
 * @var $row
 * @var $position
 * @var $scale_id
 * @var $activated
 * @var $first
 * @var $surveyid
 * @var $gid
 * @var $qid
 * @var $language
 * @var $title
 * @var $question
 * @var $relevance
 * @var $oldCode
 *
 * NB : !!! If you edit this view, remember to check if answer option row view need also to be updated !!!
 */
?>

<!-- subquestion row -->
<tr id='row_<?php echo $language; ?>_<?php echo $qid; ?>_<?php echo $scale_id; ?>' class="row-container" data-common-id="<?php echo $qid; ?>_<?php echo $scale_id; ?>">
    <?php // If survey is active : no move button, code not editable ?>
    <?php if ($activated == 'Y'): ?>
        <!-- Move icon -->
        <td class="move-icon-disable">
            &nbsp;
        </td>

        <!-- Code (title) -->
        <td class="code-title" style="vertical-align: middle;">
            <input
                class="code-title"
                type='hidden'
                name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'   <?php   // TODO: uniformisation with  $scale_id and  $position ?>
                value="<?php echo $title; ?>"
                maxlength='20'
                size='5'
            />
            <?php echo $title; ?>
        </td>

    <?php //If survey is not activated and first language : move button, code editable   ?>
    <?php  elseif ($first): ?>

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
                    id='oldcode_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
                    name='oldcode_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
                    value="<?php echo $title; ?>"
                />
            <?php endif; ?>

            <input
                type='text'
                class="code form-control input-lg"
                id='code_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
                class='code code-title'
                name='code_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
                value="<?php echo $title; ?>"
                maxlength='20' size='20'
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


    <!-- No assessment values for subQuestions -->

    <!-- Answer (Subquestion Text) -->
    <td  class="subquestion-text" style="vertical-align: middle;">
        <input
            type='text'
            size='20'
            class='answer form-control input-lg'
            id='answer_<?php echo $language; ?>_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
            name='answer_<?php echo $language; ?>_<?php echo $qid; ?>_<?php echo $scale_id; ?>'
            placeholder='<?php eT("Some example subquestion","js") ?>'
            value="<?php echo $question; ?>"
            onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('save-button').click(); return false;}"
            />
    </td>

    <!-- Relevance equation -->
    <?php if ($first):?>
        <td class="relevance-equation">
            <input data-toggle="tooltip" data-title="<?php eT("Click to expand"); ?>" type='text' class='relevance form-control input-lg' id='relevance_<?php echo $qid; ?>_<?php echo $scale_id; ?>' name='relevance_<?php echo $qid; ?>_<?php echo $scale_id; ?>' value="<?php echo $relevance; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('save-button').click(); return false;}" />
        </td>
    <?php else: ?>
        <span style="display: none" class="relevance relevance-equation">
            <?php echo $relevance; ?>
        </span>
    <?php endif; ?>


    <!-- Icons add/edit/delete -->
    <td style="vertical-align: middle;" class="subquestion-actions">

        <?php echo  getEditor("editanswer","answer_".$language."_".$qid."_{$scale_id}", "[".gT("Subquestion:", "js")."](".$language.")",$surveyid,$gid,$qid,'editanswer'); ?>

        <?php if ( $activated != 'Y' && $first  ):?>
            <?php
                // TODO : to merge subquestion and answer options,  implies : define in controller titles
            ?>

            <span class="icon-add text-success btnaddanswer" data-code="<?php echo $title; ?>" data-toggle="tooltip" data-scale-id="<?php echo $scale_id; ?>" data-placement="bottom" title="<?php eT("Insert a new subquestion after this one") ?>"></span>
            <span class="glyphicon glyphicon-trash text-danger btndelanswer"  data-toggle="tooltip" data-placement="bottom" title="<?php eT("Delete this subquestion") ?>"></span>
        <?php endif; ?>

    </td>
</tr>
