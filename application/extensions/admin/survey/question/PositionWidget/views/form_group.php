<div id='Position' class='form-group'>
    <label class="col-sm-4 control-label" for='pos'><?php eT("Position:"); ?></label>
    <div class="col-sm-8">
        <select class='form-control' name='questionposition' id='questionposition'>
            <option value=''><?php eT("At end"); ?></option>
            <option value='0'><?php eT("At beginning"); ?></option>
            <?php foreach ($aQuestions as $oq): ?>
                <option value='<?php echo $oq->attributes['question_order'] + 1; ?>'><?php eT("After"); ?>: <?php echo $oq->attributes['title']; ?></option>
                <?php endforeach; ?>
        </select>
    </div>
</div>
