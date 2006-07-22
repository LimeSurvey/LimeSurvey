<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
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
//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die ("Cannot run this script directly [database.php]");}

if (!isset($action)) {$action=returnglobal('action');}

if (get_magic_quotes_gpc())
    $_POST  = array_map('stripslashes', $_POST);

function db_quote($str)
{
	global $connect;
	return $connect->escape($str);
}


if ($action == "delattribute")
    {
    settype($_POST['qaid'], "integer");
    $query = "DELETE FROM {$dbprefix}question_attributes
              WHERE qaid={$_POST['qaid']} AND qid={$_POST['qid']}";
    $result=$connect->Execute($query) or die("Couldn't delete attribute<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
    }
elseif ($action == "addattribute")
    {
    if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
        {
        $query = "INSERT INTO {$dbprefix}question_attributes
                  (qid, attribute, value)
                  VALUES (?, ?, ?)";
        $result = $connect->Execute($query, $_POST['qid'], $_POST['attribute_name'], $_POST['attribute_value']) or die("Error<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
        }
    }
elseif ($action == "editattribute")
    {
    if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
        {
        settype($_POST['qaid'], "integer");
        $query = "UPDATE {$dbprefix}question_attributes
                  SET value=? WHERE qaid=? AND qid=?";
        $result = $connect->Execute($query, $_POST['attribute_value'], $_POST['qaid'], $_POST['qid']) or die("Error<br />".htmlspecialchars($query)."<br />".htmlspecialchars($connect->ErrorMsg()));
        }
    }
elseif ($action == "insertnewgroup")
    {
    if (!$_POST['group_name'])
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPNAME."\")\n //-->\n</script>\n";
        }
    else
        {
        $_POST  = array_map('db_quote', $_POST);

        $query = "INSERT INTO {$dbprefix}groups (sid, group_name, description) VALUES ('{$_POST['sid']}', '{$_POST['group_name']}', '{$_POST['description']}')";
        $result = $connect->Execute($query);

        if ($result)
            {
            //echo "<script type=\"text/javascript\">\n<!--\n alert(\"New group ({$_POST['group_name']}) has been created for survey id $surveyid\")\n //-->\n</script>\n";
            $query = "SELECT gid FROM {$dbprefix}groups WHERE group_name='{$_POST['group_name']}' AND sid={$_POST['sid']}";
            $result = db_execute_assoc($query);
            while ($res = $result->FetchRow()) {$gid = $res['gid'];}
            $groupselect = getgrouplist($gid);
            }
        else
            {
            echo _ERROR.": The database reported the following error:<br />\n";
            echo "<font color='red'>" . htmlspecialchars($connect->ErrorMsg()) . "</font>\n";
            echo "<pre>".htmlspecialchars($query)."</pre>\n";
            echo "</body>\n</html>";
            exit;
            }
        }
    }

elseif ($action == "updategroup")
    {
    $_POST  = array_map('db_quote', $_POST);

    $ugquery = "UPDATE {$dbprefix}groups SET group_name='{$_POST['group_name']}', description='{$_POST['description']}' WHERE sid={$_POST['sid']} AND gid={$_POST['gid']}";
    $ugresult = $connect->Execute($ugquery);
    if ($ugresult)
        {
        //echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Group ($group_name) has been updated!\")\n //-->\n</script>\n";
        $groupsummary = getgrouplist($_POST['gid']);
        }
    else
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPUPDATE."\")\n //-->\n</script>\n";
        }

    }

elseif ($action == "delgroupnone")
    {
    if (!isset($gid)) {$gid=returnglobal('gid');}
    $query = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid AND gid=$gid";
    $result = $connect->Execute($query);
    if ($result)
        {
        $gid = "";
        $groupselect = getgrouplist($gid);
        }
    else
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\n$error\")\n //-->\n</script>\n";
        }
    }

