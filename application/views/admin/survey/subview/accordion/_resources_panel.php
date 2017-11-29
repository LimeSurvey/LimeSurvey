<?php
/**
 * ressources panel tab
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResources');


App()->getClientScript()->registerScript("ressources-panel-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN);

?>
<!-- ressources panel -->
<div id='resources' class="container-fluid">
    <div class="row">
        <!-- Export -->
        <div class="col-sm-6">
            <a href="<?php echo $this->createUrl('admin/export/sa/resources/export/survey/surveyid/'.$surveyid); ?>" target="_blank" class="btn btn-default">
                <?php  eT("Export resources as ZIP archive") ?>
            </a>
            <a class="btn btn-default" href="" target='_blank' data-toggle="modal" data-target="#importRessourcesModal">
                <span class="fa fa-download"></span>
                <?php  eT("Import resources ZIP archive"); ?>
            </a>
        </div>
    </div>
    <div class="row">
        <br>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <iframe id="browseiframe" src="<?php echo Yii::app()->request->getBaseUrl() ; ?>/third_party/kcfinder/browse.php?language='<?php echo sTranslateLangCode2CK( App()->language); ?>'" width="100%" height="600px"></iframe>
        </div>
    </div>
</div>

<?php $this->renderPartial('/admin/survey/subview/import_ressources_modal', ['surveyid'=>$surveyid, 'ZIPimportAction' => $ZIPimportAction]); ?>
