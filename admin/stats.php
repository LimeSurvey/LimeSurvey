<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/


include_once("login_check.php");  //Login Check dies also if the script is started directly
if (!isset($dbprefix) || isset($_REQUEST['jpgraphdir'])) {die("Cannot run this script directly");}


include_once("login_check.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (isset($_SESSION['adminlang'])) {$language = $_SESSION['adminlang'];} else {	$language = GetBaseLanguageFromSurveyID($surveyid);};
//RL: 

if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
{
	require_once ("$jpgraphdir/jpgraph.php");
	require_once ("$jpgraphdir/jpgraph_pie.php");
	require_once ("$jpgraphdir/jpgraph_pie3d.php");
	require_once ("$jpgraphdir/jpgraph_bar.php");
	//$currentuser is created as prefix for jpgraph files
	if (isset($_SERVER['REDIRECT_REMOTE_USER']))
	{
		$currentuser=$_SERVER['REDIRECT_REMOTE_USER'];
	}
	elseif (session_id())
	{
		$currentuser=substr(session_id(), 0, 15);
	}
	else
	{
		$currentuser="standard";
	}
}

//Get array of all questions with answers
$query = "SELECT ".db_table_name("questions").".*, group_name, group_order\n"
        ."FROM ".db_table_name("questions").", ".db_table_name("groups")."\n"
        ."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
        ."AND ".db_table_name("groups").".language='".$language."' AND ".db_table_name("questions").".language='".$language."'\n"
        ."AND ".db_table_name("questions").".sid=$surveyid";
$result = db_execute_assoc($query) or die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());
$rows = $result->GetRows();

//SORT IN NATURAL ORDER!
usort($rows, 'CompareGroupThenTitle');

//Create huge array of all questions/parts/answer possibilities
$i=0;
foreach ($rows as $row)
{
	$questions[$i]=$row;
	$questions[$i]['parts']=getAnswerArray($row);
	$i++;
}
unset($rows);
//debug($questions);


//////////////////////////////////////////////////////////////////
// PRESENT HEADERS ///////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

sendcacheheaders();
$surveyoptions=browsemenubar();
echo $htmlheader;
?>
<table class='menubar' width='99%' align='center' style='margin: 5px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>
  <tr>
   <td colspan='2' height='4'>
    <strong><? echo $clang->gT("Quick Statistics") ?></strong>
   </td>
  </tr>
<? echo $surveyoptions ?>
</table>
<?
//////////////////////////////////////////////////////////////////
// END OF PRESENT HEADERS ////////////////////////////////////////
//////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////
// DO CONDITIONS PAGE ////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

