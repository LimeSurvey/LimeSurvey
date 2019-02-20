<?php
/**
 * Set subquestion/answer order
 */
$surveyid = App()->request->getParam('surveyid', 0);
?>
<form class="custom-modal-datas form-horizontal">
    <div  class="form-group" id="CssClass">
        <label class="col-sm-4 control-label"><?php eT("Random order:"); ?></label>
        <div class="col-sm-8">
            <select class="form-control custom-data attributes-to-update" id="random_order" name="random_order">
                <option value="0" selected="selected"><?php eT('Off');?></option>
                <option value="1"><?php eT('Randomize on each page load');?></option>
            </select>
        </div>
        <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" class="custom-data"/>
        <input type="hidden" name="aValidQuestionTypes" value="!ABCEFHKLMOPQRWZ1:;" class="custom-data"/>
    </div>
</form>
