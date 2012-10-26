<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">


<p class="maintitle">&nbsp;<?php echo $title; ?></p>

<div style="-moz-border-radius:15px; border-radius:15px;" >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />

<b> <?php $clang->eT("Administrator credentials"); ?>:</b><br /><br />
<?php $clang->eT("Username"); ?>: <?php echo $user; ?> <br />
<?php $clang->eT("Password"); ?>: <?php echo $pwd; ?>
<br /><br />
</div>
</div>

<div class="clear"></div>

<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
<br/>
<table style="width: 694px;">
 <tbody>
  <tr>
   <td align="left" style="width: 227px;"></td>
   <td align="right" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" value="<?php $clang->eT("Administration"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("/admin"); ?>', '_top')" />
    <div id="next" style="font-size:11px;"></div>
   </td>
  </tr>
 </tbody>
</table>
</div>
</div>
<?php $this->render("/installer/footer_view"); ?>