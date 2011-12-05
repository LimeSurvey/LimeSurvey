<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<div class="grid_2 table">
<p class="title"> &nbsp;Progress</p>
<p> &nbsp;<?php echo $progressValue ; ?>% Completed</p>
<div style="width: 280px; height: 20px; margin-left: 6px;" id="progressbar"></div>
<br />
<div id="steps">
<table class="grid_2" >
<tr class="<?php echo $classesForStep[0]; ?>">
<td>1: <?php echo $clang->gT("Welcome"); ?></td>
</tr>
<tr class="<?php echo $classesForStep[1]; ?>">
<td>2: <?php echo $clang->gT("License"); ?></td>
</tr>
<tr class="<?php echo $classesForStep[2]; ?>">
<td>3: <?php echo $clang->gT("Pre-installation check"); ?></td>
</tr>
<tr class="<?php echo $classesForStep[3]; ?>">
<td>4: <?php echo $clang->gT("Configuration"); ?></td>
</tr>
<tr class="<?php echo $classesForStep[4]; ?>">
<td>5: <?php echo $clang->gT("Database settings"); ?></td>
</tr>
<tr class="<?php echo $classesForStep[5]; ?>">
<td>6: <?php echo $clang->gT("Optional settings"); ?></td>
</tr>
</table>
</div>
</div>