elseif ($action == "delgroup")
    {
    if (!isset($gid)) {$gid=returnglobal('gid');}
    $query = "SELECT qid FROM {$dbprefix}groups, {$dbprefix}questions WHERE {$dbprefix}groups.gid={$dbprefix}questions.gid AND {$dbprefix}groups.gid=$gid";
    if ($result = db_execute_assoc($query))
        {
        if (!isset($total)) {$total=0;}
        $qtodel=$result->RecordCount();
        while ($row=$result->FetchRow())
            {
            $dquery = "DELETE FROM {$dbprefix}conditions WHERE qid={$row['qid']}";
            if ($dresult=$connect->Execute($dquery)) {$total++;}
            $dquery = "DELETE FROM {$dbprefix}answers WHERE qid={$row['qid']}";
            if ($dresult=$connect->Execute($dquery)) {$total++;}
            $dquery = "DELETE FROM {$dbprefix}questions WHERE qid={$row['qid']}";
            if ($dresult=$connect->Execute($dquery)) {$total++;}
            }
        if ($total != $qtodel*3)
            {
            echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\")\n //-->\n</script>\n";
            }
        }
    $query = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid AND gid=$gid";
    $result = $connect->Execute($query);
    if ($result)
        {
        $gid = "";
        $groupselect = getgrouplist($gid);
        }
    else
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\n$error\")\n //-->\n</script>\n";
        }
    }

elseif ($action == "insertnewquestion")
	{
	if (!$_POST['title'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_CODE."\")\n //-->\n</script>\n";		
		}
	else
    {
    $_POST  = array_map('db_quote', $_POST);

    if (!isset($_POST['lid']) || $_POST['lid'] == '') {$_POST['lid']="0";}
    $query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, preg, help, other, mandatory, lid)"
            ." VALUES ('{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}',"
            ." '{$_POST['question']}', '{$_POST['preg']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}')";
    $result = $connect->Execute($query);
    if (!$result)
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWQUESTION."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
        }
    else
        {
        $query = "SELECT qid FROM {$dbprefix}questions ORDER BY qid DESC LIMIT 1"; //get last question id
        $result=db_execute_assoc($query);
        while ($row=$result->FetchRow()) {$qid = $row['qid'];}
        }
    if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
        {
        $query = "INSERT INTO {$dbprefix}question_attributes
                  (qid, attribute, value)
                  VALUES
                  ($qid, '".$_POST['attribute_name']."', '".$_POST['attribute_value']."')";
        $result = $connect->Execute($query);
        }
    }
    }
elseif ($action == "renumberquestions")
    {
    //Automatically renumbers the "question codes" so that they follow
    //a methodical numbering method
    $question_number=1;
    $group_number=0;
    $gselect="SELECT *\n"
            ."FROM {$dbprefix}questions, {$dbprefix}groups\n"
            ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid\n"
            ."AND {$dbprefix}questions.sid=$surveyid\n"
            ."ORDER BY group_name, title";
    $gresult=db_execute_assoc($gselect) or die ("Error: ".htmlspecialchars($connect->ErrorMsg()));
    $grows = array(); //Create an empty array in case FetchRow does not return any rows
    while ($grow = $gresult->FetchRow()) {$grows[] = $grow;} // Get table output into array
    usort($grows, 'CompareGroupThenTitle');
    foreach($grows as $grow)
        {
        //Go through all the questions
        if ((isset($_GET['style']) && $_GET['style']=="bygroup") && (!isset($groupname) || $groupname != $grow['group_name']))
            {
            $question_number=1;
            $group_number++;
            }
        //echo "GROUP: ".$grow['group_name']."<br />";
        $usql="UPDATE {$dbprefix}questions\n"
            ."SET title='".str_pad($question_number, 4, "0", STR_PAD_LEFT)."'\n"
            ."WHERE qid=".$grow['qid'];
        //echo "[$sql]";
        $uresult=$connect->Execute($usql) or die("Error: ".htmlspecialchars($connect->ErrorMsg()));
        $question_number++;
        $groupname=$grow['group_name'];
        }
    }

