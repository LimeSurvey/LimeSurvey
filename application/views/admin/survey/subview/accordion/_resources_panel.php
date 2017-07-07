<?php
/**
 * ressources panel tab
 */
?>
<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>'
    var sURLParameters = '';
    var sAddParam = '';
</script>
<!-- ressources panel -->
<div id='resources' >
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <ul class="list-unstyled">

                <!-- Browse -->
                <li>
                    <label>&nbsp;</label>
                    <?php echo CHtml::dropDownList('type', 'files', array('files' =>  gT('Files','unescaped'), 'flash' =>  gT('Flash','unescaped'), 'images' =>  gT('Images','unescaped')), array('class'=>'btn btn-default')); ?>
                    <a id="loadiframe" class="btn btn-default" href="<?php echo Yii::app()->request->getBaseUrl() ; ?>/third_party/kcfinder/browse.php?language='<?php echo sTranslateLangCode2CK( App()->language); ?>'" target='_blank'>
                        <?php  eT("Browse uploaded resources") ?>
                    </a>
                </li>

                <!-- Export -->
                <li>
                    <br/>
                    <label>&nbsp;</label>
                    <a href="<?php echo $this->createUrl('admin/export/sa/resources/export/survey/surveyid/'.$surveyid); ?>" target="_blank" class="btn btn-default">
                        <?php  eT("Export resources as ZIP archive") ?>
                    </a>
                </li>

                <!-- Export -->
                <li>
                    <br/>
                    <label>&nbsp;</label>
                    <a class="btn btn-default" href="" target='_blank' data-toggle="modal" data-target="#importRessourcesModal">
                        <span class="fa fa-download"></span>
                        <?php  eT("Import resources ZIP archive"); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-sm-12 col-md-8">
            <iframe id="browseiframe" src="<?php echo Yii::app()->request->getBaseUrl() ; ?>/third_party/kcfinder/browse.php?language='<?php echo sTranslateLangCode2CK( App()->language); ?>'" width="100%" height="600px"></iframe>
        </div>
    </div>
</div>
<script>
    $('#loadiframe').on('click', function(e){
        e.preventDefault();
        $('#browseiframe').attr('src', $(this).attr('href'));
    })
</script>
<?php $this->renderPartial('/admin/survey/subview/import_ressources_modal', ['surveyid'=>$surveyid, 'ZIPimportAction' => $ZIPimportAction]); ?>