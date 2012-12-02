<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<?php echo CHtml::form(array("installer/welcome"), 'post'); ?>

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">

<p class="maintitle"><?php echo $title; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px;" >
<p><?php echo $descp; ?></p>
<hr />

<fieldset class="content-table">
<legend class="content-table-heading"><?php $clang->eT('Language selection'); ?></legend>
<table style="width: 640px;">
<tr>
<td><b><?php $clang->eT('Please select your preferred language:'); ?></b><br />
<div class="description-field"><?php $clang->eT('Your preferred language will be used through out the installation process.'); ?></div>
</td>
<td align="right">
<?php
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
    <td align="right" style="width: 227px;"><input class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' type="submit" value="<?php $clang->eT('Start installation'); ?>" /></td>
   </tr>
  </tbody>
 </table>
</div>
</div>



</form>
<?php $this->render("/installer/footer_view"); ?>
