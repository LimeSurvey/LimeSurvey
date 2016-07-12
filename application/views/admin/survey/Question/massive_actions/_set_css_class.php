<?php
/**
 * Set question css classes (parsed to massive action widget)
 * @var $model      The question model
 * @var $oSurvey    The survey object
 */
?>
<form class="custom-modal-datas">
    <div  class="form-group" id="CssClass">
        <label class="col-sm-4 control-label"><?php eT("CSS class(es):"); ?></label>
        <div class="col-sm-8">
            <input type="text" class="form-control custom-data" id="cssclass" name="cssclass" value="">
        </div>
        <input type="hidden" name="sid" value="<?php echo $_GET['surveyid']; ?>" class="custom-data"/>
    </div>
</form>
<br/><br/>
