<?php $this->load->view("installer/header_view",array('progressValue' => $progressValue)); ?>

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

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
<?php if (isset($adminoutputText)) echo $adminoutputText; ?>
        </td>
    </tr>
</table><br />
</div>
</div>
</div>
<div class="clear"></div>
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