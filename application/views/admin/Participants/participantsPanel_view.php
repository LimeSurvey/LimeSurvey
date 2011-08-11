
<html>
    <head>

        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <title></title>
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
        $user = array(
          'src' => 'images/tokens.png',
          'alt' => 'Global Participant Settings',
          'title' => 'Global Participant Settings',
          'style' => 'margin-left:5px',
          'style' => 'margin-right:1px'
          );
        $attributecontrol = array(
          'src' => 'images/token_manage.png',
          'alt' => 'Attribute Control',
          'title' => 'Attribute Control',
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
        
        $participantPanel = "<div class='menubar-main'>";
        $participantPanel .= "<div class='menubar-left'>";
        
        $participantPanel.= anchor(site_url("admin"),img($home));
        $participantPanel.= anchor('admin/participants/index',img($information));
        $participantPanel.= anchor('admin/participants/displayParticipants',img($display));
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/importCSV',img($import));
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/blacklistControl',img($blacklist));
        if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
        {
        $participantPanel.= anchor('admin/participants/userControl',img($user));
        }
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/attributeControl',img($attributecontrol));
        $participantPanel .= img($seperator);
        $participantPanel.= anchor('admin/participants/sharePanel', img($sharepanel));
        
        $participantPanel.= "</div>";
        $participantPanel.= "</div>";
        echo $participantPanel;
    ?>
</div>
    </body>
</html>
