<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view",array('progressValue' => $progressValue));
?>

<?php echo form_open('installer/install/1'); ?>

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

<div class="grid_4 table">

<p class="title"><?php echo $title; ?></p>

<div style="-moz-border-radius:15px; border-radius:15px; " >
<p><?php echo $descp; ?></p>
<hr />
<center style="color: red; font-size:12px;"><?php echo $errorConnection ; ?></center>
<br /><?php echo $clang->gT("Note: All fields marked with (*) are required."); ?>
<br />


 <fieldset class="content-table">
  <legend class="content-table-heading"><?php echo $clang->gT("Database configuration"); ?></legend>
  <table style="width: 672px; font-size:14px;">
   <tr>
    <td style="width: 428px;">
     <b><?php echo $clang->gT("Database type"); ?> * </b><br />
     <div class="description-field"><?php echo $clang->gT("This is the database type."); ?> </div>
    </td>
    <td style="width: 224px;" align="right">
     <select name="dbtype" style="width: 147px;">
      <option value="mysqli" <?php echo set_select('dbtype', 'mysqli',TRUE); ?>  >MySQL</option>
      <option value="mysql" <?php echo set_select('dbtype', 'mysql'); ?> >MySQL (old driver)</option>
      <option value="mssql" <?php echo set_select('dbtype', 'mssql'); ?>  >Microsoft SQL Server</option>
      <option value="postgre" <?php echo set_select('dbtype', 'postgres'); ?>  >PostgreSQL</option>
     </select>
    </td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b><?php echo $clang->gT("Database Location"); ?> *</b> <br />
      <div class="description-field"><?php echo $clang->gT('Set this to the IP/net location of your database server. In most cases "localhost" will work.'); ?> </div>
     </td>
     <td style="width: 224px;" align="right"><input name="dblocation" value="<?php echo set_value('dblocation'); ?>" type="text" /><?php echo "<br/>".form_error('dblocation'); ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b><?php echo $clang->gT("Database Name"); ?> *</b> <br />
      <div class="description-field"><?php echo $clang->gT("If you provide an existing database name make sure the database does not contain old tables of LimeSurvey."); ?></div>
     </td>
     <td style="width: 224px;" align="right"><input name="dbname" value="<?php echo set_value('dbname'); ?>" type="text" /><?php echo "<br/>".form_error('dbname'); ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b><?php echo $clang->gT("Database User"); ?> *</b> <br />
      <div class="description-field"><?php echo $clang->gT('Your Database server user name. In most cases "root" will work.'); ?></div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbuser" value="<?php echo set_value('dbuser'); ?>" type="text" /><?php echo "<br/>".form_error('dbuser'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b><?php echo $clang->gT("Database Password"); ?></b> <br />
     <div class="description-field"><?php echo $clang->gT("Your Database server password."); ?></div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbpwd" value="" type="password" /><?php echo "<br/>".form_error('dbpwd'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b><?php echo $clang->gT("Confirm Password"); ?></b> <br />
     <div class="description-field"><?php echo $clang->gT("Confirm your database server password."); ?></div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbconfirmpwd" value="" type="password" /><?php echo "<br/>".form_error('dbconfirmpwd'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b><?php echo $clang->gT("Database Prefix"); ?></b> <br />
     <div class="description-field"><?php echo $clang->gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.'); ?></div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbprefix" value="<?php echo set_value('dbprefix','lime_'); ?>" type="text" /></td>
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
 <table style="width: 694px; background: #ffffff;">
  <tbody>
   <tr>
    <td align="left" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/0"); ?>', '_top')" /></td>
    <td align="center" style="width: 34%;"></td>
    <td align="right" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="<?php echo $clang->gT("Next"); ?>" /></td>
   </tr>
  </tbody>
 </table>
</div>
</div>

</form>

<?php $this->load->view("installer/footer_view"); ?>