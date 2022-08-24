<?php
/**
 * Set subquestion/answer order
 */
$surveyid = App()->request->getParam('surveyid', 0);
/** @var AdminController $this */
/** @var Question $model */

?>
<form class="custom-modal-datas form-horizontal" data-trigger-validation="true">
    <div  class="mb-3" id="CssClass">
        <label class="col-md-4 form-label"><?php eT("Random order:"); ?></label>
        <div class="col-md-8">
            <select class="form-select custom-data attributes-to-update" id="random_order" name="random_order" required>
                <option value="" selected="selected"><?php eT('Please select an option');?></option>
                <option value="0"><?php eT('Off');?></option>
                <option value="1"><?php eT('Randomize on each page load');?></option>
            </select>
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) $surveyid; ?>" class="custom-data"/>
        <input type="hidden" name="aValidQuestionTypes" value="!ABCEFHKLMOPQRWZ1:;" class="custom-data"/>
    </div>
</form>
