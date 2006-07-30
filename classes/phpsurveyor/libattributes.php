<?php
class libattributes
{
	private $db; // lets keep a reference to adodb for ourselves.
	private $dbprefix; // lets keep the db prefix for our queries
	private $attributelist; // Attribute List
	private $surveyformat; // Keep track of survey mode
	private $currentattributes; // list of current attributes for ( quesiton / group / allinone )
	private $gid; // Current Group

	function libattributes($db,$dbprefix)
	{
		$this->db = $db;
		$this->dbprefix = $dbprefix;
		$this->db ->debug = true;

		/* Define our Attributes
		For each question attribute include a key:
		name - the display name
		types - a string with one character representing each question typ to which the attribute applies
		help - a short explanation */

		$qattributes = array();
		$qattributes[]=array("name"=>"display_columns",
		"types"=>"LMZ",
		"help"=>"Number of columns to display");
		$qattributes[]=array("name"=>"maximum_chars",
		"types"=>"STUN",
		"help"=>"Maximum Characters Allowed");
		$qattributes[]=array("name"=>"permission",
		"types"=>"5DGL!OMPQNRSTUYABCEFHWZ",
		"help"=>"Flexible attribute for permissions");
		$qattributes[]=array("name"=>"text_input_width",
		"types"=>"SNTU",
		"help"=>"Width of text input box");
		$qattributes[]=array("name"=>"hide_tip",
		"types"=>"L!OMPWZ",
		"help"=>"Hide the tip that is normally supplied with question");
		$qattributes[]=array("name"=>"random_order",
		"types"=>"L!OMPRQWZ^",
		"help"=>"Present Answers in random order");
		$qattributes[]=array("name"=>"code_filter",
		"types"=>"FGWZ",
		"help"=>"Filter the available answers by this value");
		$qattributes[]=array("name"=>"array_filter",
		"types"=>"ABFCE",
		"help"=>"Filter an Array's Answers from a Multiple Options Question");
		$qattributes[]=array("name"=>"display_rows",
		"types"=>"TU",
		"help"=>"How many rows to display");
		$qattributes[]=array("name"=>"default_value",
		"types"=>"^",
		"help"=>"What value to use as the default");
		$qattributes[]=array("name"=>"minimum_value",
		"types"=>"^",
		"help"=>"The lowest value on the slider");
		$qattributes[]=array("name"=>"maximum_value",
		"types"=>"^",
		"help"=>"The highest value on the slider");
		$qattributes[]=array("name"=>"answer_width",
		"types"=>"^ABCEF",
		"help"=>"The percentage width of the answer column");
		$this->attributelist = $qattributes;
	}

	/**
	* create_attributes($surveyformat,$gid) - Creates internal array of attribute objects
	* @return none
	*/
	function create_attributes($surveyformat,$gid)
	{
		$this->surveyformat = $surveyformat;
		$this->gid = $gid;
		if ($surveyformat == "G")
		{
			$questions = $this->get_group_questions($gid);
			foreach ($questions as $quest)
			{
				$this->get_question_attributes($quest[0]);
			}
		}
	}


	/**
	* get_attributes($type) - Returns availible attributes for a certain question type
	* @return returns an array of availible attributes for that question type $array[$x] => name, help.
	*/
	function get_attributes($type)
	{
		foreach($this->attributelist as $qa)
		{
			for ($i=0; $i<=strlen($qa['types'])-1; $i++)
			{
				$qat[substr($qa['types'], $i, 1)][]=array("name"=>$qa['name'],
				"help"=>$qa['help']);
			}
		}
		return $qat[$type];
	}

	/**
	* get_question_attributes($qid) - Gets all attributes for a question
	* @return an array of attribute objects  
	*/	
	private function get_question_attributes($qid)
	{
		$result = $this->db->Execute("SELECT qaid, attribute, value FROM question_attributes where qid='{$qid}'");
		if ($result->RecordCount() > 0)
		{
			$qattributes = array();
			while ($attrs = $result->fetchRow())
			{
				$qattributes[$attrs['qaid']] = new attribute($attrs['qaid'],$attrs['attribute'],$attrs['value'],$attrs['qid']);
			}
			return $qattributes;
		} else {
			return false;
		}
	}

	/**
	* get_group_questions($gid) - Gets qid's for all questions in group
	* @return an array qids 
	*/	
	private function get_group_questions($gid)
	{
		$result = $this->db->Execute("SELECT qid FROM questions where gid='{$gid}'");
		if ($result->RecordCount() > 0)
		{
			$questions = array();
			while ($attrs = $result->fetchRow())
			{
				$questions[$attrs[0]] = $attrs[0];
			}
			return $questions;
		} else {
			return false;
		}
	}

	/**
	* get_attribute_value($qid,$attribute) - Gets the value of an attribute
	* @return returns the string value of an attribute
	*/	
	function get_attribute_value($qid,$attribute)
	{
		$val = $this->db->GetOne("SELECT value FROM {$this->dbprefix}question_attributes WHERE attribute='{$attribute}' AND qid='".(int)$qid."'");
		if ($val)
		{
			return $val;
		} else
		{
			return false;
		}
	}


	/**
	* new_attribute($qid,$attribute,$value) - Stores a new attribute into the attributes table
	* @return returns true if successful and false if not
	*/	
	function new_attribute($qid,$attribute,$value)
	{
		$result = $this->db->Execute("INSERT INTO question_attributes (qid,attribute,value) values('{$qid}','{$attribute}','{$value}')");
		if ($result)
		{
			return true;
		} else
		{
			return false;
		}
	}

}
?>