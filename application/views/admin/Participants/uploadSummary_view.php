<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <script src="<?php echo site_url("scripts/jquery/jquery.js")?>" type="text/javascript"></script>
    </head>
    <body>
        <?php
           $uploadSummary = "<div class='header ui-widget-header'>".$clang->gT("CPDB CSV Summary")."</div><div class='messagebox ui-corner-all'>";
            if($this->session->flashdata('errorinupload') == "") {
            $uploadSummary .= "<div class='successheader'>".$clang->gT('Uploaded CSV file successfully')."</div>";
            if($this->session->flashdata('imported') !=0) {
            $uploadSummary .= "<div class='successheader'>".$clang->gT('Successfully created token entries')."</div>";
             } else {
            $uploadSummary .= "<div class='warningheader'>".$clang->gT("Failed to create token entries")."</div>";
            }
           $uploadSummary .= "<ul><li>".sprintf($clang->gT("%s records in CSV"),$this->session->flashdata('recordcount'))."</li><li>".sprintf($clang->gT("%s records met minumum requirements"),$this->session->flashdata('mincriteria'))."</li><li>".sprintf($clang->gT("%s records have blank madatory fields"),$this->session->flashdata('mandatory'))."</li><li>".sprintf($clang->gT("%s records imported"),$this->session->flashdata('imported'))."</li></ul></ul>";
            if ($this->session->flashdata('dupcount')>0 || count($this->session->flashdata('invalidemaillist'))>0 || count($this->session->flashdata('invalidattribute'))>0)
            {
            $uploadSummary .= "<div class='warningheader'>".$clang->gT('Warnings')."</div><ul><li>";
            $uploadSummary .= sprintf($clang->gT("%s duplicate records removed"),$this->session->flashdata('dupcount'));
            $uploadSummary .= "<a href='#' onclick='$(\"#duplicateslist\").toggle();'>".$clang->gT('List')."</a>";
            $uploadSummary .= "<div class='badtokenlist' id='duplicateslist' style='display: none;'><ul>";
            foreach($this->session->flashdata('duplicatelist') as $data) 
              {
                $uploadSummary .= "<li>".$data."</li>";
              } 
            }
            $uploadSummary .= "</li>";
            if (count($this->session->flashdata('invalidmaillist'))>0)
            {
            $uploadSummary .= "<li>".sprintf($clang->gT("%s records with invalid email address removed"),count($this->session->flashdata('invalidemaillist')));
            $uploadSummary .= "<a href='#' onclick='$(\"#invalidemaillist\").toggle();'>".$clang->gT('List')."</a>";
            $uploadSummary .= "<div class='badtokenlist' id='invalidemaillist' style='display: none;'><ul>";
               foreach($this->session->flashdata('invalidmaillist') as $data) 
              {
            $uploadSummary.= "<li>".$data."</li>";
              }
            }
           $uploadSummary .= "</ul></div></li>";
          if (count($this->session->flashdata('invalidattribute'))>0) 
              {
            $uploadSummary .="<li>".sprintf($clang->gT("%s records have incomplete or wrong attribute values"),count($this->session->flashdata('invalidattribute')));
            $uploadSummary .="<a href='' onclick=\'$(\"#invalidattributelist\").toggle();'>'".$clang->gT('List')."</a>";
            $uploadSummary .="<div class='badtokenlist' id='invalidattributelist' style='display: none;'><ul>";
                foreach($this->session->flashdata('invalidattribute') as $data) 
                    {
                        $uploadSummary.= "<li>".$data."</li>";
                    } }
                $uploadSummary .= "</ul></div></li></ul></div>";
           }
           else
           { 
                $uploadSummary .= "<div class='warningheader'>".$this->session->flashdata('errorinupload')."</div>";
          }
          echo $uploadSummary;
          ?>
         
    </body>
</html>
