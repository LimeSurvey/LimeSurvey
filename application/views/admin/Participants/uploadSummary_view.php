<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/adminstyle.css" ?>" />
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('adminscripts')."uploadsummary.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">var redUrl = "<?php echo site_url("admin/participants/displayParticipants");?>";</script>
    </head>
    <body>
        <?php
            $uploadSummary = "<div class='header ui-widget-header'>".$clang->gT("CPDB CSV Summary")."</div><div class='messagebox ui-corner-all'>";
            if(empty($this->session->userdata['errorinupload'])) 
            {
                $uploadSummary .= "<div class='successheader'>".$clang->gT('Uploaded CSV file successfully')."</div>";
                if($this->session->userdata['imported'] !=0) {
                $uploadSummary .= "<div class='successheader'>".$clang->gT('Successfully created CPDB entries')."</div>";
                } else {
                $uploadSummary .= "<div class='warningheader'>".$clang->gT("Failed to create token entries")."</div>";
                }
                if(!empty($this->session->userdata['recordcount']))
                {
                    $uploadSummary .= "<ul><li>".sprintf($clang->gT("%s records in CSV"),$this->session->userdata['recordcount'])."</li>";
                }
                if(!empty($this->session->userdata['mandatory']))
                {
                    $uploadSummary .= "<li>".sprintf($clang->gT("%s records have blank madatory fields"),$this->session->userdata['mandatory'])."</li>";
                }
                $uploadSummary .= "<li>".sprintf($clang->gT("%s records met minumum requirements"),$this->session->userdata['mincriteria'])."</li>";
                $uploadSummary .= "<li>".sprintf($clang->gT("%s records imported"),$this->session->userdata['imported'])."</li></ul>";
                if(count($this->session->userdata['duplicatelist'])> 0 || count($this->session->userdata['invalidemaillist'])>0 || count($this->session->userdata['invalidattribute'])>0)
                {   
                    $uploadSummary .= "<div class='warningheader'>".$clang->gT('Warnings')."</div><ul>";
                    if (!empty($this->session->userdata['duplicatelist']) && (count($this->session->userdata['duplicatelist']) >0)) 
                    {
                        $uploadSummary .= "<li>".sprintf($clang->gT("%s duplicate records removed"),count($this->session->userdata['duplicatelist']));
                        $uploadSummary .= "<div class='badtokenlist' id='duplicateslist' ><ul>";
                        foreach($this->session->userdata['duplicatelist'] as $data) 
                        {
                            $uploadSummary .= "<li>".$data."</li>";
                        } 
                        $uploadSummary .= "</ul></div></li>";
                    }
                    if ((!empty($this->session->userdata['invalidemaillist'])) && (count($this->session->userdata['invalidemaillist']) >0))
                    {
                        $uploadSummary .= "<li>".sprintf($clang->gT("%s records with invalid email address removed"),count($this->session->userdata['invalidemaillist']));
                        $uploadSummary .= "<div class='badtokenlist' id='invalidemaillist'><ul>";
                        foreach($this->session->userdata['invalidemaillist'] as $data) 
                        {
                            $uploadSummary.= "<li>".$data."</li>";
                        }
                        $uploadSummary .= "</ul></div></li>";
                    }
                    if((!empty($this->session->userdata['invalidattribute'])) &&(count($this->session->userdata['invalidattribute'])>0)) 
                    {
                        $uploadSummary .="<li>".sprintf($clang->gT("%s records have incomplete or wrong attribute values"),count($this->session->userdata['invalidattribute']));
                        $uploadSummary .="<div class='badtokenlist' id='invalidattributelist' ><ul>";
                        foreach($this->session->userdata['invalidattribute'] as $data) 
                        {
                            $uploadSummary.= "<li>".$data."</li>";
                        } 
                    }
                    $uploadSummary .= "</ul></div></li></ul><p><input type='button' name='pppanel' id='pppanel' value='View uploaded records' /></p></div>";
           }
            }
           else
            { 
               echo $this->session->userdata['errorinupload']['error'];
                $uploadSummary .= "<div class='warningheader'>".$this->session->userdata['errorinupload']['error']."</div>";
            }
          
          echo $uploadSummary;
          
          ?>
         
    </body>
</html>
