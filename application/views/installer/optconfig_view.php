<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<?php echo CHtml::beginForm($this->createUrl('installer/optional')); ?>

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">

<p class="title"><?php echo $title; ?></p>

<div style="-moz-border-radius:15px; border-radius:15px;" >
<p><?php echo $descp; ?></p>
<hr />
<?php echo $confirmation; ?>
<div style="color:red; font-size:12px;">
	<?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
</div>
<br />
<?php echo $clang->gT("You can leave these settings blank and change them later"); ?>
<br />

<fieldset class="content-table">
<legend class="content-table-heading"><?php echo $clang->gT("Optional settings"); ?></legend>
<table style="width: 640px; font-size:14px;">
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'adminLoginName', array('label' => $clang->gT("Admin login name"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be the userid by which admin of board will login."); ?></div>
</td>
<td align="right"><?php echo CHtml::activeTextField($model, 'adminLoginName'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'adminLoginPwd', array('label' => $clang->gT("Admin login password"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be the password of admin user."); ?></div>
</td>
<td align="right"><?php echo CHtml::activePasswordField($model, 'adminLoginPwd'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'confirmPwd', array('label' => $clang->gT("Confirm your admin password"))); ?></b><br />
</td>
<td align="right"><?php echo CHtml::activePasswordField($model, 'confirmPwd'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'adminName', array('label' => $clang->gT("Administrator name"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This is the default name of the site administrator and used for system messages and contact options."); ?></div>
</td>
<td align="right"><?php echo CHtml::activeTextField($model, 'adminName'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'adminEmail', array('label' => $clang->gT("Administrator email"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This is the default email address of the site administrator and used for system messages, contact options and default bounce email."); ?></div>
</td>
<td align="right"><?php echo CHtml::activeTextField($model, 'adminEmail'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'siteName', array('label' => $clang->gT("Site name"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This name will appear in the survey list overview and in the administration header."); ?></div>
</td>
<td align="right"><?php echo CHtml::activeTextField($model, 'siteName'); ?></td>
</tr>
<tr>
<td><b><?php echo CHtml::activeLabelEx($model, 'surveylang', array('label' => $clang->gT("Default language"))); ?></b><br />
<div class="description-field"><?php echo $clang->gT("This will be your default language."); ?></div>
</td>
<td align="right">
<?php
foreach(getlanguagedata(true, true) as $langkey => $languagekind)
{
	$languages[htmlspecialchars($langkey)] = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
}
echo CHtml::activeDropDownList($model, 'surveylang', $languages, array('style' => 'width: 156px', 'encode' => false, 'en' => array('selected' => true)));
?>
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
    <td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/welcome"); ?>', '_top')" /></td>
    <td align="center" style="width: 227px;"></td>
    <td align="right" style="width: 227px;"><?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')); ?></td>
   </tr>
  </tbody>
 </table>
</div>
</div>


<?php echo CHtml::endForm(); ?>

<?php $this->render("/installer/footer_view"); ?>
