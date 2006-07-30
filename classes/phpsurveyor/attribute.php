<?php

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