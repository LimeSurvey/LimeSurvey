<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view",array('progressValue' => $progressValue));
?>

<form action="<?php echo base_url().'index.php/installer/language'; ?>" method="post">

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

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
<select id='installerLang' name='installerLang' style='width:190px;'>
<?php
    $langauges = getlanguagedata(true,true);
    foreach($langauges as $langkey => $languagekind)
    {
        $selected = $langkey === 'en';
        $value = $langkey;
        $label = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
        echo '<option value="', htmlspecialchars($langkey), '"', $selected ? ' selected="selected"' : '', ">",
            $label, '</option>', "\n";
    }
?>
</select>
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
<?php $this->load->view("installer/footer_view"); ?>