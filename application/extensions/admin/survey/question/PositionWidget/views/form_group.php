<?php
/**
 * Position Widget Form Group View
 * Display a position selector in form-group style, fit in right accordions
 *
 * @var $aQuestions     A array containing the datas to create the options for each questions
 */
?>

<!-- PositionWidget : form_group -->
<div id='PositionWidget' class='form-group'>
    <label class="col-sm-4 control-label" for='pos'><?php eT("Position:"); ?></label>
    <div class="col-sm-8">
        <select class='form-control <?php echo $this->classes;?>' name='questionposition' id='questionposition'>
            <option value=''><?php eT("At end"); ?></option>
            <option value='0'><?php eT("At beginning"); ?></option>
            <?php foreach ($aQuestions as $oq): ?>
                <option value='<?php echo $oq->attributes['question_order'] + 1; ?>'><?php eT("After"); ?>: <?php echo $oq->attributes['title']; ?></option>
                <?php endforeach; ?>
        </select>
    </div>
</div>
<!-- end of PositionWidget : form_group -->
