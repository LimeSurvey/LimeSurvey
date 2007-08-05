<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class attribute
{
	private $id;
	private $name;
	private $value;
	private $types;
	private $qid;
	
	function attribute($id,$name, $value,$qid)
	{
		$this->id = $id;
		$this->name = $name;
		$this->value = $value;
		$this->qid = $qid;
	}
}

?>
