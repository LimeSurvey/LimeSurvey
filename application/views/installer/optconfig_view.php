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
&nbsp;<?php echo $confirmation; ?><br />
&nbsp;You can leave these settings blank and change them later
<br />

<form action="<?php echo base_url().'index.php/installer/optional'; ?>" method="post">
<fieldset class="content-table">
<legend class="content-table-heading">Optional settings</legend>
<table style="width: 640px;">
<tr>
<td><b>Admin Login Name</b><br />
<div class="description-field">This will be the userid by which admin of board will login.</div>
</td>
<td align="right"><input type="text" name="adminLoginName"/></td>
</tr>
<tr>
<td><b>Admin Login Password</b><br />
<div class="description-field">This will be the password of admin user.</div>
</td>
<td align="right" ><input type="password" name="adminLoginPwd"/></td>
</tr>
<tr>
<td><b>Confirm Password</b><br />
<div class="description-field">Confirm your admin password.</div>
</td>
<td align="right"><input type="password" name="confirmPwd"/></td>
</tr>
<tr>
<td><b>Site Name </b><br />
<div class="description-field">This name will appear in the survey list overview and in the administration header.</div>
</td>
<td align="right"><input type="text" name="siteName"/></td>
</tr>
<tr>
<td><b>Admin Email</b><br />
<div class="description-field">This is the default email address of the site administrator and used for system messages and contact options.</div>
</td>
<td align="right"><input type="text" name="adminEmail"/></td>
</tr>
<tr>
<td><b>Default Language</b><br />
<div class="description-field">This will be your default language. Select "<b>en</b>" without double quotes for english.</div>
</td>
<td align="right">

<select id='surveylang' name='surveylang' style='width:156px;'>

<?php
			
			foreach (getlanguagedata(true,true) as $langkey=>$languagekind)
            {
				if ($langkey=="en")
				{
					?>
					<option value='<?php echo $langkey; ?>' selected='yes'><?php echo $languagekind['nativedescription']." - ".$languagekind['description']; ?></option>
					<?php
				}
				else
				{
                ?>
                <option value='<?php echo $langkey; ?>'><?php echo $languagekind['nativedescription']." - ".$languagekind['description']; ?></option>
                <?php
            }
				
            }
?>
</select>
<!--<input type="text" name="surveylang" value="en"/>  -->
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan="2"> <div class="demo"><table style="font-size:11px; width: 640px; background: #ffffff;">
<tbody>
<tr>
<td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="Previous" onclick="javascript: window.open('<?php echo site_url("installer/install/license"); ?>', '_top')" /></td>
<td align="center" style="width: 227px;"></td>
<td align="right" style="width: 227px;"><input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type="submit" value="Next" /></td>
</tr>
</tbody>
</table></td>
</tr>
</table>
</fieldset>
</form>

</div>
</div>
</div>
<!--
<div class="container_5">
<div class="grid_2">&nbsp;</div>
<div class="grid_3 demo">
<br/>
<table style="width: 570px;">
<tbody>
<tr>
<td align="left" style="width: 190px;"><a href="<?php echo base_url(); ?>index.php/installer/install/license"> Previous </a></td>
<td align="center" style="width: 190px;"></td>
<td align="right" style="width: 190px;"><a href="<?php echo base_url(); ?>index.php/installer/install/0"> Next </a></td>
</tr>
</tbody>
</table>
</div>
</div> -->
<?php $this->load->view("installer/footer_view"); ?>