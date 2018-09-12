<?php
    $surveyinfo = getSurveyInfo($surveyid);
    $oAdminTheme = AdminTheme::getInstance();
    $oAdminTheme->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'emailtemplates.js');
    $oAdminTheme->registerCssFile( 'PUBLIC', 'popup-dialog.css' );
    $count=0;
?>
<script type='text/javascript'>
    var sReplaceTextConfirmation='<?php eT("This will replace the existing text. Continue?","js"); ?>';
    var sKCFinderLanguage='<?php echo sTranslateLangCode2CK(App()->language); ?>';

    var LS = LS || {};  // namespace
    LS.lang = LS.lang || {};  // object holding translations
    LS.lang['Remove attachment'] = '<?php echo eT("Remove attachment"); ?>';
    LS.lang['Edit relevance equation'] = '<?php echo eT("Edit relevance equation"); ?>';

    $(document).ready(function () {
        $('button.add-attachment').click(function(e)
        {
            e.preventDefault();
            var target = $(this).parent().parent().parent().find('table');
            console.log("target = ");
            console.log(target);
            openKCFinder_singleFile(target);

        });
    });
</script>

<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("Edit email templates"))); ?>
    <h3><?php eT("Edit email templates"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

<?php echo CHtml::form(array('admin/emailtemplates/sa/update/surveyid/'.$surveyid), 'post', array('name'=>'emailtemplates', 'class'=>'form-horizontal', 'id'=>'emailtemplates'));?>

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

</div>
</div>
</div>

<div id="attachment-relevance-editor" class="modal fade">
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                <h4 class="modal-title"><?php eT("Relevance equation");?></h4>
            </div>
            <div class='modal-body'>
                <div class='form-group'>
                    <textarea class='form-control'></textarea>
                </div>
            </div>
            <div class='modal-footer'>
                <button type="button" class='btn btn-default' data-dismiss='modal'><?php eT("Close");?></button>
                <button type="button" class='btn btn-success'><?php eT("Apply");?></button>
            </div>
        </div>
    </div>
</div>
