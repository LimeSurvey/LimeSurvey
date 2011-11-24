<div class='header ui-widget-header'>
	<?php echo $clang->gT("Token summary");?>
</div>
<br />
<table align='center' class='statisticssummary'>
	<tr>
		<th>
		    <?php echo $clang->gT("Total records in this token table");?>
		</th>
		<td>
			<?php echo $queries['tkcount']; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Total with no unique Token");?>
		</th>
		<td>
			<?php echo $queries['query1']; ?>
		</td>
	</tr>
	<tr>	
		<th>
			<?php echo $clang->gT("Total invitations sent");?>
		</th>
		<td>
			<?php echo $queries['query2']; ?>
		</td>
	</tr>
	<tr>			
		<th>
			<?php echo $clang->gT("Total opted out");?>
		</th>
		<td>
			<?php echo $queries['query3']; ?>
		</td>
	</tr>
	<tr>		
		<th>
			<?php echo $clang->gT("Total surveys completed");?>
		</th>	
		<td>
			<?php echo $queries['query4']; ?>
		</td>
	</tr>
</table>
<br />

