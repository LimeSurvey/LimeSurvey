<?php
/*
#############################################################
# >>> PHPSurveyor                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date:    20 February 2003                               #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#   Copyright (C) 2003  Jason Cleeland                      #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License as published by the Free Software          #
# Foundation; either version 2 of the License, or (at your  #
# option) any later version.                                #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/

require_once(dirname(__FILE__).'/../config.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

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
$query = "SELECT ".db_table_name("questions").".*, group_name\n"
        ."FROM ".db_table_name("questions").", ".db_table_name("groups")."\n"
        ."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
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
$slstyle2 = "style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080; width: 150'";
$surveyoptions=browsemenubar();
echo $htmlheader;
?>
<table width='99%' align='center' style='margin: 5px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>
  <tr bgcolor='#555555'>
   <td colspan='2' height='4'>
    <font size='1' face='verdana' color='white'><strong><? echo _("Quick Statistics") ?></strong></font>
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

     function selectAll(item) {
	   var element=document.getElementById(item);
	   for (var i=0; i < element.options.length; i++) {
	    method.options[i].selected=true;
	   }
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
       var filters = new Array();
       var filter = document.getElementById('filter');
       for (var i = 0; i < concount+1; i++) {
	    filters[i]=conditions.options[i].value;
	   }
	   filter.value=filters;
     }
     function removeCondition() {
       var conditions=document.getElementById('conditions');
       var selected=conditions.selectedIndex;
       conditions.options[selected] = null;
       if(conditions.options.length < 1 ) {
        document.getElementById('removecondition').display='none';
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
    }
    function removeCondition() {
    	var conditions=document.getElementById('conditions');
    	var selected=conditions.selectedIndex;
    	conditions.options[selected] = null;
    	if(conditions.options.length < 1 ) {
    		document.getElementById('removecondition').display='none';
    	}

    }
    //--></script>
    <?

    //////////////////////////////////////////////////////////////////
    // END OF DO JAVASCRIPT //////////////////////////////////////////
    //////////////////////////////////////////////////////////////////
    ?>
	<table width='99%' align='center' style='border: 1px'>
    <tr>
    <td colspan='2' style='border: 1px; background-color: #cccccc' align='center'>
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

    //////////////////////////////////////////////////////////////////
    // DO FIELD SELECTION PAGE ///////////////////////////////////////
    //////////////////////////////////////////////////////////////////

} elseif (isset($_POST['action']) && $_POST['action'] == "fields") {
	print_r($_POST);
	print_r($_GET);
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
   
    <table width='99%' align='center' style='border: 1px'>
     <tr>
      <td colspan='2' style='border: 1px; background-color: #cccccc; text-align: center'>
       <form method='post' action='stats.php?sid=<? echo $_GET['sid'] ?>'>
       <input type='hidden' name='action' value='display' />
       <input type='hidden' name='filter[]' value='<? echo htmlentities($_POST['filter'][0], ENT_QUOTES) ?>' />
        <? presentQuestionList($questions) ?>
       <input type='button' value='Select All' onClick='selectAll("questions")' / >
       <input type='submit' value='Proceed' />
       </form>
      </td>
     </tr>
     <tr>
      <td colspan='2' style='border: 1px; background-color: #eeeeee'>
      <? presentFilterConditions() ?>
      </td>
     </tr>
    </table>
    
	<table width='99%' align='center' style='border: 1px'>
	<tr>
	<td colspan='2' style='border: 1px; background-color: #cccccc'>
	<? presentQuestionList($questions) ?>
	</td>
	</tr>
	<tr>
	<td colspan='2' style='border: 1px; background-color: #eeeeee'>
	<? presentFilterConditions() ?>
	</td>
	</tr>
	</table>


	<?


} elseif (isset($_POST['action']) && $_POST['action'] == "display") {
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "Howdy";

}

function presentQuestionList($questions) {
//////////////////////////////////////////////////////////////////
// PRESENT QUESTION LIST /////////////////////////////////////////
//////////////////////////////////////////////////////////////////
    ?>
    <table class='filter' width='99%' align='center'>
     <tr>
      <th>
       <? echo _("Select question(s) from the following list") ?>
      </th>
     </tr>
     <tr>
      <td style='text-align: center'>
       <select multiple name='questions[]' id='questions' size='10' onDblClick='alert(this.value)' onChange='display(this.value)'>
    <?
    $currentgroup="";
    foreach($questions as $question) {
      if($question['group_name'] != $currentgroup) {
        if($currentgroup != "") {
    	  echo "    </optgroup>\n";
    	}
        echo "    <optgroup label='"._("Group").": ".$question['group_name']."'>\n";
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

	//////////////////////////////////////////////////////////////////
	// PRESENT QUESTION LIST /////////////////////////////////////////
	//////////////////////////////////////////////////////////////////
	?>
	<table class='filter' width='99%' align='center'>
	<tr>
	<th>
	<? echo _("Select question(s) from the following list") ?>
	</th>
	</tr>
	<tr>
	<td style='text-align: center'>
	<select multiple name='questions' size='10' onDblClick='alert(this.value)' onChange='display(this.value)'>
	<?
	$currentgroup="";
	foreach($questions as $question) {
		if($question['group_name'] != $currentgroup) {
			if($currentgroup != "") {
				echo "    </optgroup>\n";
			}
			echo "    <optgroup label='"._("Group").": ".$question['group_name']."'>\n";
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
					echo "      <option selected='selected' value='like'>"._("Like")."</option>\n";
					echo "      <option value='equals'>"._("Equals")."</option>\n";
					echo "     </select><br />\n";
					echo "     <input type='text' name='$inputname' id='$valuename'><br />\n";
					break;
					default:
					echo "     <select name='method$inputname' id='method$inputname'>\n";
					echo "      <option selected='selected' value='in'>"._("Equals")."</option>\n";
					echo "      <option value='notin'>"._("Not Equals")."</option>\n";
					echo "     </select><br />\n";
					echo "     <select multiple size='5' name='$inputname' id='$valuename'>\n";
					foreach($part['answers'] as $answer) {
						echo "      <option value='".$answer['code']."'>".$answer['answer']."</option>\n";
					}
					echo "     </select><br />\n";
					break;
				}
				echo "     <input type='button' value='"._("Add")."' onClick='addCondition(\"$inputname\")'>";
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
	<table width='99%' align='center'>
     <tr>
      <th>
       <? echo _("Filter Conditions") ?>
      </th>
     </tr>
     <tr>
      <td style='text-align: center'>
       <input type='hidden' name='filter[]' id='filter'>
       <select multiple name='conditions' id='conditions' size='5' style='width: 600'>
        <?
        if(isset($_POST['filter'])) {
		 foreach($_POST['filter'] as $condition) {
		  $bits=explode(",", $condition);
		  //print_r($bits);
		  foreach($bits as $bit) {
		    echo "      <option value='$bit'>".$bit."</option>\n";
		  }
		 }
		}
        ?>
	<tr>
	<th>
	<? echo _("Filter Conditions") ?>
	</th>
	</tr>
	<tr>
	<td style='text-align: center'>
	<select multiple name='conditions[]' id='conditions' size='5' style='width: 600'>
	<?
	if(isset($_POST['conditions'])) {
		foreach($_POST['conditions'] as $condition) {
			echo "      <option value='$condition'>".$condition."</option>\n";
		}
	}
	?>
	</select><br />
	<input type='button' value='Remove' id='removecondition' onClick='removeCondition()' />
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
			"answer"=>_("Short free text"),
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
			"answer"=>_("Yes"),
			"default_answer"=>"",
			"sortorder"=>0);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"U",
			"answer"=>_("Uncertain"),
			"default_answer"=>"",
			"sortorder"=>1);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"N",
			"answer"=>_("No"),
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
			"answer"=>_("Increase"),
			"default_answer"=>"",
			"sortorder"=>0);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"S",
			"answer"=>_("Same"),
			"default_answer"=>"",
			"sortorder"=>1);
			$output[$i]["answers"][]=array("qid"=>$qid,
			"code"=>"D",
			"answer"=>_("Decrease"),
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
		"answer"=>_("Yes"),
		"default_value"=>"",
		"sortorder"=>0),
		1=>array("qid"=>$qid,
		"code"=>"N",
		"answer"=>_("No"),
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
		"answer"=>_("Male"),
		"default_value"=>"",
		"sortorder"=>0),
		array("qid"=>$qid,
		"code"=>"F",
		"answer"=>_("Female"),
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
		"answer"=>_("Date"),
		"default_value"=>"",
		"sortorder"=>1))
		);
		break;
		case "N": //Numerical
		$output[] = array("part"=>$question,
		"id"=>"",
		"answers"    =>array(0=>array("qid"=>$qid,
		"code"=>"",
		"answer"=>_("Numerical"),
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
		"answer"=>_("Short free text"),
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
		"answer"=>_("Long free text"),
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
		"answer"=>_("Huge free text"),
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