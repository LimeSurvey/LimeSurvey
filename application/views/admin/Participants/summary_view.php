
<!DOCTYPE html>
<html>
    <head><link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/adminstyle.css" ?>" /></head>
    <body>
        <div class='header ui-widget-header'>
        <?php echo $clang->gT("Central Participants Database Summary");?>
        </div>
        <br />
        <table align='center' class='statisticssummary'>
	<tr>
		<th>
		    <?php echo $clang->gT("Total Participants in central table  ");?>
		</th>
		<td>
			<?php echo $totalrecords; ?>
		</td>
	</tr>
	<tr>
		<th>
            <?php echo $clang->gT("Participants Owned by you  "); ?>

		</th>
		<td>
			<?php echo $owned.' / '.$totalrecords; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Participants Shared with you  ");?>
		</th>
		<td>
			<?php echo $totalrecords-$owned.' / '.$totalrecords; ?>
		</td>
	</tr>
	<tr>
	<th>
            <?php echo $clang->gT("Participants you have shared  ");?>
		</th>
		<td>
            <?php echo $shared.' / '.$totalrecords; ?>
		</td>
	</tr>
    <tr>
		<th>
            <?php echo $clang->gT("Blacklisted Participants  ");?>
		</th>
		<td>
            <?php echo $blacklisted; ?>
		</td>
	</tr>
    <tr>
		<th>
          <?php echo $clang->gT("Total attributes in the central table  ");?>
		</th>
		<td>
            <?php echo $attributecount; ?>
		</td>
	</tr>
</table>
<br />
        <?php
        // put your code here
        ?>
    </body>
</html>
