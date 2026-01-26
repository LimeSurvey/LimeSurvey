<!--
    dataentry/caption_view.php
-->
<div class="side-body">
    <h3><?php eT("Data entry"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <!-- Survey name and description -->
            <div class="jumbotron ">
            <h2><?php echo stripJavaScript($thissurvey['name']); ?></h2>
            <p><?php echo flattenText($thissurvey['description'],true); ?></p>
            </div>
        <?php echo CHtml::form(array("admin/dataentry/sa/insert"), 'post', array('name'=>'addsurvey', 'id'=>'addsurvey', 'enctype'=>'multipart/form-data'));?>
            <?php if (count(Survey::model()->findByPk($surveyid)->additionalLanguages)>0):?>
                <div class="col-3">
                    <?php echo $langlistbox; ?>
                </div>
            <?php endif; ?>

            <table class='data-entry-tbl table'>

                <tr class='data-entry-separator'>
                    <td colspan='3'></td>
                </tr>

            <?php if ($oSurvey->hasTokensTable) //Give entry field for token id
            { ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><font color='red'>*</font>
                    <strong><?php echo gT("Access code",'html',$sDataEntryLanguage); ?>:</strong>
                </td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' id='token' name='token' oninput='activateSubmit(this);' />
                </td>
                </tr>

                <tr class='data-entry-separator'><td colspan='3'>
                <script type="text/javascript"><!--
                function activateSubmit(me)
                {
                    if (me && me.value != '')
                    {
                        $('#submitdata').removeAttr('disabled');
                        $('#save-button').removeAttr('disabled');
                        $('#save-and-close-button').removeAttr('disabled');
                    }
                    else
                    {
                        $('#submitdata').attr('disabled', 'disabled');
                        $('#save-button').attr('disabled', 'disabled');
                        $('#save-and-close-button').attr('disabled', 'disabled');
                    }
                }
                //--></script>
                </td></tr>
            <?php }


            if ($thissurvey['datestamp'] == "Y") //Give datestampentry field
            {
                $localtimedate=dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig('timeadjust')); ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><strong>
                <?php echo gT("Datestamp",'html',$sDataEntryLanguage); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' name='datestamp' value='<?php echo $localtimedate; ?>' />
                </td>
                </tr>

                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php }

            if ($thissurvey['savequotaexit'] == "Y") //Give quota exit field
            { ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><strong>
                <?php echo gT("Quota exit",'html',$sDataEntryLanguage); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' name='quota_exit' value='NULL' />
                </td>
                </tr>

                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php }

            if ($thissurvey['ipaddr'] == "Y") //Give ipaddress field
            { ?>
                <tr>
                <td valign='top' width='1%'></td>
                <td valign='top' align='right' width='30%'><strong>
                <?php echo gT("IP address",'html',$sDataEntryLanguage); ?>:</strong></td>
                <td valign='top'  align='left' style='padding-left: 20px'>
                <input type='text' name='ipaddr' value='NULL' />
                </td>
                </tr>

                <tr class='data-entry-separator'><td colspan='3'></td></tr>
            <?php } ?>
