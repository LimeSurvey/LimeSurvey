<?php $this->load->view("installer/header_view",array('progressValue' => $progressValue)); ?>

<?php echo form_open('installer/install/1'); ?>

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

<div class="grid_4 table">

<p class="title">&nbsp;<?php echo $title; ?></p>

<div style="-moz-border-radius:15px; border-radius:15px; " >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
&nbsp;<?php echo $errorConnection ; ?>
<br /><b>&nbsp; Note: All fields marked with (*) are required.</b>
<br />


 <fieldset class="content-table">
  <legend class="content-table-heading">Database configuration</legend>
  <table style="width: 672px;">
   <tr>
    <td style="width: 428px;">
     <b>Database type * </b><br />
     <div class="description-field">This is the database type. </div>
    </td>
    <td style="width: 224px;" align="right">
     <select name="dbType" style="width: 147px;">
      <option value="mysqli" <?php echo set_select('dbType', 'mysqli',TRUE); ?>  >MySQL</option>
      <option value="mysql" <?php echo set_select('dbType', 'mysql'); ?> >MySQL (old driver)</option>
      <option value="mssql" <?php echo set_select('dbType', 'mssql'); ?>  >Microsoft SQL Server</option>
      <option value="postgre" <?php echo set_select('dbType', 'postgre'); ?>  >PostgreSQL</option>
     </select>
    </td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b>Database Location *</b> <br />
      <div class="description-field">Set this to the IP/net location of your database server. In most cases "<b>localhost</b>" will work. </div>
     </td>
     <td style="width: 224px;" align="right"><input name="dbLocation" value="<?php echo set_value('dbLocation'); ?>" type="text" /><?php echo "<br/>".form_error('dbLocation'); ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b>Database Name *</b> <br />
      <div class="description-field">If you provide an existing database name make sure the database does not contain old tables of LimeSurvey.</div>
     </td>
     <td style="width: 224px;" align="right"><input name="dbName" value="<?php echo set_value('dbName'); ?>" type="text" /><?php echo "<br/>".form_error('dbName'); ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
      <b>Database User *</b> <br />
      <div class="description-field">Your Database server user name. In most cases "<b>root</b>" will work.</div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbUser" value="<?php echo set_value('dbUser'); ?>" type="text" /><?php echo "<br/>".form_error('dbUser'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b>Database Password</b> <br />
     <div class="description-field">Your Database server password.</div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbPwd" value="" type="password" /><?php echo "<br/>".form_error('dbPwd'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b>Confirm Password</b> <br />
     <div class="description-field">Confirm your database server password.</div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbConfirmPwd" value="" type="password" /><?php echo "<br/>".form_error('dbConfirmPwd'); ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
     <b>Database Prefix</b> <br />
     <div class="description-field">If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.</div>
    </td>
    <td style="width: 224px;" align="right"><input name="dbPrefix" value="<?php echo set_value('dbPrefix','lime_'); ?>" type="text" /></td>
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
    <td align="left" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/0"); ?>', '_top')" /></td>
    <td align="center" style="width: 34%;"></td>
    <td align="right" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="Next" /></td>
   </tr>
  </tbody>
 </table>
</div>
</div>

</form>

<?php $this->load->view("installer/footer_view"); ?>