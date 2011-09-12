<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view",array('progressValue' => $progressValue));
?>

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
<?php echo $clang->gT("LimeSurvey tried to delete the following file but couldn't succeed. You will have to remove the file or else you will not be able to log in."); ?><br />
</font><br />
<?php echo $clang->gT("File path:");?> "<?php echo $this->config->item('rootdir').'/tmp/sample_installer_file.txt'; ?>".
<br /><br />
<?php } ?>

<b> <?php echo $clang->gT("Administrator credentials"); ?>:</b><br /><br />
<?php echo $clang->gT("Username"); ?>: <?php echo $user; ?> <br />
<?php echo $clang->gT("Password"); ?>: <?php echo $pwd; ?>
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