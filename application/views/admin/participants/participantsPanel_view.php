<script src="<?php echo Yii::app()->getConfig('adminscripts') . "participantpanel.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
    var exporttocsvcountall = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/exporttocsvcountAll"); ?>";
    var exporttocsvall = "<?php echo Yii::app()->getController()->createUrl("exporttocsvAll"); ?>";
    var okBtn = "<?php $clang->eT("OK", 'js') ?>";
    var error = "<?php $clang->eT("Error", 'js') ?>";
    var exportBtn = "<?php $clang->eT("Export", 'js') ?>";
    var cancelBtn = "<?php $clang->eT("Cancel", 'js') ?>";
    var sSelectAllText = "<?php $clang->eT("Select all", 'js') ?>";
    var sNonSelectedText = "<?php $clang->eT("None selected", 'js') ?>";
    var sNSelectedText = "<?php $clang->eT("selected", 'js') ?>";
    var exportToCSVURL = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/exporttocsv"); ?>";
</script>
<div class="menubar">
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Participant panel"); ?> </strong>
    </div>
    <?php
        $home = array('src' => $sImageURL.'home.png',
            'alt' => $clang->gT("Main admin screen"), 
            'title' => $clang->gT("Main admin screen"),  
            'style' => 'margin-left:2px');

        $information = array('src' => $sImageURL.'summary.png',
            'alt' => $clang->gT("Information"),  
            'title' => $clang->gT("Information"), 
            'style' => 'margin-left:2px');

        $import = array('src' => $sImageURL.'importcsv.png',
            'alt' => $clang->gT("Import from CSV file"), 
            'title' => $clang->gT("Import from CSV file"), 
            'style' => 'margin-left:0px',
            'style' => 'margin-right:1px');

        $export = array('src' => $sImageURL.'exportcsv.png',
            'alt' => $clang->gT("Export to CSV file"), 
            'title' => $clang->gT("Export to CSV file"),
            'name' => 'export',
            'id' => 'export',
            'style' => 'margin-left:0px',
            'style' => 'margin-right:1px');

        $display = array('src' => $sImageURL.'document.png',
            'alt' => $clang->gT("Display participants"),
            'title' => $clang->gT("Display participants"),
            'style' => 'margin-left:5px');

        $blacklist = array('src' => $sImageURL.'trafficred.png',
            'alt' => $clang->gT("Blacklist control"),
            'title' => $clang->gT("Blacklist control"),
            'style' => 'margin-left:1px',
            'style' => 'margin-right:1px');

        $globalsettings = array('src' => $sImageURL.'global.png',
            'alt' => $clang->gT("Global participant settings"),
            'title' => $clang->gT("Global participant settings"),
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
            'alt' => $clang->gT("Share panel"), 
            'title' => $clang->gT("Share panel"),
            'height' => 35,
            'width' => 35,
            'style' => 'margin-left:5px');

        $separator = array('src' => $sImageURL.'separator.gif',
            'alt' => '',
            'options'=> array(
                'class' => 'separator'),
            'title' => '');

        $ajaxloader = array('src' => $sImageURL.'ajax-loader.gif',
            'alt' => 'AJAX loader',
            'title' => 'AJAX loader');
    ?>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php
                echo CHtml::link(CHtml::image($home['src'], $home['alt']), Yii::app()->getController()->createUrl("/admin"));
                echo CHtml::link(CHtml::image($information['src'], $information['alt']), $this->createUrl('admin/participants/sa/index'));
                echo CHtml::link(CHtml::image($display['src'], $display['alt']), $this->createUrl('admin/participants/sa/displayParticipants'));
                echo CHtml::image($separator['src'], $separator['alt'], $separator['options']);
                echo CHtml::link(CHtml::image($import['src'], $import['alt']), $this->createUrl('admin/participants/sa/importCSV'));
                echo CHtml::link(CHtml::image($export['src'], $export['alt']), '#',array('id'=>$export['id']));
                echo CHtml::image($separator['src'], $separator['alt'], $separator['options']);
                echo CHtml::link(CHtml::image($blacklist['src'], $blacklist['alt']), $this->createUrl('admin/participants/sa/blacklistControl'));
                if (Permission::model()->hasGlobalPermission('superadmin','read'))
                {
                    echo CHtml::link(CHtml::image($globalsettings['src'], $globalsettings['alt']), $this->createUrl('admin/participants/sa/userControl'));
                }
                echo CHtml::image($separator['src'], $separator['alt'], $separator['options']);
                echo CHtml::link(CHtml::image($attributecontrol['src'], $attributecontrol['alt']), $this->createUrl('admin/participants/sa/attributeControl'));
                echo CHtml::image($separator['src'], $separator['alt'], $separator['options']);
                echo CHtml::link(CHtml::image($sharepanel['src'], $sharepanel['alt']), $this->createUrl('admin/participants/sa/sharePanel'));
            ?>
        </div>
    </div>
    <div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
        <?php echo CHtml::image($ajaxloader['src'], $ajaxloader['alt']); ?>
    </div>
    <div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
        <?php $clang->eT("There are no participants to be exported."); ?>
    </div>
    <div id="exportcsv" title="exportcsv" style="display:none" class='form30'>
        <ul><li><label for='attributes'><?php $clang->eT("Attributes to export:"); ?></label>
                <select id="attributes" name="attributes" multiple="multiple" style='width: 350px' size=7>
                    <?php
                        foreach ($aAttributes as $value)
                        {
                            echo "<option value=" . $value['attribute_id'] . ">" . $value['defaultname'] . "</option>\n";
                        }
                    ?>
                </select>
            </li>
        </ul>
    </div>    
</div>
