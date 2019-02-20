<?php
/**
 * Set question css classes (parsed to massive action widget)
 */
?>
<form class="custom-modal-datas  form-horizontal">
    <div class="form-group" id="CssClass">
        <label class="col-sm-4 control-label"><?php eT("CSS class(es):"); ?></label>
        <div class="col-sm-8">
            <input type="text" class="form-control custom-data attributes-to-update" id="cssclass" name="cssclass" value="">
        </div>
        <input type="hidden" name="sid" value="<?php echo (int) Yii::app()->request->getParam('surveyid',0); ?>" class="custom-data"/>
        <input type="hidden" name="aValidQuestionTypes" value="15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*" class="custom-data"/>
    </div>
</form>
