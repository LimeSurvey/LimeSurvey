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


<div style="-moz-border-radius:15px; border-radius:15px; " >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
&nbsp;<?php echo $errorConnection ; ?>
<!-- <form action="<?php echo base_url()."index.php/installer/process" ; ?>" method="post"> -->
<!-- <?php echo validation_errors(); ?> -->
<br /><b>&nbsp; Note: All fields marked with (*) are required.</b>
<br />
<!--
<fieldset class="content-table">
<legend class="content-table-heading">Do It Yourself</legend>
If you are using MS SQL Server as your database system, <b>create an empty database</b> and, if needed, a new user.

</fieldset>
-->
<fieldset class="content-table">
<legend class="content-table-heading">Database configuration</legend>
<table style="width: 672px;">
<?php echo form_open('installer/install/1'); ?>
<tr>
<td style="width: 428px;"> <b>Database type * </b><br />
<div class="description-field">This is the database type. </div>
</td>
<td style="width: 224px;" align="right">
<select name="dbType" style="width: 147px;">
<option value="mysqli" <?php echo set_select('dbType', 'mysqli',TRUE); ?>  >mysqli</option>
<option value="mysql" <?php echo set_select('dbType', 'mysql'); ?> >mysql</option>
<option value="mssql" <?php echo set_select('dbType', 'mssql'); ?>  >mssql</option>
<option value="postgres" <?php echo set_select('dbType', 'postgre'); ?>  >postgres</option>
</select>
</td>
</tr>
<tr>
<td style="width: 428px;"> <b>Database Location *</b> <br />
<div class="description-field">Set this to the IP/net location of your database server. In most cases "<b>localhost</b>" will work. </div>
</td>
<td style="width: 224px;" align="right"><input name="dbLocation" value="<?php echo set_value('dbLocation'); ?>" type="text" /><?php echo "<br/>".form_error('dbLocation'); ?></td>
</tr>
<tr>
<td style="width: 428px;"> <b>Database Name *</b> <br />
<div class="description-field">If you provide an existing database name make sure the database does not contain old tables of LimeSurvey.</div>
</td>
<td style="width: 224px;" align="right"><input name="dbName" value="<?php echo set_value('dbName'); ?>" type="text" /><?php echo "<br/>".form_error('dbName'); ?></td>
</tr>
<tr>
<td style="width: 428px;"> <b>Database User *</b> <br />
<div class="description-field">Your Database server user name. In most cases "<b>root</b>" will work.</div>
</td>
<td style="width: 224px;" align="right"><input name="dbUser" value="<?php echo set_value('dbUser'); ?>" type="text" /><?php echo "<br/>".form_error('dbUser'); ?></td>
</tr>
<tr>
<td style="width: 428px;"> <b>Database Password</b> <br />
<div class="description-field">Your Database server password.</div>
</td>
<td style="width: 224px;" align="right"><input name="dbPwd" value="" type="password" /><?php echo "<br/>".form_error('dbPwd'); ?></td>
</tr>
<tr>
<td style="width: 428px;"> <b>Confirm Password</b> <br />
<div class="description-field">Confirm your database server password.</div>
</td>
<td style="width: 224px;" align="right"><input name="dbConfirmPwd" value="" type="password" /><?php echo "<br/>".form_error('dbConfirmPwd'); ?></td>
</tr>
<tr>
<td style="width: 428px;"> <b>Database Prefix</b> <br />
<div class="description-field">If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.</div>
</td>
<td style="width: 224px;" align="right"><input name="dbPrefix" value="<?php echo set_value('dbPrefix','lime_'); ?>" type="text" /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>

<td colspan="2"> <div class="demo"><table style="width: 662px; background: #ffffff;">
<tbody>
<tr>
<td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/0"); ?>', '_top')" /></td>
<td align="center" style="width: 227px;"></td>
<td align="right" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="Next" /></td>
</tr>
</tbody>
</table></div></td>
</tr>


</table>

</form>
</fieldset>


</div>
</div>
</div>

<!--<div class="container_5">
<div class="grid_2">&nbsp;</div>
<div class="grid_3 demo">
<br/>
<table style="width: 570px;">
<tbody>
<tr>
<td align="left" style="width: 190px;"><a href="<?php echo base_url(); ?>index.php/installer/install/0"> Previous </a></td>
<td align="center" style="width: 190px;"></td>
<td align="right" style="width: 190px;"><input type="submit" value="Next" /></td>
</tr>
</tbody>
</table>
</div>
</div> -->
<?php $this->load->view("installer/footer_view"); ?>