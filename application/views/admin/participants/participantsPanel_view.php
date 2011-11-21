<script src="<?php echo $this->config->item('adminscripts')."participantPanel.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
 var exporttocsvcountall = "<?php echo site_url("admin/participants/exporttocsvcountAll");?>";
 var exporttocsvall = "<?php echo site_url("admin/participants/exporttocsvAll");?>";
 var okBtn = "<?php echo $clang->gT("OK") ?>";
 var error = "<?php echo $clang->gT("Error") ?>";
</script>
<div class="menubar">
  <div class='header ui-widget-header'>
    <div class="menubar-title-left">
      <strong><?php echo $clang->gT("Participants panel"); ?> </strong>
    </div>
  </div>
  <?php
    $home = array('src' => 'images/home.png',
                  'alt' => 'Home Page',
                  'title' => 'Home Page',
                  'style' => 'margin-left:2px');

    $information = array('src' => 'images/summary.png',
                         'alt' => 'Information',
                         'title' => 'Information',
                         'style' => 'margin-left:2px');

    $import = array('src' => 'images/importcsv.png',
                    'alt' => 'Import from CSV',
                    'title' => 'Import from CSV',
                    'style' => 'margin-left:0px',
                    'style' => 'margin-right:1px');

    $export = array('src' => 'images/exportcsv.png',
                    'alt' => 'Export All',
                    'title' => 'Export All',
                    'name' => 'export',
                    'id' => 'export',
                    'style' => 'margin-left:0px',
                    'style' => 'margin-right:1px');

    $display = array('src' => 'images/document.png',
                     'alt' => 'Display Participants',
                     'title' => 'Display Participants',
                     'style' => 'margin-left:5px');

    $blacklist = array('src' => 'images/trafficred.png',
                       'alt' => 'Blacklist control',
                       'title' => 'BlackList Control',
                       'style' => 'margin-left:1px',
                       'style' => 'margin-right:1px');

    $globalsettings = array('src' => 'images/token_manage.png',
                            'alt' => 'Global participant settings',
                            'title' => 'Global Participant Settings',
                            'style' => 'margin-left:5px',
                            'style' => 'margin-right:1px');

    $attributecontrol = array('src' => 'images/tag_green.png',
                              'alt' => 'Attribute control',
                              'title' => 'Attribute control',
                              'width' => 50,
                              'height' => 35,
                              'style' => 'margin-left:0px',
                              'style' => 'margin-right:1px');

    $sharepanel = array('src' => 'images/share.png',
                        'alt' => 'Share panel',
                        'title' => 'Share panel',
                        'height' => 35,
                        'width'=> 35,
                        'style' => 'margin-left:5px');

    $seperator = array('src' => 'images/seperator.gif',
                       'alt' => '',
                       'title' => '');

    $ajaxloader = array('src' => 'images/ajax-loader.gif',
                        'alt' => 'Ajax Loader',
                        'title' => 'Ajax Loader');
  ?>
  <div class='menubar-main'>
    <div class='menubar-left'>
     <?php
        echo anchor(site_url("admin"),img($home));
        echo anchor('admin/participants/index',img($information));
        echo anchor('admin/participants/displayParticipants',img($display));
        echo img($seperator);
        echo anchor('admin/participants/importCSV',img($import));
        echo img($export);
        echo img($seperator);
        echo anchor('admin/participants/blacklistControl',img($blacklist));
        if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
        {
            echo anchor('admin/participants/userControl',img($globalsettings));
        }
        echo img($seperator);
        echo anchor('admin/participants/attributeControl',img($attributecontrol));
        echo img($seperator);
        echo anchor('admin/participants/sharePanel', img($sharepanel));
     ?>
    </div>
  </div>
  <div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
   <?php echo img($ajaxloader); ?>
  </div>
  <div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
   <?php echo $clang->gT("There are no participants to be exported"); ?>
  </div>
</div>