if(!isset($_POST['action'])) {

	//////////////////////////////////////////////////////////////////
	// DO JAVASCRIPT /////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
    ?>
    <script type='text/javascript'><!--
    function display(value) {
    	<?
    	foreach($questions as $question) {
    		foreach($question['parts'] as $part) {
    			echo "    document.getElementById('".$question['sid']."X".$question['gid']."X".$question['qid'].$part['id']."').style.display='none';\n";
    		}
    	}
    	?>
    	document.getElementById(value).style.display='';
    }

    function addCondition(value) {
    	var val='VALUE'+value;
    	var type=document.getElementById(val).type;
    	var element=document.getElementById(val);
    	var meth='method'+value;
    	var method=document.getElementById(meth);

    	switch(type) {
    		case 'text':
    		var sql=value+" ";
    		for(var i=0; i < method.options.length; i++) {
    			if(method.options[i].selected && method.options[i].value == "like") {
    				sql = sql + " LIKE '%" + element.value + "%'";
    			}
    			if (method.options[i].selected && method.options[i].value == "equals") {
    				sql = sql + " = '" + element.value + "'";
    			}
    		}
    		break;
    		case 'select-multiple':
    		for(var i=0; i < method.options.length; i++) {
    			if(method.options[i].selected && method.options[i].value == "in") {
    				var modifier='IN';
    			}
    			if (method.options[i].selected && method.options[i].value == "notin") {
    				var modifier='NOT IN';
    			}
    		}
            var sql=value+' '+modifier+' (';
            var r = new Array();
            var x=0;
            for (var i = 0; i < element.options.length; i++) {
    		 if (element.options[i].selected) {
              var thisvalue = element.options[i].value;
              if(x>0) {
    		   sql = sql+', ';
    		  }
              sql = sql + "'"+thisvalue+"'";
              x++;
             }
            }
            sql = sql+')';
            break;
       }
       var conditions=document.getElementById('conditions');
       concount=conditions.options.length;
       conditions.options[concount] = new Option(sql, sql);
       if(conditions.options.length > 0) {
        document.getElementById('removecondition').display='';
       }
       
       var filters = new String();
       var filter = document.getElementById('filter');
       for (var i = 0; i < concount+1; i++) {
	    filters += conditions.options[i].value + ";";
	   }
	   filter.value=filters;
	   alert(filters);
     }

    //--></script>
    <?

    //////////////////////////////////////////////////////////////////
    // END OF DO JAVASCRIPT //////////////////////////////////////////
    //////////////////////////////////////////////////////////////////
    ?>
	<table width='99%' align='center' style='border: 1px'>
    <tr>
    <td colspan='2' style='border: 1px;' align='center'>
    <form method='post' action='stats.php?sid=<? echo $_GET['sid'] ?>'>
    <input type='hidden' name='action' value='fields'>
    <? presentFilterConditions() ?>
    <input type='submit' value='Proceed'>
    </form>
    </td>
    </tr>
    <tr>
    <td valign='top' width='50%'>
    <? presentQuestionList($questions) ?>
    </td>
    <td valign='top'>
    <? presentAnswerList($questions) ?>
    </td>
    </tr>

    </table>
    <?


} elseif (isset($_POST['action']) && $_POST['action'] == "fields") {
    //////////////////////////////////////////////////////////////////
    // DO FIELD SELECTION PAGE ///////////////////////////////////////
    //////////////////////////////////////////////////////////////////
	?>

    <script type='text/javascript'>
    <!--
      function selectAll(item) {
	   var element=document.getElementById(item);
	   for (var i=0; i < element.options.length; i++) {
	    element.options[i].selected=true;
	   }
	  }
	  function display(item) {
	   /* Just a dummy function */
	  }

    //-->
    </script>
    
    <form method='post' action='stats.php?sid=<? echo $_GET['sid'] ?>'>
    <input type='hidden' name='action' value='display'>
	<table width='99%' align='center' style='border: 1px'>
	<tr>
	<td colspan='2' style='text-align: center; border: 1px;'>
	<? presentFilterConditions() ?>
	</td>
	</tr>
	<tr>
	<td colspan='2' style='text-align: center; border: 1px;'>
	<? presentQuestionList($questions) ?>
	<input type='button' value='<? echo $clang->gT("Select all"); ?>' onclick='selectAll("questions")'>
	<input type='submit' value='<? echo $clang->gT("Proceed"); ?>'>
	</td>
	</tr>
	</table>
	</form>


	<?


} elseif (isset($_POST['action']) && $_POST['action'] == "display") {
/////////////////////////////////////////////////////////////////////
// START OF PRESENTING RESULTS //////////////////////////////////////
/////////////////////////////////////////////////////////////////////

echo "<pre>";
print_r($_POST);
echo "</pre>";

/////////////////////////////////////////////////////////////////////
// END OF PRESENTING RESULTS //////////////////////////////////////
/////////////////////////////////////////////////////////////////////
}

