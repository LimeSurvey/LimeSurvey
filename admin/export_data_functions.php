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

/**
 * Strips html tags and replaces new lines
 *
 * @param $string
 * @return $string
 */
function strip_tags_full($string) {
	$string=html_entity_decode_php4($string, ENT_QUOTES, "UTF-8");
	//combining these into one mb_ereg_replace call ought to speed things up
	$string = str_replace(array("\r\n","\r","\n",'-oth-'), '', $string);
	//The backslashes must be escaped twice, once for php, and again for the regexp
	$string = str_replace("'|\\\\'", "&apos;", $string);
	return strip_tags($string);
}

/**
 * Returns true if passed $value is numeric
 *
 * @param $value
 * @return bool
 */
function my_is_numeric($value)  {
	$american = preg_match ("/^(-){0,1}([0-9]+)(,[0-9][0-9][0-9])*([.][0-9]){0,1}([0-9]*)$/" ,$value) == 1;
	$world = preg_match ("/^(-){0,1}([0-9]+)(.[0-9][0-9][0-9])*([,][0-9]){0,1}([0-9]*)$/" ,$value) == 1;
	return ($american or $world);
}

/**
 * Creates a fieldmap with all information necessary to output the fields
 *
 * @return array
 */

function spss_fieldmap($prefix = 'V') {
	global $surveyid, $dbprefix, $typeMap, $connect, $clang;
	global $tokensexist, $surveyprivate, $token_fields;

	$fieldmap = createFieldMap($surveyid, 'full');		//Create a FULL fieldmap

	#See if tokens are being used
	$tablelist = $connect->MetaTables() or safe_die ("Error getting table list<br />".$connect->ErrorMsg());
	foreach ($tablelist as $tbl)
	{
		if ($tbl == "{$dbprefix}tokens_$surveyid") {$tokensexist =  1;}
	}

	#Lookup the names of the attributes
	$query="SELECT sid, 'attribute1', 'attribute2', private, language FROM {$dbprefix}surveys WHERE sid=$surveyid";
	$result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());  //Checked
	$num_results = $result->RecordCount();
	$num_fields = $num_results;
	# Build array that has to be returned
	for ($i=0; $i < $num_results; $i++) {
		$row = $result->FetchRow();
		if ($row['attribute1']) {$attr1_name = $row['attribute1'];} else {$attr1_name = $clang->gT('Attribute 1');}
		if ($row['attribute2']) {$attr2_name = $row['attribute2'];} else {$attr2_name = $clang->gT('Attribute 2');}
		$surveyprivate=$row['private'];
		$language=$row['language'];
	}

	$fieldno=0;

	if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {

		$tablefieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
		foreach ($tablefieldnames as $tokenfieldname) {
			$token_fields[]=$tokenfieldname;
		}
		if (in_array('firstname', $token_fields)) {
			$fields[$fieldno++]=array('id'=>"$prefix$fieldno" ,'name'=>$clang->gT('First Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
		}
		if (in_array('lastname', $token_fields)) {
			$fields[$fieldno++]=array('id'=>"$prefix$fieldno",'name'=> $clang->gT('Last Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
		}
		if (in_array('email', $token_fields)) {
			$fields[$fieldno++]=array('id'=>"$prefix$fieldno",'name'=> $clang->gT('Email'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
		}
        $tokenattributes=GetTokenFieldsAndNames($surveyid,true);
        foreach ($tokenattributes as $attributefield=>$attributedescription)
        {
            if (in_array($attributefield, $token_fields)) {
                $fields[$fieldno++]=array('id'=>"$prefix$fieldno",'name'=>$attributedescription,'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
            }
        }
	} else {
		$fields=array();
	}

	$tempArray = array();
	$fieldnames = array_values($connect->MetaColumnNames("{$dbprefix}survey_$surveyid", true));
	$num_results = count($fieldnames);
	$num_fields = $num_results;
	$diff = 0;
	# Build array that has to be returned
	for ($i=0; $i < $num_results; $i++) {
		#Conditions for SPSS fields:
		# - Length may not be longer than 8 characters
		# - Name may not begin with a digit
		$fieldname = $fieldnames[$i];
		$fieldtype = '';
		$val_size = 1;
		$hide = 0;
		 
		#Determine field type
		if ($fieldname=='submitdate' || $fieldname=='startdate' || $fieldname == 'datestamp') {
			$fieldtype = 'DATETIME23.2';
			$ftype = 'DATETIME';
		} elseif ($fieldname=='startlanguage') {
			$fieldtype = 'A';
			$val_size = 19;
		} elseif ($fieldname=='token') {
			$fieldtype = 'A';
			$ftype = 'VARCHAR';
			$val_size = 16;
		} elseif ($fieldname=='id') {
			$fieldtype = 'F';
			$ftype = 'ID';
			$val_size = 7; //Arbitrarilty restrict to 9,999,999 (7 digits) responses/survey
		} elseif ($fieldname == 'ipaddr') {
			$fieldtype = 'A';
			$ftype = 'IP';
			$val_size = '15';
		} elseif ($fieldname == 'refurl') {
			$fieldtype = 'A';
			$ftype = 'REFURL';
			$val_size = 255;
		}
		 
		#Get qid (question id)
		$code='';
		$noQID = Array('id', 'token', 'datestamp', 'submitdate', 'startdate', 'startlanguage', 'ipaddr', 'refurl');
		if (in_array($fieldname, $noQID) || substr($fieldname,0,10)=='attribute_'){
			$qid = 0;
			$varlabel = $fieldname;
			$ftitle = $fieldname;
		} else{
			//GET FIELD DATA
			$fielddata=arraySearchByKey($fieldname, $fieldmap, 'fieldname', 1);
			$qid=$fielddata['qid'];
			$ftype=$fielddata['type'];
			$fsid=$fielddata['sid'];
			$fgid=$fielddata['gid'];
			$code=mb_substr($fielddata['fieldname'],strlen($fsid."X".$fgid."X".$qid));
			$varlabel=$fielddata['question'];
			$ftitle=$fielddata['title'];
			if (!is_null($code) && $code<>"" ) $ftitle .= "_$code";
			if (isset($typeMap[$ftype]['size'])) $val_size = $typeMap[$ftype]['size'];
			if($fieldtype == '') $fieldtype = $typeMap[$ftype]['SPSStype'];
			if (isset($typeMap[$ftype]['hide'])) {
				$hide = $typeMap[$ftype]['hide'];
				$diff++;
			}
				
		}
		$fieldno++;
		$fid = $fieldno - $diff;
		$lsLong = isset($typeMap[$ftype]["name"])?$typeMap[$ftype]["name"]:$ftype;
		$tempArray = array('id'=>"$prefix$fid",'name'=>mb_substr($fieldname, 0, 8),
		    'qid'=>$qid, 'code'=>$code,'SPSStype'=>$fieldtype,'LStype'=>$ftype,"LSlong"=>$lsLong,
		    'ValueLabels'=>'','VariableLabel'=>$varlabel,"sql_name"=>$fieldname,"size"=>$val_size,
		    'title'=>$ftitle, 'hide'=>$hide);
		$fields[] = $tempArray;
	}
	return $fields;
}

/**
 * Creates a query string with all fields for the export
 *
 * @return string
 */
function spss_getquery() {
	global $tokensexist, $surveyprivate, $dbprefix, $surveyid, $token_fields;

	if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {
		$query="SELECT {$dbprefix}tokens_$surveyid.firstname   ,
		{$dbprefix}tokens_$surveyid.lastname    ,
		{$dbprefix}tokens_$surveyid.email";
        $tokenattributes=GetTokenFieldsAndNames($surveyid,true);
        foreach ($tokenattributes as $attributefield=>$attributedescription)
		    if (in_array($attributefield, $token_fields)) {
			    $query .= ",\n		{$dbprefix}tokens_$surveyid.$attributefield";
		    }
        }
		$query .= ",\n	       {$dbprefix}survey_$surveyid.*
	    FROM {$dbprefix}survey_$surveyid
	    LEFT JOIN {$dbprefix}tokens_$surveyid ON {$dbprefix}survey_$surveyid.token = {$dbprefix}tokens_$surveyid.token";
		if (incompleteAnsFilterstate() === true)
		{
			$query .= " WHERE {$dbprefix}survey_$surveyid.submitdate is not null ";
		}
	} else {
		$query = "SELECT *
	    FROM {$dbprefix}survey_$surveyid";
		if (incompleteAnsFilterstate() === true)
		{
			$query .= ' WHERE submitdate is not null ';
		}
	}
	return $query;
}
?>