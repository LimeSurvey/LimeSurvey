<?php 
	echo '<pre>';
	foreach ($groups as $group)
	{
		echo $group->group_name . "\n";
		foreach($group->questions as $question)
		{
			var_dump($question->attributes);
//			echo $group->group_name . "\n";
		}
		
	}
	echo '</pre>';
?>