function presentQuestionList($questions) {
	//////////////////////////////////////////////////////////////////
	// PRESENT QUESTION LIST /////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	?>
	<script type='text/javascript'>
	<!--
     function selectAll(item) {
	   var element=document.getElementById(item);
	   for (var i=0; i < element.options.length; i++) {
	    element.options[i].selected=true;
	   }
	 }	
	//--></script>
	<table class='filter' width='99%' align='center'>
	<tr>
	<th>
	<? echo $clang->gT("Question List") ?>
	</th>
	</tr>
	<tr>
	<td style='text-align: center'>
	<select multiple name='questions[]' id='questions' size='10' onDblClick='alert(this.value)' onchange='display(this.value)'>
	<?
	$currentgroup="";
	foreach($questions as $question) {
		if($question['group_name'] != $currentgroup) {
			if($currentgroup != "") {
				echo "    </optgroup>\n";
			}
			echo "    <optgroup label='".$clang->gT("Group").": ".$question['group_name']."'>\n";
		}
		if(count($question['parts']) > 1) {
			echo "     <option value=''>".$question['title'].": ".$question['question']."</option>\n";
			foreach($question['parts'] as $qpart) {
				$spacer="";
				for($i=0; $i<=strlen($question['title'])*2; $i++) {
					$spacer .= "&nbsp;";
				}
				echo "     <option value='".$question['sid']."X".$question['gid']."X".$question['qid'].$qpart['id']."'>".$spacer."-> ".$qpart['part']."</option>\n";
			}
		} else {
			echo "     <option value='".$question['sid']."X".$question['gid']."X".$question['qid']."'>".$question['title'].": ".$question['question']."</option>\n";
		}
		$currentgroup=$question['group_name'];
	}
	?>
	</optgroup>
	</select>
	</td>
	</tr>
	</table>
	<?
	//////////////////////////////////////////////////////////////////
	// END OF PRESENT QUESTION LIST //////////////////////////////////
	//////////////////////////////////////////////////////////////////
}


function presentAnswerList($questions) {
	//////////////////////////////////////////////////////////////////
	// PRESENT ANSWER LIST ///////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	?>
	<table class='filter' width='99%' align='center'>
	<tr>
	<td style='text-align: center'>
	<?
	foreach($questions as $question) {
		if(count($question['parts']) > 0) {
			foreach($question['parts'] as $part) {
				echo "    <div id='".$question['sid']."X".$question['gid']."X".$question['qid'].$part['id']."' style='display: none'>\n";
				echo "     <strong>".$question['question']."</strong><br />";
				if(count($question['parts']) > 1) {
					echo "     <i>".$part['part']."</i><br />";
				}
				$inputname=$question['sid']."X".$question['gid']."X".$question['qid'].$part['id'];
				$valuename="VALUE".$inputname;
				//$valuename=$inputname;
				switch($question['type']) {
					case "S":
					case "T":
					case "U":
					case "Q":
					case "N":
					case "D":
					echo "     <select name='method$inputname' id='method$inputname'>\n";
					echo "      <option selected='selected' value='like'>".$clang->gT("Like")."</option>\n";
					echo "      <option value='equals'>".$clang->gT("Equals")."</option>\n";
					echo "     </select><br />\n";
					echo "     <input type='text' name='$inputname' id='$valuename'><br />\n";
					break;
					default:
					echo "     <select name='method$inputname' id='method$inputname'>\n";
					echo "      <option selected='selected' value='in'>".$clang->gT("Equals")."</option>\n";
					echo "      <option value='notin'>".$clang->gT("Not Equals")."</option>\n";
					echo "     </select><br />\n";
					echo "     <select multiple size='5' name='$inputname' id='$valuename'>\n";
					foreach($part['answers'] as $answer) {
						echo "      <option value='".$answer['code']."'>".$answer['answer']."</option>\n";
					}
					echo "     </select><br />\n";
					break;
				}
				echo "     <input type='button' value='".$clang->gT("Add")."' onclick='addCondition(\"$inputname\")'>";
				echo "    </div>\n";
			}
		}
	}
	?>
	</td>
	</tr>
	</table>
	<?
	//////////////////////////////////////////////////////////////////
	// END OF PRESENT ANSWER LIST ////////////////////////////////////
	//////////////////////////////////////////////////////////////////
}

