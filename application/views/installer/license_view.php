<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view", array('progressValue' => $progressValue));
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
<iframe src="<?php echo base_url(); ?>COPYING" style="height: 268px; width: 694px; border-width: 0px;"> </iframe>
<hr />

</div>
</div>

</div>
<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<form action="<?php echo site_url("installer/install/0"); ?>" method="post" style="width: 300px;" name="formcheck">
<table style="font-size:11px; width: 694px; background: #ffffff;">
<tbody>
   <tr>
    <td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/welcome"); ?>', '_top')" /></td>
    <td align="center" style="width: 227px;"></td>
    <td align="right" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"  type="submit" value="<?php echo $clang->gT('Next'); ?>" /></td>
   </tr>
</tbody>
</table>
</form>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>