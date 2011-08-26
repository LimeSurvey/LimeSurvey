<html>
    <head>
    <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js"?>" type="text/javascript"></script>
    <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('adminscripts')."participantPanel.js" ?>" type="text/javascript"></script>
    
       <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/adminstyle.css" ?>" />
        <title></title>
        <script type="text/javascript">
        var exporttocsvcountall = "<?php echo site_url("admin/participants/exporttocsvcountAll");?>";
        var exporttocsvall = "<?php echo site_url("admin/participants/exporttocsvAll");?>";
        var okBtn = "<?php echo $clang->gT("OK") ?>";
        var error = "<?php echo $clang->gT("Error") ?>";
        </script>
    </head>
    <body>
       <div class="menubar">
      <div class='header ui-widget-header'><div class="menubar-title-left">
	<strong><?php echo $clang->gT("Participants Panel"); ?> </strong>
	</div></div>
        <?php
	$home = array(
          'src' => 'images/home.png',
          'alt' => 'Home Page',
          'title' => 'Home Page',
            'style' => 'margin-left:2px'
          );
        $information = array(
          'src' => 'images/summary.png',
          'alt' => 'Information',
          'title' => 'Information',
            'style' => 'margin-left:2px'
          );
        
        $import = array(
          'src' => 'images/importcsv.png',
          'alt' => 'Import from CSV',
          'title' => 'Import from CSV',
            'style' => 'margin-left:0px',
            'style' => 'margin-right:1px'
          );
                
        $export = array(
          'src' => 'images/exportcsv.png',
          'alt' => 'Export All',
          'title' => 'Export All',
          'name' => 'export',
           'id' => 'export',
            'style' => 'margin-left:0px',
            'style' => 'margin-right:1px'
          );
        $display = array(
          'src' => 'images/document.png',
          'alt' => 'Display Participants',
          'title' => 'Display Participants',
            'style' => 'margin-left:5px'
          );
        $blacklist = array(
          'src' => 'images/trafficred.png',
          'alt' => 'Blacklist Control',
          'title' => 'BlackList Control',
            'style' => 'margin-left:1px',
            'style' => 'margin-right:1px'
          );
        $globalsettings = array(
          'src' => 'images/token_manage.png',
          'alt' => 'Global Participant Settings',
          'title' => 'Global Participant Settings',
          'style' => 'margin-left:5px',
          'style' => 'margin-right:1px'
          );
        $attributecontrol = array(
          'src' => 'images/tag_green.png',
          'alt' => 'Attribute Control',
          'title' => 'Attribute Control',
          'width' => 50,
          'height' => 35,
           'style' => 'margin-left:0px',
            'style' => 'margin-right:1px'
          );
        $sharepanel = array(
          'src' => 'images/share.png',
          'alt' => 'Share Panel',
          'title' => 'Share Panel',
          'height' => 35,
          'width'=> 35,
          'style' => 'margin-left:5px'
          );
        $seperator = array(
          'src' => 'images/seperator.gif',
          'alt' => '',
          'title' => ''
          );
        $ajaxloader = array(
          'src' => 'images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );
        $participantPanel = "<div class='menubar-main'>";
        $participantPanel .= "<div class='menubar-left'>";
        
        $participantPanel.= anchor(site_url("admin"),img($home));
        $participantPanel.= anchor('admin/participants/index',img($information));
        $participantPanel.= anchor('admin/participants/displayParticipants',img($display));
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/importCSV',img($import));
        $participantPanel.= img($export);
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/blacklistControl',img($blacklist));
        if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
        {
        $participantPanel.= anchor('admin/participants/userControl',img($globalsettings));
        }
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/attributeControl',img($attributecontrol));
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/sharePanel', img($sharepanel));
        
        $participantPanel.= "</div>";
        $participantPanel.= "</div>";
        $participantPanel.= "<div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>";
        $participantPanel.= img($ajaxloader);
        $participantPanel.= "</div>";
        $participantPanel.= "<div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>";
        $participantPanel.= $clang->gT("There are no participants to be exported");
        $participantPanel.= "</div>";

        
        echo $participantPanel;
    ?>
</div>
    </body>
</html>
