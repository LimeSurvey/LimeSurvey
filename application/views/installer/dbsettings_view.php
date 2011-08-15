<?php $this->load->view("installer/header_view",array('progressValue' => $progressValue)); ?>
<div class="container_6">
<div class="grid_2 table">
<p class="title"> &nbsp;Progress</p>
<p> &nbsp;<?php echo $progressValue ; ?>% Completed</p>
<div style="width: 320px; height: 20px; margin-left: 6px;" id="progressbar"></div>
<br />
<div id="steps">
<table class="grid_2" >
<tr class="<?php echo $classesForStep[0]; ?>">
<td>1: License</td>
</tr>
<tr class="<?php echo $classesForStep[1]; ?>">
<td>2: Pre-installation check</td>
</tr>
<tr class="<?php echo $classesForStep[2]; ?>">
<td>3: Configuration </td>
</tr>
<tr class="<?php echo $classesForStep[3]; ?>">
<td>4: Database settings </td>
</tr>
<tr class="<?php echo $classesForStep[4]; ?>">
<td>5: Optional settings</td>
</tr>
</table>
</div>




</div>
<div class="grid_4 table">

<p class="title">&nbsp;<?php echo $title; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px;" >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
<br />
<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>
    <tr bgcolor='#dedede'>
        <td colspan='2' height='4'><font size='1' face='verdana' color='black'>
            <strong>LimeSurvey Setup</strong></font>
        </td>
    </tr>
<?php if (isset($adminoutputText))echo $adminoutputText; ?>
        </td>
    </tr>
</table><br />
</div>
</div>
</div>
<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<table style="font-size:11px; width: 694px;">
<tbody>
<tr>
<td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/1"); ?>', '_top')" /></td>
<td align="center" style="width: 227px;"></td>
<td align="right" style="width: 227px;"><?php if (isset($adminoutputForm)) echo $adminoutputForm; ?></td>
</tr>
</tbody>
</table>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>