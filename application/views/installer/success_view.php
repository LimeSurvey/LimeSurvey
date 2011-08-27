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

<?php if (isset($error) && $error) { ?>
<font color="red">
LimeSurvey tried to delete the following file but couldn't succeed. You'll have to do it manually else you won't be able to use the admin board.<br />
This is a security precaution.</font><br />
File path : "<?php echo $this->config->item('rootdir').'/tmp/sample_installer_file.txt'; ?>".
<br /><br />
<?php } ?>



<b>PLEASE REMEMBER TO COMPLETELY
REMOVE THE FOLLOWING DIRECTORY/FILES.
</b><br /><br />
1. Installation directory (<?php echo $this->config->item('rootdir').'/installer'; ?>).<br />
2. Installer script (<?php echo $this->config->item('rootdir').'/application/controllers/installer.php'; ?>).<br /><br />
<b>Optional:</b> <br /><br />
1. Installer view files(<?php echo $this->config->item('rootdir').'/application/views/installer'; ?>). <br /><br />
Press Delete to delete these files or you can do it manually later on.<br /><br />
<b> Administrator credentials :</b><br />
Username : <?php echo $user; ?> <br />
Password : <?php echo $pwd; ?>
<br /><br />
</div>
</div>

</div>
<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<table style="width: 694px;">
<tbody>
<tr>
<td align="left" style="width: 300px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/license"); ?>', '_top')" /></td>
<td align="center" style="width: 800px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Delete" onclick="javascript: window.open('<?php echo site_url("installer/deletefiles"); ?>', '_top')"  /></td>
<td align="right" style="width: 190px;">
<div id="next" style="font-size:11px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="Administration" onclick="javascript: window.open('<?php echo site_url("admin/"); ?>', '_top')" /></div>
</form>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>