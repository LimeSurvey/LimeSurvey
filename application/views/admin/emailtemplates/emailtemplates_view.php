<?php 
    $surveyinfo = getSurveyInfo($surveyid); 
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'emailtemplates.js');
?>
<script type='text/javascript'>
    var sReplaceTextConfirmation='<?php $clang->eT("This will replace the existing text. Continue?","js"); ?>';
    var sKCFinderLanguage='<?php echo sTranslateLangCode2CK($clang->getlangcode()) ?>';
    

$(document).ready(function () {
    $('button.add-attachment').click(function(e)
    {
        e.preventDefault();
        var target = $(this).parent().find('table');
        openKCFinder_singleFile(target); 
        
    });
    
    
    
});




</script>

<div class='header ui-widget-header'>
    <?php $clang->eT("Edit email templates"); ?>
</div>
<?php echo CHtml::form(array('admin/emailtemplates/sa/update/surveyid/'.$surveyid), 'post', array('name'=>'emailtemplates', 'class'=>'form30newtabs'));?>

    <div id='tabs'>
        <ul>
            <?php foreach ($grplangs as $grouplang): ?>
                <li><a href='#tab-<?php echo $grouplang; ?>'><?php echo getLanguageNameFromCode($grouplang,false); ?>
                        <?php if ($grouplang == Survey::model()->findByPk($surveyid)->language): ?>
                            <?php echo ' ('.$clang->gT("Base language").')'; ?>
                            <?php endif; ?>
                    </a></li>
                <?php endforeach; ?>
        </ul>
        <?php 
            foreach ($grplangs as $key => $grouplang)
            {
                $bplang = $bplangs[$key];
                $esrow = $attrib[$key];
                $aDefaultTexts = $defaulttexts[$key];
                if ($ishtml == true)
                {
                    $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditionalNewlineToBreak($aDefaultTexts['admin_detailed_notification'],$ishtml);
                }
                $this->renderPartial('/admin/emailtemplates/email_language_tab', compact('surveyinfo', 'ishtml', 'surveyid', 'clang', 'grouplang', 'bplang', 'esrow', 'aDefaultTexts'));
            }
            ?>
    </div>
    <p>
        <?php echo CHtml::htmlButton($clang->gT('Save'),array('type'=>'submit','value'=>'save','name'=>'save')) ?>
        <?php echo CHtml::htmlButton($clang->gT('Save and close'),array('type'=>'submit','value'=>'saveclose','name'=>'save')) ?>
        <?php echo CHtml::hiddenField('action','tokens'); ?>
        <?php echo CHtml::hiddenField('language',$esrow->surveyls_language); ?>
    </p>
    <?php echo CHtml::endForm() ?>
<div id="attachment-relevance-editor" style="display: none; overflow: hidden;">
    <textarea style="resize: none; height: 90%; width: 100%; box-sizing: border-box">

    </textarea>
    <button>Apply</button>
</div>
