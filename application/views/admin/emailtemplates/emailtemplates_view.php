<?php
/**
 * General options
 * @var AdminController $this
 * @var Survey $oSurvey
 */

$count=0;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyEmailTemplates');


App()->getClientScript()->registerScript( "EmailTemplateViews_variables", "
var sReplaceTextConfirmation='".gT("This will replace the existing text. Continue?","js")."';
var sKCFinderLanguage='".sTranslateLangCode2CK(App()->language)."';

var LS = LS || {};  // namespace
    LS.lang = LS.lang || {};  // object holding translations
    LS.lang['Remove attachment'] = '".gT("Remove attachment")."';
    LS.lang['Edit relevance equation'] = '".gT("Edit relevance equation")."';
", LSYii_ClientScript::POS_BEGIN );

?>        
        <div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3><?php eT("Edit email templates"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

<?php echo CHtml::form(array('admin/emailtemplates/sa/update/surveyid/'.$surveyid), 'post', array('name'=>'emailtemplates', 'class'=>'', 'id'=>'emailtemplates'));?>

        <ul class="nav nav-tabs">
            <?php foreach ($oSurvey->allLanguages as $grouplang): ?>
                <li role="presentation" class="<?php if($count==0){ echo 'active'; $count++; }?>" >
                    <a data-toggle="tab" href='#tab-<?php echo $grouplang; ?>'><?php echo getLanguageNameFromCode($grouplang,false); ?>
                        <?php if ($grouplang == $oSurvey->language): ?>
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
                foreach ($oSurvey->allLanguages as $key => $grouplang) {
                    $bplang = $bplangs[$key];
                    $esrow = $attrib[$key];
                    $aDefaultTexts = $defaulttexts[$key];

                    $this->renderPartial('/admin/emailtemplates/email_language_tab', compact( 'ishtml', 'surveyid', 'grouplang', 'bplang', 'esrow', 'aDefaultTexts', 'active'));

                    if($count == 0) {
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

<div class="modal modal-large fade" tabindex="-1" role="dialog" id="kc-modal-open">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=gT("Choose file")?></h4>
      </div>
      <div class="modal-body" style="padding: 0;">
        <iframe frameBorder="0" style="min-height: 600px; height:100%; width: 100%;" src="about:blank"></iframe>
      </div>
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
