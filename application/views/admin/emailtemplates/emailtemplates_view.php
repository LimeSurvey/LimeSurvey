<?php
    $surveyinfo = getSurveyInfo($surveyid);
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'emailtemplates.js');
    $count=0;
?>
<script type='text/javascript'>
    var sReplaceTextConfirmation='<?php eT("This will replace the existing text. Continue?","js"); ?>';
    var sKCFinderLanguage='<?php echo sTranslateLangCode2CK(App()->language); ?>';


$(document).ready(function () {
    $('button.add-attachment').click(function(e)
    {
        e.preventDefault();
        var target = $(this).parent().find('table');
        openKCFinder_singleFile(target);

    });



});




</script>

<div class="side-body">
	<h3><?php eT("Edit email templates"); ?></h3>

	<div class="row">
		<div class="col-lg-12 content-right">

<?php echo CHtml::form(array('admin/emailtemplates/sa/update/surveyid/'.$surveyid), 'post', array('name'=>'emailtemplates', 'class'=>'form30newtabs', 'id'=>'emailtemplates'));?>

        <ul class="nav nav-tabs">
            <?php foreach ($grplangs as $grouplang): ?>
                <li role="presentation" class="<?php if($count==0){ echo 'active'; $count++; }?>" >
                    <a data-toggle="tab" href='#tab-<?php echo $grouplang; ?>'><?php echo getLanguageNameFromCode($grouplang,false); ?>
                        <?php if ($grouplang == Survey::model()->findByPk($surveyid)->language): ?>
                            <?php echo ' ('.gT("Base language").')'; ?>
                            <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php
                $count = 0;
                $active = 'active';
                foreach ($grplangs as $key => $grouplang)
                {
                    $bplang = $bplangs[$key];
                    $esrow = $attrib[$key];
                    $aDefaultTexts = $defaulttexts[$key];
                    if ($ishtml == true)
                    {
                        $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditionalNewlineToBreak($aDefaultTexts['admin_detailed_notification'],$ishtml);
                    }

                    $this->renderPartial('/admin/emailtemplates/email_language_tab', compact('surveyinfo', 'ishtml', 'surveyid', 'grouplang', 'bplang', 'esrow', 'aDefaultTexts', 'active'));
                    
                    if($count == 0)
                    {
                        $count++;
                        $active = '';
                    }                    
                }
            ?>
            <p>
                <?php echo CHtml::htmlButton(gT('Save'),array('type'=>'submit','value'=>'save','name'=>'save', 'class'=>'hidden')) ?>
                <?php echo CHtml::htmlButton(gT('Save and close'),array('type'=>'submit','value'=>'saveclose','name'=>'save', 'class'=>'hidden')) ?>
                <?php echo CHtml::hiddenField('action','tokens'); ?>
                <?php echo CHtml::hiddenField('language',$esrow->surveyls_language); ?>
            </p>
        </div>
    <?php echo CHtml::endForm() ?>
<div id="attachment-relevance-editor" style="display: none; overflow: hidden;">
    <textarea style="resize: none; height: 90%; width: 100%; box-sizing: border-box">

    </textarea>
    <button>Apply</button>
</div>

</div>
</div>
</div>
