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

<b> Administrator credentials :</b><br />
Username : <?php echo $user; ?> <br />
Password : <?php echo $pwd; ?>
<br /><br />
</div>
</div>

<div class="clear"></div>

<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<table style="width: 694px;">
 <tbody>
  <tr>
   <td align="left" style="width: 227px;"></td>
   <td align="right" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="Administration" onclick="javascript: window.open('<?php echo site_url("admin/"); ?>', '_top')" />
    <div id="next" style="font-size:11px;"></div>
   </td>
  </tr>
 </tbody>
</table>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>