elseif ($action == "updatequestion")
    {
    $cqquery = "SELECT type FROM {$dbprefix}questions WHERE qid={$_POST['qid']}";
    $cqresult=db_execute_assoc($cqquery) or die ("Couldn't get question type to check for change<br />".htmlspecialchars($cqquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
    while ($cqr=$cqresult->FetchRow()) {$oldtype=$cqr['type'];}

	global $change;
	$change = "0";
	if (($oldtype == "J" && $_POST['type']== "I") || ($oldtype == "I" && $_POST['type']== "J") || ($oldtype == $_POST['type']))
		{
		$change = "1";
		}

    if ($oldtype != $_POST['type'])
        {
        //Make sure there are no conditions based on this question, since we are changing the type
        $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']}";
        $ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
        $cccount=$ccresult->RecordCount();
        while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
        if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
        }
    $_POST  = array_map('db_quote', $_POST);
    if (isset($cccount) && $cccount)
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONTYPECONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
        }
    else
        {
        if (isset($_POST['gid']) && $_POST['gid'] != "")
            {
            $uqquery = "UPDATE {$dbprefix}questions "
                    . "SET type='{$_POST['type']}', title='{$_POST['title']}', "
                    . "question='{$_POST['question']}', preg='{$_POST['preg']}', help='{$_POST['help']}', "
                    . "gid='{$_POST['gid']}', other='{$_POST['other']}', "
                    . "mandatory='{$_POST['mandatory']}'";
            if (isset($_POST['lid']) && trim($_POST['lid'])!="") 
            	{ 
            		$uqquery.=", lid='{$_POST['lid']}' ";
            	}
            $uqquery.= "WHERE sid={$_POST['sid']} AND qid={$_POST['qid']}";
            $uqresult = $connect->Execute($uqquery) or die ("Error Update Question: ".htmlspecialchars($uqquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
            if (!$uqresult)
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONUPDATE."\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                }
		   if ($oldtype !=  $_POST['type'] & $change == "0")
		       {
				$query = "DELETE FROM {$dbprefix}answers WHERE qid={$_POST['qid']}"; 
				$result = mysql_query($query);	
				if (!$result)
					{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERS."\n".mysql_error()."\")\n //-->\n</script>\n";
					}
			   }	
            }
        else
            {
            echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONUPDATE."\")\n //-->\n</script>\n";
            }
        }
    }

elseif ($action == "copynewquestion")
	{
	if (!$_POST['title'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_CODE."\")\n //-->\n</script>\n";		
		}
	else
    {
    $_POST  = array_map('db_quote', $_POST);
    if (!isset($_POST['lid']) || $_POST['lid']=='') {$_POST['lid']=0;}
    $query = "INSERT INTO {$dbprefix}questions (sid, gid, type, title, question, help, other, mandatory, lid) VALUES ('{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}', '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}')";
    $result = $connect->Execute($query);
    $newqid = $connect->Insert_ID();
    if (!$result)
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWQUESTION."\\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
        }
    if (returnglobal('copyanswers') == "Y")
        {
        $q1 = "SELECT * FROM {$dbprefix}answers WHERE qid="
            . returnglobal('oldqid')
            . " ORDER BY code";
        $r1 = db_execute_assoc($q1);
        while ($qr1 = $r1->FetchRow())
            {
            $qr1 = array_map('db_quote', $qr1);
            $i1 = "INSERT INTO {$dbprefix}answers (qid, code, answer, default_value, sortorder) "
                . "VALUES ('$newqid', '{$qr1['code']}', "
                . "'{$qr1['answer']}', '{$qr1['default_value']}', "
                . "'{$qr1['sortorder']}')";
            $ir1 = $connect->Execute($i1);
            }
        }
    if (returnglobal('copyattributes') == "Y")
        {
        $q1 = "SELECT * FROM {$dbprefix}question_attributes
               WHERE qid=".returnglobal('oldqid')."
               ORDER BY qaid";
        $r1 = db_execute_assoc($q1);
        while($qr1 = $r1->FetchRow())
            {
            $qr1 = array_map('db_quote', $qr1);
            $i1 = "INSERT INTO {$dbprefix}question_attributes
                   (qid, attribute, value)
                   VALUES ('$newqid',
                   '{$qr1['attribute']}',
                   '{$qr1['value']}')";
            $ir1 = $connect->Execute($i1);
            } // while
        }
    }
    }
elseif ($action == "delquestion")
    {
    if (!isset($qid)) {$qid=returnglobal('qid');}
    //check if any other questions have conditions which rely on this question. Don't delete if there are.
    $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid=$qid";
    $ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
    $cccount=$ccresult->RecordCount();
    while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
    if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
    if ($cccount) //there are conditions dependant on this question
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
        }
    else
        {
        //see if there are any conditions/attributes/answers for this question, and delete them now as well
        $cquery = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
        $cresult = $connect->Execute($cquery);
        $query = "DELETE FROM {$dbprefix}question_attributes WHERE qid=$qid";
        $result = $connect->Execute($query);
        $cquery = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
        $cresult = $connect->Execute($cquery);
        $query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
        $result = $connect->Execute($query);
        if ($result)
            {
            $qid="";
            $_POST['qid']="";
            $_GET['qid']="";
            }
        else
            {
            echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELETE."\n$error\")\n //-->\n</script>\n";
            }
        }
    }

elseif ($action == "delquestionall")
    {
    if (!isset($qid)) {$qid=returnglobal('qid');}
    //check if any other questions have conditions which rely on this question. Don't delete if there are.
    $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_GET['qid']}";
    $ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this question<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
    $cccount=$ccresult->RecordCount();
    while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
    if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
    if ($cccount) //there are conditions dependant on this question
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
        }
    else
        {
        //First delete all the answers
        if (!isset($total)) {$total=0;}
        $query = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
        if ($result=$connect->Execute($query)) {$total++;}
        $query = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
        if ($result=$connect->Execute($query)) {$total++;}
        $query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
        if ($result=$connect->Execute($query)) {$total++;}
        }
        if ($total==3)
            {
            $qid="";
            $_POST['qid']="";
            $_GET['qid']="";
            }
        else
            {
            echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELETE."\n$error\")\n //-->\n</script>\n";
            }
    }

