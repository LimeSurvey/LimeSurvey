<?php
App()->getClientScript()->registerScript( "EmailTemplateViews_variables", "
window.EmailTemplateData = ".json_encode($jsData).";
", LSYii_ClientScript::POS_BEGIN);
?>

<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3><?php eT("Edit email templates"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(array('admin/emailtemplates/sa/update/surveyid/'.$surveyid), 'post', array('name'=>'emailtemplates', 'data-isvuecomponent' => 'true','class'=>'', 'id'=>'emailtemplates'));?>
                <div id="emailTemplatesEditor"><emailtemplateseditor /></div>
            <?php echo CHtml::endForm() ?>  
        </div>
    </div>
</div>
