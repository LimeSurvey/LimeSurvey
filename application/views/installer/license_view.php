<?php $this->load->view("installer/header_view", array('progressValue' => $progressValue)); ?>

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

<br /><br />
</div>
</div>

</div>
<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<form action="<?php echo site_url("installer/install/0"); ?>" method="post" style="width: 300px;" name="formcheck">
<table style="width: 694px;">
<tbody>
<tr>
<td align="center" style="width: 800px;"></td>
<td align="right" style="width: 190px;">
<div id="next" style="font-size:11px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"  type="submit" value="Next" /></div>
</td>
</tr>
</tbody>
</table>
</form>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>