<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view",array('progressValue' => $progressValue));
?>

<form action="<?php echo $this->config->site_url('installer/optional'); ?>" method="post">

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

<div class="grid_4 table">

<p class="title"><?php echo $title; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px;" >
<p><?php echo $descp; ?></p>
<hr />
<?php echo $confirmation; ?><br />
<?php echo $clang->gT("You can leave these settings blank and change them later"); ?>
<br />

<fieldset class="content-table">
<legend class="content-table-heading"><?php echo $clang->gT("Optional settings"); ?></legend>
<table style="width: 640px; font-size:14px;">
<tr>
<td><b><?php echo $clang->gT("Admin login name"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be the userid by which admin of board will login."); ?></div>
</td>
<td align="right"><input type="text" name="adminLoginName"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Admin login password"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be the password of admin user."); ?></div>
</td>
<td align="right" ><input type="password" name="adminLoginPwd"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Confirm password"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("Confirm your admin password."); ?></div>
</td>
<td align="right"><input type="password" name="confirmPwd"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Administrator name"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This is the default name of the site administrator and used for system messages and contact options."); ?></div>
</td>
<td align="right"><input type="text" name="adminName"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Administrator email"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This is the default email address of the site administrator and used for system messages, contact options and default bounce email."); ?></div>
</td>
<td align="right"><input type="text" name="adminEmail"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Site name"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This name will appear in the survey list overview and in the administration header."); ?></div>
</td>
<td align="right"><input type="text" name="siteName"/></td>
</tr>
<tr>
<td><b><?php echo $clang->gT("Default language"); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be your default language."); ?></div>
</td>
<td align="right">

<select id='surveylang' name='surveylang' style='width:156px;'>
<?php $this->load->view('installer/language_options_view'); ?>
</select>
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
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
 <table style="font-size:11px; width: 694px; background: #ffffff;">
  <tbody>
   <tr>
    <td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/1"); ?>', '_top')" /></td>
    <td align="center" style="width: 227px;"></td>
    <td align="right" style="width: 227px;"><input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type="submit" value="<?php echo $clang->gT("Next"); ?>" /></td>
   </tr>
  </tbody>
 </table>
</div>
</div>



</form>
<?php $this->load->view("installer/footer_view"); ?>
