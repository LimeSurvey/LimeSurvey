<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <script src="<?php echo site_url("scripts/admin/userControl.js")?>" type="text/javascript"></script>
        <title></title>
    </head>
    <body>
        <?php
        $userControl = "<div class='header ui-widget-header'>";
	$userControl .= "<strong>".$clang->gT("Global Participant Settings")."</strong></div>";
        $userControl .= "<div id='tabs'>";
        $userControl .= "<ul><li><a href='#usercontrol'>User Control</a></li></ul>";
        $userControl .= "<div id='usercontrol-1'>";
	if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
        {
            $attribute = array('class' => 'form44');
            $userControl .= form_open('admin/participants/storeUserControlValues',$attribute);
            $options = array('Y'=>'Yes','N'=> 'No');
            $userControl .= "<ul><li><label for='userideditable'>".$clang->gT('User ID editable : ')."</label>";
            $userControl .= form_dropdown('userideditable', $options,$userideditable);
            $userControl .= "</li>";       
            $userControl .= "<p>";       
            $userControl .= form_submit('submit', 'Submit');
            $userControl .="</p>";
        }
        else
        {  
          $userControl .=   "<div class='messagebox ui-corner-all'>".$clang->gT("You don't have sufficient permissions")."</div>";
        }
        $userControl .= "</div></div>";
         echo $userControl;
  
        ?>
        
    </body>
</html>