function presentFilterConditions() {
	//////////////////////////////////////////////////////////////////
	// SHOW FILTER CONDITIONS ////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	?>
	<script type='text/javascript'>
	<!--
	    function removeCondition() {
    	var conditions=document.getElementById('conditions');
    	var selected=conditions.selectedIndex;
    	var token = conditions.options[selected].value + ";";
    	var token2 = conditions.options[selected].value;
    	var filter = document.getElementById('filter');
		conditions.options[selected] = null;
    	if(conditions.options.length < 1 ) {
            document.getElementById('removecondition').display='none';
    	}
    	if(filter.value.indexOf(token) != -1) {
		  var newfilter=filter.value.replace(token,"");
		} else {
		  var newfilter=filter.value.replace(token2, "");
		}
		filter.value=newfilter;
		if (filter.value.indexOf(",") == 0) {
            newfilter = filter.value.substring(1);
            filter.value=newfilter;
		}
    }   
    //--></script>
	<table width='99%' align='center'>
	<tr>
	<th>
	<? echo $clang->gT("Filter Conditions") ?>
	</th>
	</tr>
	<tr>
	<td style='text-align: center'>
	<input type='hidden' name='filter' id='filter' value="<? if(isset($_POST['filter'])) {echo $_POST['filter'];} ?>" />
	<select multiple name='conditions[]' id='conditions' size='5' style='width: 600'>
	<?
	if(isset($_POST['filter'])) {
	    $filters=explode(";", $_POST['filter']);
		foreach($filters as $condition) {
		    if($condition != "") {
                echo "      <option value=\"$condition\">".$condition."</option>\n";
			}
		}
	}
	?>
	</select><br />
	<input type='button' value='Remove' id='removecondition' onclick='removeCondition()' />
	</td>
	</tr>
	</table>
	<?


	//////////////////////////////////////////////////////////////////
	// END OF SHOW FILTER CONDITIONS /////////////////////////////////
	//////////////////////////////////////////////////////////////////
}


