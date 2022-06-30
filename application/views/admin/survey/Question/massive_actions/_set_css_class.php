<?php
/**
 * Set question css classes (parsed to massive action widget)
 */

/** @var AdminController $this */
/** @var Question $model */
?>
<form class="custom-modal-datas  form-horizontal">
    <div class="mb-3" id="CssClass">
        <label class="col-md-4 form-label"><?php eT("CSS class(es):"); ?></label>
        <div class="col-md-8">
            <input type="text" class="form-control custom-data attributes-to-update" id="cssclass" name="cssclass" value="">
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
        <input type="hidden" name="aValidQuestionTypes" value="15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*" class="custom-data"/>
    </div>
</form>
