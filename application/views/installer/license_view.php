<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">


<p class="title">&nbsp;<?php echo $title; ?></p>



<div style="-moz-border-radius:15px; border-radius:15px;" >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
<iframe src="<?php echo $this->createUrl('installer/viewlicense'); ?>" style="height: 268px; width: 694px; border-width: 0px;"> </iframe>
<hr />

</div>
</div>

</div>
<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<form action="<?php echo $this->createUrl('installer/license'); ?>" method="post" style="width: 300px;" name="formcheck">
<table style="font-size:11px; width: 694px; background: #ffffff;">
<tbody>
   <tr>
    <td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/install/welcome"); ?>', '_top')" /></td>
    <td align="center" style="width: 227px;"></td>
    <td align="right" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"  type="submit" value="<?php echo $clang->gT('I accept'); ?>" /></td>
   </tr>
</tbody>
</table>
</form>
</div>
</div>
<?php $this->render("/installer/footer_view"); ?>