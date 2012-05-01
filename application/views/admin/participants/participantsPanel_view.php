<script src="<?php echo Yii::app()->getConfig('adminscripts') . "participantPanel.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
    var exporttocsvcountall = "<?php echo Yii::app()->getController()->createUrl("exporttocsvcountAll"); ?>";
    var exporttocsvall = "<?php echo Yii::app()->getController()->createUrl("exporttocsvAll"); ?>";
    var okBtn = "<?php $clang->eT("OK") ?>";
    var error = "<?php $clang->eT("Error") ?>";
</script>
<div class="menubar">
    <div class='menubar-title ui-widget-header'>
            <strong><?php $clang->eT("Participant panel"); ?> </strong>
    </div>
    <?php
    $home = array('src' => $sImageURL.'home.png',
        'alt' => 'Home Page',
        'title' => 'Home Page',
        'style' => 'margin-left:2px');

    $information = array('src' => $sImageURL.'summary.png',
        'alt' => 'Information',
        'title' => 'Information',
        'style' => 'margin-left:2px');

    $import = array('src' => $sImageURL.'importcsv.png',
        'alt' => 'Import from CSV',
        'title' => 'Import from CSV',
        'style' => 'margin-left:0px',
        'style' => 'margin-right:1px');

    $export = array('src' => $sImageURL.'exportcsv.png',
        'alt' => 'Export all',
        'title' => 'Export all',
        'name' => 'export',
        'id' => 'export',
        'style' => 'margin-left:0px',
        'style' => 'margin-right:1px');

    $display = array('src' => $sImageURL.'document.png',
        'alt' => 'Display participants',
        'title' => 'Display participants',
        'style' => 'margin-left:5px');

    $blacklist = array('src' => $sImageURL.'trafficred.png',
        'alt' => 'Blacklist control',
        'title' => 'Blacklist control',
        'style' => 'margin-left:1px',
        'style' => 'margin-right:1px');

    $globalsettings = array('src' => $sImageURL.'global.png',
        'alt' => 'Global participant settings',
        'title' => 'Global participant settings',
        'style' => 'margin-left:5px',
        'style' => 'margin-right:1px');

    $attributecontrol = array('src' => $sImageURL.'tag.png',
        'alt' => $clang->gT("Attribute management"),
        'title' => $clang->gT("Attribute management"),
        'width' => 50,
        'height' => 35,
        'style' => 'margin-left:0px',
        'style' => 'margin-right:1px');

    $sharepanel = array('src' => $sImageURL.'share.png',
        'alt' => 'Share panel',
        'title' => 'Share panel',
        'height' => 35,
        'width' => 35,
        'style' => 'margin-left:5px');

    $seperator = array('src' => $sImageURL.'separator.gif',
        'alt' => '',
        'options'=> array(
        'class' => 'separator'),
        'title' => '');

    $ajaxloader = array('src' => $sImageURL.'ajax-loader.gif',
        'alt' => 'Ajax Loader',
        'title' => 'Ajax Loader');
    ?>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php
            echo CHtml::link(CHtml::image($home['src'], $home['alt']), Yii::app()->getController()->createUrl("/admin"));
            echo CHtml::link(CHtml::image($information['src'], $information['alt']), $this->createURL('admin/participants/index'));
            echo CHtml::link(CHtml::image($display['src'], $display['alt']), $this->createURL('admin/participants/displayParticipants'));
            echo CHtml::image($seperator['src'], $seperator['alt'], $seperator['options']);
            echo CHtml::link(CHtml::image($import['src'], $import['alt']), $this->createURL('admin/participants/importCSV'));
            echo CHtml::link(CHtml::image($export['src'], $export['alt']), $this->createURL('admin/participants/exporttocsvAll'));
            echo CHtml::image($seperator['src'], $seperator['alt'], $seperator['options']);
            echo CHtml::link(CHtml::image($blacklist['src'], $blacklist['alt']), $this->createURL('admin/participants/blacklistControl'));
            if (Yii::app()->session['USER_RIGHT_SUPERADMIN'])
            {
                echo CHtml::link(CHtml::image($globalsettings['src'], $globalsettings['alt']), $this->createURL('admin/participants/userControl'));
            }
            echo CHtml::image($seperator['src'], $seperator['alt'], $seperator['options']);
            echo CHtml::link(CHtml::image($attributecontrol['src'], $attributecontrol['alt']), $this->createURL('admin/participants/attributeControl'));
            echo CHtml::image($seperator['src'], $seperator['alt'], $seperator['options']);
            echo CHtml::link(CHtml::image($sharepanel['src'], $sharepanel['alt']), $this->createURL('admin/participants/sharePanel'));
            ?>
        </div>
    </div>
    <div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
        <?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
    <div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
        <?php $clang->eT("There are no participants to be exported"); ?>
    </div>
</div>
