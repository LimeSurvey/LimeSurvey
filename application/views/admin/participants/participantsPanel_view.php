<script src="<?php echo Yii::app()->getConfig('adminscripts')."participantPanel.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
 var exporttocsvcountall = "<?php echo Yii::app()->createUrl("exporttocsvcountAll");?>";
 var exporttocsvall = "<?php echo Yii::app()->createUrl("exporttocsvAll");?>";
 var okBtn = "<?php echo $clang->gT("OK") ?>";
 var error = "<?php echo $clang->gT("Error") ?>";
</script>
<div class="menubar">
  <div class='header ui-widget-header'>
    <div class="menubar-title-left">
      <strong><?php echo $clang->gT("Participant panel"); ?> </strong>
    </div>
  </div>
  <?php
    $home = array('src' => Yii::app()->getConfig('imageurl').'/home.png',
                  'alt' => 'Home Page',
                  'title' => 'Home Page',
                  'style' => 'margin-left:2px');
    
    $information = array('src' => Yii::app()->getConfig('imageurl').'/summary.png',
                         'alt' => 'Information',
                         'title' => 'Information',
                         'style' => 'margin-left:2px');
    
    $import = array('src' => Yii::app()->getConfig('imageurl').'/importcsv.png',
                    'alt' => 'Import from CSV',
                    'title' => 'Import from CSV',
                    'style' => 'margin-left:0px',
                    'style' => 'margin-right:1px');
    
    $export = array('src' => Yii::app()->getConfig('imageurl').'/exportcsv.png',
                    'alt' => 'Export All',
                    'title' => 'Export All',
                    'name' => 'export',
                    'id' => 'export',
                    'style' => 'margin-left:0px',
                    'style' => 'margin-right:1px');
    
    $display = array('src' => Yii::app()->getConfig('imageurl').'/document.png',
                     'alt' => 'Display participants',
                     'title' => 'Display participants',
                     'style' => 'margin-left:5px');
    
    $blacklist = array('src' => Yii::app()->getConfig('imageurl').'/trafficred.png',
                       'alt' => 'Blacklist control',
                       'title' => 'BlackList control',
                       'style' => 'margin-left:1px',
                       'style' => 'margin-right:1px');
    
    $globalsettings = array('src' => Yii::app()->getConfig('imageurl').'/token_manage.png',
                            'alt' => 'Global participant settings',
                            'title' => 'Global participant settings',
                            'style' => 'margin-left:5px',
                            'style' => 'margin-right:1px');
    
    $attributecontrol = array('src' => Yii::app()->getConfig('imageurl').'/tag_green.png',
                              'alt' => 'Attribute control',
                              'title' => 'Attribute control',
                              'width' => 50,
                              'height' => 35,
                              'style' => 'margin-left:0px',
                              'style' => 'margin-right:1px');
    
    $sharepanel = array('src' => Yii::app()->getConfig('imageurl').'/share.png',
                        'alt' => 'Share panel',
                        'title' => 'Share panel',
                        'height' => 35,
                        'width'=> 35,
                        'style' => 'margin-left:5px');
    
    $seperator = array('src' => Yii::app()->getConfig('imageurl').'/seperator.gif',
                       'alt' => '',
                       'title' => '');
    
    $ajaxloader = array('src' => Yii::app()->getConfig('imageurl').'/ajax-loader.gif',
                        'alt' => 'Ajax Loader',
                        'title' => 'Ajax Loader');
  ?>
  <div class='menubar-main'>
    <div class='menubar-left'>
     <?php 
        echo CHtml::link(CHtml::image($home['src'],$home['alt']),Yii::app()->createUrl("admin")); 
        echo CHtml::link(CHtml::image($information['src'],$information['alt']),$this->createURL('admin/participants/sa/index'));
        echo CHtml::link(CHtml::image($display['src'],$display['alt']),$this->createURL('admin/participants/sa/displayParticipants'));
        echo CHtml::image($seperator['src'],$seperator['alt']);
        echo CHtml::link(CHtml::image($import['src'],$import['alt']),$this->createURL('admin/participants/sa/importCSV'));
        echo CHtml::link(CHtml::image($export['src'],$export['alt']),$this->createURL('admin/participants/sa/exporttocsvAll'));
        echo CHtml::image($seperator['src'],$seperator['alt']);
        echo CHtml::link(CHtml::image($blacklist['src'],$blacklist['alt']),$this->createURL('admin/participants/sa/blacklistControl'));
        if(Yii::app()->session['USER_RIGHT_SUPERADMIN'])
        {
            echo CHtml::link(CHtml::image($globalsettings['src'],$globalsettings['alt']),$this->createURL('admin/participants/sa/userControl'));
        }
        echo CHtml::image($seperator['src'],$seperator['alt']);
        echo CHtml::link(CHtml::image($attributecontrol['src'],$attributecontrol['alt']),$this->createURL('admin/participants/sa/attributeControl'));
        echo CHtml::image($seperator['src'],$seperator['alt']);
        echo CHtml::link( CHtml::image($sharepanel['src'],$sharepanel['alt']),$this->createURL('admin/participants/sa/sharePanel'));
     ?>
    </div>
  </div>
  <div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
   <?php echo CHtml::image($ajaxloader['src'],$ajaxloader['alt']); ?>
  </div>
  <div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
   <?php echo $clang->gT("There are no participants to be exported"); ?>
  </div>
</div>