elseif ($action == "modanswer")
    {
    if ((!isset($_POST['olddefault']) || ($_POST['olddefault'] != $_POST['default']) && $_POST['default'] == "Y") || ($_POST['default'] == "Y" && $_POST['ansaction'] == _AL_ADD)) //TURN ALL OTHER DEFAULT SETTINGS TO NO
        {
        $query = "UPDATE {$dbprefix}answers SET default_value = 'N' WHERE qid={$_POST['qid']}";
        $result=$connect->Execute($query) or die("Error occurred updating default_value settings");
        }
    if (isset($_POST['code'])) $_POST['code'] = db_quote($_POST['code']);
    if (isset($_POST['oldcode'])) {$_POST['oldcode'] = db_quote($_POST['oldcode']);}
    if (isset($_POST['answer'])) $_POST['answer'] = db_quote($_POST['answer']);
    if (isset($_POST['oldanswer'])) {$_POST['oldanswer'] = db_quote($_POST['oldanswer']);}
    if (isset($_POST['default_value'])) {$_POST['oldanswer'] = db_quote($_POST['default_value']);}
    switch ($_POST['ansaction'])
        {
        case _AL_FIXSORT:
            fixsortorder($_POST['qid']);
            break;
        case _AL_SORTALPHA:
            $uaquery = "SELECT * FROM {$dbprefix}answers WHERE qid='{$_POST['qid']}' ORDER BY answer";
            $uaresult = db_execute_assoc($uaquery) or die("Cannot get answers<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
            while($uarow=$uaresult->FetchRow())
                {
                $orderedanswers[]=array("qid"=>$uarow['qid'],
                                        "code"=>$uarow['code'],
                                        "answer"=>$uarow['answer'],
                                        "default_value"=>$uarow['default_value'],
                                        "sortorder"=>$uarow['sortorder']);
                } // while
            $i=0;
            foreach ($orderedanswers as $oa)
                {
                $position=sprintf("%05d", $i);
                $upquery = "UPDATE {$dbprefix}answers SET sortorder='$position' WHERE qid='{$oa['qid']}' AND code='{$oa['code']}'";
                $upresult = $connect->Execute($upquery);
                $i++;
                } // foreach
            break;
        case _AL_ADD:
            if ((trim($_POST['code'])=='') || (trim($_POST['answer'])==''))
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWANSWERMISSING."\")\n //-->\n</script>\n";
                }
            else
                {
                $uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
                $uaresult = $connect->Execute($uaquery) or die ("Cannot check for duplicate codes<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                $matchcount = $uaresult->RecordCount();
                if ($matchcount) //another answer exists with the same code
                    {
                    echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWANSWERDUPLICATE."\")\n //-->\n</script>\n";
                    }
                else
                    {
                    $cdquery = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, default_value) VALUES ('{$_POST['qid']}', '{$_POST['code']}', '{$_POST['answer']}', '{$_POST['sortorder']}', '{$_POST['default']}')";
                    $cdresult = $connect->Execute($cdquery) or die ("Couldn't add answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                    }
                }
            break;
        case _AL_SAVE:
            if ((trim($_POST['code'])=='') || (trim($_POST['answer'])==''))
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATEMISSING."\")\n //-->\n</script>\n";
                }
            else
                {
                if ($_POST['code'] != $_POST['oldcode']) //code is being changed. Check against other codes and conditions
                    {
                    $uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
                    $uaresult = $connect->Execute($uaquery) or die ("Cannot check for duplicate codes<br />".htmlspecialchars($uaquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                    $matchcount = $uaresult->RecordCount();
                    $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
                    $ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this answer<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                    $cccount=$ccresult->RecordCount();
                    while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
                    if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
                    }
                if (isset($matchcount) && $matchcount) //another answer exists with the same code
                    {
                    echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATEDUPLICATE."\")\n //-->\n</script>\n";
                    }
                else
                    {
                    if (isset($cccount) && $cccount) // there are conditions dependent upon this answer to this question
                        {
                        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATECONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
                        }
                    else
                        {
                        $cdquery = "UPDATE {$dbprefix}answers SET qid='{$_POST['qid']}', code='{$_POST['code']}', answer='{$_POST['answer']}', sortorder='{$_POST['sortorder']}', default_value='{$_POST['default']}' WHERE code='{$_POST['oldcode']}' AND qid='{$_POST['qid']}'";
                        $cdresult = $connect->Execute($cdquery) or die ("Couldn't update answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                        }
                    }
                }
            break;
        case _AL_DEL:
            $ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
            $ccresult = db_execute_assoc($ccquery) or die ("Couldn't get list of cqids for this answer<br />".htmlspecialchars($ccquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
            $cccount=$ccresult->RecordCount();
            while ($ccr=$ccresult->FetchRow()) {$qidarray[]=$ccr['qid'];}
            if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
            if ($cccount)
                {
                echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
                }
            else
                {
                $cdquery = "DELETE FROM {$dbprefix}answers WHERE code='{$_POST['oldcode']}' AND answer='{$_POST['oldanswer']}' AND qid='{$_POST['qid']}'";
                $cdresult = $connect->Execute($cdquery) or die ("Couldn't update answer<br />".htmlspecialchars($cdquery)."<br />".htmlspecialchars($connect->ErrorMsg()));
                }
            fixsortorder($qid);
            break;
        case _AL_UP:
            $newsortorder=sprintf("%05d", $_POST['sortorder']-1);
            $replacesortorder=$newsortorder;
            $newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='$newreplacesortorder'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            break;
        case _AL_DN:
            $newsortorder=sprintf("%05d", $_POST['sortorder']+1);
            $replacesortorder=$newsortorder;
            $newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
            $newreplace2=sprintf("%05d", $_POST['sortorder']);
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='{$_POST['sortorder']}'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            $cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
            $cdresult=$connect->Execute($cdquery) or die(htmlspecialchars($connect->ErrorMsg()));
            break;
        default:
            break;
        }
    }


elseif ($action == "insertCSV")
	{
		if (get_magic_quotes_gpc() == "0")
			{
			$_POST['svettore'] = addcslashes($_POST['svettore'], "'");
			}
		$vettore = explode ("^", $svettore );
	$band = 0;
	$indice = $_POST['numcol'] - 1;
	foreach ($vettore as $k => $v)
		{
		$vettoreriga = explode ($elem, $v);
		if ($band == 1)
			{
			$valore = $vettoreriga[$indice];
			$valore = trim($valore);
			if (!is_null($valore))
				{ 
				$cdquery = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, default_value) VALUES ('{$_POST['qid']}', '$k', '$valore', '00000', 'N')";
				$cdresult = $cdresult=mysql_query($cdquery) or die(mysql_error());
				}
			}
		$band = 1;
		}
	}

elseif ($action == "insertnewsurvey")
    {
    if ($_POST['url'] == "http://") {$_POST['url']="";}
    if (!$_POST['short_title'])
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWSURVEY_TITLE."\")\n //-->\n</script>\n";
        }
    else
        {
        $_POST  = array_map('db_quote', $_POST);
        if (trim($_POST['expires'])=="")
        {
        	$_POST['expires']='1980-01-01';
        }
          else 
          	{
        	$_POST['expires']="'".$_POST['expires']."'";
        	}
        // Get random ids until one is found that is not used
        do
          {
          $surveyid = getRandomID();
          $isquery = "SELECT sid FROM {$dbprefix}surveys WHERE sid=$surveyid";
          $isresult = db_execute_assoc($isquery);
          }
        while ($isresult->RecordCount()>0);

        $isquery = "INSERT INTO {$dbprefix}surveys\n"
                  . "(sid, short_title, description, admin, active, welcome, useexpiry, expires, "
                  . "adminemail, private, faxto, format, template, url, urldescrip, "
                  . "language, datestamp, ipaddr, refurl, usecookie, notification, allowregister, attribute1, attribute2, "
                  . "email_invite_subj, email_invite, email_remind_subj, email_remind, "
                  . "email_register_subj, email_register, email_confirm_subj, email_confirm, "
                  . "allowsave, autoredirect, allowprev)\n"
                  . "VALUES ($surveyid, '{$_POST['short_title']}', '{$_POST['description']}',\n"
                  . "'{$_POST['admin']}', 'N', '".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
                  . "'{$_POST['useexpiry']}',{$_POST['expires']}, '{$_POST['adminemail']}', '{$_POST['private']}',\n"
                  . "'{$_POST['faxto']}', '{$_POST['format']}', '{$_POST['template']}', '{$_POST['url']}',\n"
                  . "'{$_POST['urldescrip']}', '{$_POST['language']}', '{$_POST['datestamp']}', '{$_POST['ipaddr']}', '{$_POST['refurl']}',\n"
                  . "'{$_POST['usecookie']}', '{$_POST['notification']}', '{$_POST['allowregister']}',\n"
                  . "'{$_POST['attribute1']}', '{$_POST['attribute2']}', '{$_POST['email_invite_subj']}',\n"
                  . "'{$_POST['email_invite']}', '{$_POST['email_remind_subj']}',\n"
                  . "'{$_POST['email_remind']}', '{$_POST['email_register_subj']}',\n"
                  . "'{$_POST['email_register']}', '{$_POST['email_confirm_subj']}',\n"
                  . "'{$_POST['email_confirm']}', \n"
                  . "'{$_POST['allowsave']}', '{$_POST['autoredirect']}', '{$_POST['allowprev']}')";
        $isresult = $connect->Execute($isquery);
        if ($isresult)
            {
            $surveyselect = getsurveylist();
            }
        else
            {
            $errormsg=_DB_FAIL_NEWSURVEY." - ".$connect->ErrorMsg();
            echo "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
            echo htmlspecialchars($isquery);
            }
        }
    }

elseif ($action == "updatesurvey")
    {
    if ($_POST['url'] == "http://") {$_POST['url']="";}
    $_POST  = array_map('db_quote', $_POST);

   if (trim($_POST['expires'])=="")
   		{
        	$_POST['expires']='1980-01-01';
        }
          else 
          	{
        	$_POST['expires']="'".$_POST['expires']."'";
        	}

    $usquery = "UPDATE {$dbprefix}surveys \n"
              . "SET short_title='{$_POST['short_title']}', description='{$_POST['description']}',\n"
              . "admin='{$_POST['admin']}', welcome='".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
              . "useexpiry='{$_POST['useexpiry']}', expires={$_POST['expires']}, adminemail='{$_POST['adminemail']}',\n"
              . "private='{$_POST['private']}', faxto='{$_POST['faxto']}',\n"
              . "format='{$_POST['format']}', template='{$_POST['template']}',\n"
              . "url='{$_POST['url']}', urldescrip='{$_POST['urldescrip']}',\n"
              . "language='{$_POST['language']}', datestamp='{$_POST['datestamp']}', ipaddr='{$_POST['ipaddr']}', refurl='{$_POST['refurl']}',\n"
              . "usecookie='{$_POST['usecookie']}', notification='{$_POST['notification']}',\n"
              . "allowregister='{$_POST['allowregister']}', attribute1='{$_POST['attribute1']}',\n"
              . "attribute2='{$_POST['attribute2']}', email_invite_subj='{$_POST['email_invite_subj']}',\n"
              . "email_invite='{$_POST['email_invite']}', email_remind_subj='{$_POST['email_remind_subj']}',\n"
              . "email_remind='{$_POST['email_remind']}', email_register_subj='{$_POST['email_register_subj']}',\n"
              . "email_register='{$_POST['email_register']}', email_confirm_subj='{$_POST['email_confirm_subj']}',\n"
              . "email_confirm='{$_POST['email_confirm']}', allowsave='{$_POST['allowsave']}',\n"
              . "autoredirect='{$_POST['autoredirect']}', allowprev='{$_POST['allowprev']}'\n"
              . "WHERE sid={$_POST['sid']}";
    $usresult = $connect->Execute($usquery) or die("Error updating<br />".htmlspecialchars($usquery)."<br /><br /><strong>".htmlspecialchars($connect->ErrorMsg()));
    if ($usresult)
        {
        $surveyselect = getsurveylist();
        }
    else
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_SURVEYUPDATE."\n".$connect->ErrorMsg() ." ($usquery)\")\n //-->\n</script>\n";
        }
    }

elseif ($action == "delsurvey") //can only happen if there are no groups, no questions, no answers etc.
    {
    $query = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
    $result = $connect->Execute($query);
    if ($result)
        {
        $surveyid = "";
        $surveyselect = getsurveylist();
        }
    else
        {
        echo "<script type=\"text/javascript\">\n<!--\n alert(\"Survey id($surveyid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
        }
    }

else
    {
    echo "$action Not Yet Available!";
    }

?>
