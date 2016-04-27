<?php
/**
 * ressources panel tab
 */
?>

<!-- ressources panel -->
<div id='resources' class="tab-pane fade in">
    <ul class="list-unstyled">

        <!-- Browse -->
        <li>
            <label>&nbsp;</label>
            <?php echo CHtml::dropDownList('type', 'files', array('files' =>  gT('Files','unescaped'), 'flash' =>  gT('Flash','unescaped'), 'images' =>  gT('Images','unescaped')), array('class'=>'btn btn-default')); ?>
            <a class="btn btn-default" href="<?php echo Yii::app()->request->getBaseUrl() ; ?>/third_party/kcfinder/browse.php?language='<?php echo sTranslateLangCode2CK( App()->language); ?>'" target='_blank'>
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
