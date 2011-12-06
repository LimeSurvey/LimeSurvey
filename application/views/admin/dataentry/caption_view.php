<div class='header ui-widget-header'><?php echo $clang->gT("Data entry"); ?></div>
    
            <form action='<?php echo $this->createUrl('admin/dataentry/sa/insert'); ?>' enctype='multipart/form-data' name='addsurvey' method='post' id='addsurvey'>
            <table class='data-entry-tbl' cellspacing='0'>
            <tr>
            <td colspan='3' align='center'>
            <strong><?php echo $thissurvey['name']; ?></strong>
            <br /><?php echo FlattenText($thissurvey['description'],true); ?>
            </td>
            </tr>
    
            <tr class='data-entry-separator'><td colspan='3'></td></tr>
    
            <?php if (count(GetAdditionalLanguagesFromSurveyID($surveyid))>0)
            { ?>
                <tr>
                <td colspan='3' align='center'>
                <?php echo $langlistbox; ?>
                </td>
                </tr>
    
                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php }
    
            if (tableExists('{{tokens_'.$thissurvey['sid'].'}}')) //Give entry field for token id
            { ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><font color='red'>*</font><strong><?php echo $blang->gT("Token"); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' id='token' name='token' onkeyup='activateSubmit(this);' />
                </td>
                </tr>
    
                <tr class='data-entry-separator'><td colspan='3'></td></tr>
    
                
                <script type="text/javascript"><!-- 
                function activateSubmit(me)
                {
                    if (me.value != '')
                    {
                        document.getElementById('submitdata').disabled = false;
                    }
                    else
                    {
                        document.getElementById('submitdata').disabled = true;
                    }
                }
                //--></script>
            <?php } 
    
        
            if ($thissurvey['datestamp'] == "Y") //Give datestampentry field
            { 
                $localtimedate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')); ?> 
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><strong>
                <?php echo $blang->gT("Datestamp"); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' name='datestamp' value='<?php echo $localtimedate; ?>' />
                </td>
                </tr>
    
                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php }
    
            if ($thissurvey['ipaddr'] == "Y") //Give ipaddress field
            { ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><strong>
                <?php echo $blang->gT("IP address"); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' name='ipaddr' value='NULL' />
                </td>
                </tr>
    
                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php } ?>