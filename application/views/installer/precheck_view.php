<?php
$clang = &get_instance()->limesurvey_lang;
$this->load->view("installer/header_view",array('progressValue' => $progressValue));

function dirReport($dir,$write)
{
    $clang = &get_instance()->limesurvey_lang;
    $error = 0;
    
    if ($dir == "Found")
    {
       $a = $clang->gT("Found");
    } else
    {
       $error = 1;
       $a = $clang->gT("Not Found");
    }
    
    if ($write == "Writable")
    {
       $b = $clang->gT("Writable");
    } else
    {
       $error = 1;
       $b = $clang->gT("Unwritable");
    }
    
    if ($error)
    {
       return '<b><font color="red">'.$a.' & '.$b.'</font></b>';     
    }
    else
    {
       return $a.' & '.$b;       
    }
}

?>

<div class="container_6">

<?php $this->load->view('installer/sidebar_view', array(
       'progressValue' => $progressValue,
       'classesForStep' => $classesForStep
    ));
?>

<div class="grid_4 table">

<p class="title">&nbsp;<?php echo "$title"; ?></p>
<div style="-moz-border-radius:15px; border-radius:15px; " >
<p>&nbsp;<?php echo $descp; ?></p>
<hr />
<fieldset class="content-table">
<legend class="content-table-heading"><?php echo $clang->gT("Required settings"); ?></legend>

<table style="width: 671px; margin-top: 0px; border-top-width: 1px; ">
<tr>
       <td  style="width: 209px;"><?php //echo $clang->gT("Required settings"); ?></td>
       <td align="center" style="width: 225px;"><b><?php echo $clang->gT("Recommended setting"); ?></b></td>
       <td align="center" style="width: 225px;"><b><?php echo $clang->gT("Current setting"); ?></b></td>
</tr>
<tr>
       <td style="width: 209px;"><?php echo $clang->gT("PHP version"); ?></td>
       <td align="center" style="width: 225px;">5.1.6+</td>
       <td align="center" style="width: 225px;"><?php if (isset($verror) && $verror) { ?><span style='font-weight:bold; color: red'><?php echo $clang->gT("Outdated"); ?>: <?php echo $phpVersion; ?></span></b>
       <?php } else { ?><?php echo $phpVersion ; ?> <?php } ?></td>
</tr>
<tr>
       <td style="width: 209px;"><?php echo $clang->gT("PHP5 mbstring library"); ?></td>
       <td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
       <td align="center" style="width: 225px;"><?php echo $mbstringPresent ; ?></td>
</tr>
<tr>
       <td style="width: 209px;">/application/config/database.php <?php echo $clang->gT("file"); ?></td>
       <td align="center" style="width: 225px;"><?php echo $clang->gT("Found & Writable"); ?></td>
       <td align="center" style="width: 225px;"><?php  echo dirReport($databasePresent,$databaseWritable); ?></td>
</tr>
<tr>
       <td style="width: 209px;">/application/config/autoload.php <?php echo $clang->gT("file"); ?></td>
       <td align="center" style="width: 225px;"><?php echo $clang->gT("Found & Writable"); ?></td>
       <td align="center" style="width: 225px;"><?php  echo dirReport($autoloadPresent,$autoloadWritable); ?></td>
</tr>
<tr>
       <td style="width: 209px;">/tmp <?php echo $clang->gT("directory"); ?></td>
       <td align="center" style="width: 225px;"><?php echo $clang->gT("Found & Writable"); ?></td>
       <td align="center" style="width: 225px;"><?php  echo dirReport($tmpdirPresent,$tmpdirWritable); ?></td>
</tr>
<tr>
       <td style="width: 209px;">/upload <?php echo $clang->gT("directory"); ?></td>
       <td align="center" style="width: 225px;"><?php echo $clang->gT("Found & Writable"); ?></td>
       <td align="center" style="width: 225px;"><?php  echo dirReport($uploaddirPresent,$uploaddirWritable); ?></td>
</tr>
<tr>
       <td style="width: 209px;">/templates <?php echo $clang->gT("directory"); ?></td>
       <td align="center" style="width: 225px;"><?php echo $clang->gT("Found & Writable"); ?></td>
       <td align="center" style="width: 225px;"><?php  echo dirReport($templatedirPresent,$templatedirWritable); ?></td>
</tr>

</table>
</fieldset>
<fieldset class="content-table">
<legend class="content-table-heading"><?php echo $clang->gT('Optional settings'); ?></legend>
<table style="width: 671px; margin-top: 0px; border-top-width: 1px;" >
<tr>
       <td style="width: 209px;">&nbsp;</td>
       <td align="center" style="width: 225px;"><b><?php echo $clang->gT('Reccommended settings'); ?></b></td>
       <td align="center" style="width: 225px;"><b><?php echo $clang->gT('Current settings'); ?></b></td>
</tr>
<tr>
       <td style="width: 209px;">PHP5 GD library</td>
       <td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
       <td align="center" style="width: 225px;"><?php echo $gdPresent ; ?></td>
</tr>
<tr>
       <td style="width: 209px;">PHP5 LDAP library</td>
       <td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
       <td align="center" style="width: 225px;"><?php echo $ldapPresent ; ?></td>
</tr>
<tr>
       <td style="width: 209px;">PHP5 zip library</td>
       <td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
       <td align="center" style="width: 225px;"><?php echo $zipPresent ; ?></td>
</tr>
<tr>
       <td style="width: 209px;">PHP5 zlib library</td>
       <td align="center" style="width: 225px;"><img src="<?php echo base_url(); ?>installer/images/tick-right.png" alt="Check" /></td>
       <td align="center" style="width: 225px;"><?php echo $zlibPresent ; ?></td>
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
<table style="font-size:11px; width: 694px;">
<tbody>
<tr>
<td align="left" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT('Previous'); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/license"); ?>', '_top')" /></td>
<td align="center" style="width: 227px;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT('Check again'); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/0"); ?>', '_top')" /></td>
<td align="right" style="width: 227px;">
<?php if (isset($next) && $next== TRUE) { ?>
<input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT('Next'); ?>" onclick="javascript: window.open('<?php echo site_url("installer/install/1"); ?>', '_top')" />
<?php } ?>

</td>
</tr>
</tbody>
</table>
</div>
</div>
<?php $this->load->view("installer/footer_view"); ?>