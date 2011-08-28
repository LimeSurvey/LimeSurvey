<?php $this->load->view("installer/header_view",array('progressValue' => $progressValue)); ?>

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

<div class="grid_4 table">

<p class="title">&nbsp;<?php echo "$title"; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px; " >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
<fieldset class="content-table">
<legend class="content-table-heading">Basic settings</legend>
<table style="width: 671px; margin-top: 0px; border-top-width: 1px; ">
<tr>
<td  style="width: 209px;">Required settings</td>
<td align="center" style="width: 225px;"><b>Recommended setting</b></td>
<td align="center" style="width: 225px;"><b>Current setting</b></td>
</tr>
<tr>
<td style="width: 209px;">PHP version</td>
<td align="center" style="width: 225px;">5.1.6 or later</td>
<td align="center" style="width: 225px;"><?php if (isset($verror) && $verror) { ?><span style='font-weight:bold; color: red'>Outdated: <?php echo $phpVersion; ?></span></b>
<?php } else { ?><?php echo $phpVersion ; ?> <?php } ?></td>
</tr>
<tr>
<td style="width: 209px;">mbstring library</td>
<td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
<td align="center" style="width: 225px;"><?php echo $mbstringPresent ; ?></td>
</tr>
<tr>
<td style="width: 209px;">Root directory</td>
<td align="center" style="width: 225px;">Found,Writable</td>
<td align="center" style="width: 225px;"><?php if (isset($derror) && $derror) { ?><b><font color="red"><?php echo $directoryPresent ; if ($directoryWritable) echo ",$directoryWritable" ; ?></font></b>
<?php } else { ?><?php echo $directoryPresent ; if ($directoryWritable) echo ",$directoryWritable" ; ?> <?php } ?></td>
</tr>
<tr>
<td style="width: 209px;">/tmp directory</td>
<td align="center" style="width: 225px;">Found,Writable</td>
<td align="center" style="width: 225px;"><?php if (isset($terror) && $terror) { ?><b><font color="red"><?php echo $tmpdirPresent ; if ($tmpdirWritable) echo ",$tmpdirWritable" ; ?></font></b>
<?php } else { ?><?php echo $tmpdirPresent ; if ($tmpdirWritable) echo ",$tmpdirWritable" ; ?><?php } ?></td>
</tr>
<tr>
<td style="width: 209px;">/upload directory</td>
<td align="center" style="width: 225px;">Found,Writable</td>
<td align="center" style="width: 225px;"><?php if (isset($uerror) && $uerror) { ?><b><font color="red"><?php echo $uploaddirPresent ; if ($uploaddirWritable) echo ",$uploaddirWritable" ; ?></font></b>
<?php } else { ?><?php echo $uploaddirPresent ; if ($uploaddirWritable) echo ",$uploaddirWritable" ; ?><?php } ?></td>
</tr>
<tr>
<td style="width: 209px;">/template directory</td>
<td align="center" style="width: 225px;">Found,Writable</td>
<td align="center" style="width: 225px;"><?php if (isset($tperror) && $tperror) { ?><b><font color="red"><?php echo $templatedirPresent ; if ($templatedirWritable) echo ",$templatedirWritable" ; ?></font></b>
<?php } else { ?><?php echo $templatedirPresent ; if ($templatedirWritable) echo ",$templatedirWritable" ; ?><?php } ?></td>
</tr>

</table>
</fieldset>
<fieldset class="content-table">
<legend class="content-table-heading">Optional settings</legend>
<table style="width: 671px; margin-top: 0px; border-top-width: 1px;" >
<tr>
<td style="width: 209px;">&nbsp;</td>
<td align="center" style="width: 225px;"><b>Reccommended settings</b></td>
<td align="center" style="width: 225px;"><b>Current settings</b></td>
</tr>
<tr>
<td style="width: 209px;">GD library</td>
<td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
<td align="center" style="width: 225px;"><?php echo $gdPresent ; ?></td>
</tr>
<tr>
<td style="width: 209px;">LDAP library</td>
<td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
<td align="center" style="width: 225px;"><?php echo $ldapPresent ; ?></td>
</tr>
<tr>
<td style="width: 209px;">PHP zip library</td>
<td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
<td align="center" style="width: 225px;"><?php echo $zipPresent ; ?></td>
</tr>
<tr>
<td style="width: 209px;">PHP zlib library</td>
<td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
<td align="center" style="width: 225px;"><?php echo $zlibPresent ; ?></td>
</tr>

</table>
</fieldset>
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
<td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/license"); ?>', '_top')" /></td>
<td align="center" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Check again" onclick="javascript: window.open('<?php echo site_url("installer/install/0"); ?>', '_top')" /></td>
<td align="right" style="width: 227px;">
<?php if (isset($next) && $next== TRUE) { ?>
<input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Next" onclick="javascript: window.open('<?php echo site_url("installer/install/1"); ?>', '_top')" />
<?php } ?>

</td>
</tr>
</tbody>
</table>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>