function getAnswerArray($data) {
	$qid=$data['qid'];
	$type=$data['type'];
	$question=$data['question'];
	$lid=$data['lid'];
	//Get answers if question has them
	switch($type) {
		case "M":
		case "L":
		case "!":
		case "O":
		case "P":
		//These are all types that have answers in the answer table
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$output[]=array("part"=>$question,
		"id"=>"",
		"answers"=>$result->GetRows()
		);
		break;
		case "R":
		//These are all types that have answers in the answer table
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$output[]=array("part"=>$question,
		"id"=>"",
		"answers"=>$result->GetRows()
		);
		break;
		case "Q":
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		foreach($result->GetRows() as $row) {
			$output[]=array("part"=>$row['answer'],
			"id"=>$row['code'],
			"answers"=>array(0=>array("qid"=>$qid,
			"code"=>$row['code'],
			"answer"=>$clang->gT("Short Free text"),
			"default_answer"=>"",
			"sortorder"=>0)
			)
			);
		}
		break;
		case "A": //Predefined - 5 point choice
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$i=0;
		foreach($result->GetRows() as $row) {
			$output[$i]["part"]=$row['answer'];
			$output[$i]["id"]=$row['code'];
			for($j=1; $j<=5; $j++) {
				$output[$i]["answers"][]=array("qid"=>$qid,
				"code"=>$j,
				"answer"=>$j,
				"default_answer"=>"",
				"sortorder"=>$j);
			}
			$i++;
		}
		break;
		case "B": //Predefined - 10 point choice
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$i=0;
		foreach($result->GetRows() as $row) {
			$output[$i]["part"]=$row['answer'];
			$output[$i]["id"]=$row['code'];
			for($j=1; $j<=10; $j++) {
				$output[$i]["answers"][]=array("qid"=>$qid,
				"code"=>$j,
				"answer"=>$j,
				"default_answer"=>"",
				"sortorder"=>$j);
			}
			$i++;
		}
		break;
		case "C": //Predefined - Yes/Uncertain/No
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$i=0;
		foreach($result->GetRows() as $row) {
			$output[$i]["part"]=$row['answer'];
			$output[$i]["id"]=$row['code'];
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"Y",
			"answer"=>$clang->gT("Yes"),
			"default_answer"=>"",
			"sortorder"=>0);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"U",
			"answer"=>$clang->gT("Uncertain"),
			"default_answer"=>"",
			"sortorder"=>1);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"N",
			"answer"=>$clang->gT("No"),
			"default_answer"=>"",
			"sortorder"=>0);
			$i++;
		}
		break;
		case "E": //Predefined - Increase/Same/Decrease
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$i=0;
		foreach($result->GetRows() as $row) {
			$output[$i]["part"]=$row['answer'];
			$output[$i]["id"]=$row['code'];
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"I",
			"answer"=>$clang->gT("Increase"),
			"default_answer"=>"",
			"sortorder"=>0);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"S",
			"answer"=>$clang->gT("Same"),
			"default_answer"=>"",
			"sortorder"=>1);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"D",
			"answer"=>$clang->gT("Decrease"),
			"default_answer"=>"",
			"sortorder"=>0);
			$i++;
		}
		break;
		case "F": //Predefined (flexible)
		case "H": //Predefined (flexible)
		$query = "SELECT * FROM ".db_table_name("answers")."
    	          WHERE ".db_table_name("answers").".qid=".$qid."
    	          ORDER BY ".db_table_name("answers").".sortorder";
		$result = db_execute_assoc($query) or die("Couldn't extract answers!<br />$query<br />".$connect->ErrorMsg());
		$query2 = "SELECT * FROM ".db_table_name("labels")."
                   WHERE ".db_table_name("labels").".lid=".$lid."
                   ORDER BY ".db_table_name("labels").".sortorder";
		$result2 = db_execute_assoc($query2) or die("Couldn't extract labels!<br />$query2<br />".$connect->ErrorMsg());
		$labels=$result2->GetRows();
		$i=0;
		foreach($result->GetRows() as $row) {
			$output[$i]["part"]=$row['answer'];
			$output[$i]["id"]=$row['code'];
			foreach($labels as $row2) {
				$output[$i]["answers"][]=array("qid"=>$qid,
				"code"=>$row2['code'],
				"answer"=>$row2['title'],
				"default_answer"=>"",
				"sortorder"=>$row2['sortorder']);
			}
			$i++;
		}
		break;
		case "W":
		case "Z":
		break;
		case "Y":
		//A predefined type
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"=>array(0=>array("qid"=>$qid,
		"code"=>"Y",
		"answer"=>$clang->gT("Yes"),
		"default_value"=>"",
		"sortorder"=>0),
		1=>array("qid"=>$qid,
		"code"=>"N",
		"answer"=>$clang->gT("No"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		case "5":
		$output[0]["part"]=$question;
		$output[0]["id"]="";
		for ($i=1; $i<=5; $i++) {
			$output[0]["answers"][]=array("qid"=>$qid,
			"code"=>$i,
			"answer"=>$i,
			"default_value"=>"",
			"sortorder"=>$i);
		}
		break;
		case "G":
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"=>array(0=>array("qid"=>$qid,
		"code"=>"M",
		"answer"=>$clang->gT("Male"),
		"default_value"=>"",
		"sortorder"=>0),
		array("qid"=>$qid,
		"code"=>"F",
		"answer"=>$clang->gT("Female"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		case "D": //Date
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>$clang->gT("Date"),
		"default_value"=>"",
		"sortorder"=>1))
		);
		break;
		case "N": //Numerical
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>$clang->gT("Numerical"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		case "S": //Short
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>$clang->gT("Short Free Text"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		case "T": //Long
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>$clang->gT("Long Free Text"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		case "U": //Huge
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>$clang->gT("Huge Free Text"),
		"default_value"=>"",
		"sortorder"=>1)
		)
		);
		break;
		default:
		/*echo "<hr>";
		print_r($data);
		echo "<hr>";*/
		$output=array();
		break;
	}
	return $output;
}

function debug($data) {
	print "<pre>";
	print_r($data);
	print "</pre>";
}
?>
