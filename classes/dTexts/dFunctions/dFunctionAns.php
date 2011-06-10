<?php

class dFunctionAns implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $connect;
		$field = $args[0];
		if (isset($_SESSION['srid'])) $srid = $_SESSION['srid'];
		$sid = returnglobal('sid');
        // Map Question.title to first SGQA field matching it
        $fieldmap=createFieldMap($sid,$style='full');
        if (isset($fieldmap))
        {
            foreach($fieldmap as $fielddata)
            {
                if ($fielddata['title'] == $field)
                {
            		return retrieve_Answer($fielddata['fieldname'], $_SESSION['dateformats']['phpdate']);
                }
            }
        }
		return $field;
	}
}
