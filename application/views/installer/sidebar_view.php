<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<div class="grid_2 table">
<p class="title"> &nbsp;Progress</p>
<p> &nbsp;<?php echo $progressValue ; ?>% Completed</p>
<div style="width: 320px; height: 20px; margin-left: 6px;" id="progressbar"></div>
<br />
<div id="steps">
<table class="grid_2" >
<tr class="<?php echo $classesForStep[0]; ?>">
<td>1: License</td>
</tr>
<tr>
<td></td>
</tr>
<tr class="<?php echo $classesForStep[1]; ?>">
<td>2: Pre-installation check</td>
</tr>
<tr class="<?php echo $classesForStep[2]; ?>">
<td>3: Configuration </td>
</tr>
<tr class="<?php echo $classesForStep[3]; ?>">
<td>4: Database settings </td>
</tr>
<tr class="<?php echo $classesForStep[4]; ?>">
<td>5: Optional settings</td>
</tr>
</table>
</div>
</div>