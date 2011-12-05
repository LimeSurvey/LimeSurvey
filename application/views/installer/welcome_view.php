<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<form action="<?php echo $this->createUrl('installer/welcome'); ?>" method="post">

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">

<p class="title"><?php echo $title; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px;" >
<p><?php echo $descp; ?></p>
<hr />

<fieldset class="content-table">
<legend class="content-table-heading">Language Selection</legend>
<table style="width: 640px;">
<tr>
<td><b>Please select your preferred language:</b><br />
<div class="description-field">Your preferred language will be used through out the installation process.</div>
</td>
<td align="right">
<?php
foreach(getlanguagedata(true, true) as $langkey => $languagekind)
{
	$languages[htmlspecialchars($langkey)] = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
}
echo CHtml::dropDownList('installerLang', 'en', $languages, array('style' => 'width: 190px', 'id' => 'installerLang', 'encode' => false));
?>
</td>
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
    <td align="left" style="width: 227px;">&nbsp;</td>
    <td align="center" style="width: 227px;">&nbsp;</td>
    <td align="right" style="width: 227px;"><input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type="submit" value="<?php echo $clang->gT('Start Installation'); ?>" /></td>
   </tr>
  </tbody>
 </table>
</div>
</div>



</form>
<?php $this->render("/installer/footer_view"); ?>
