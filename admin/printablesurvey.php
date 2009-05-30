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

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");
include_once(dirname(__FILE__)."/classes/tcpdf/extensiontcpdf.php"); 

// TEMP function for debugging
function try_debug($line)
{
	global $debug;
	if($debug > 0)
	{
		return '<!-- printablesurvey.php: '.$line.' -->';
	};
};
$surveyid = $_GET['sid'];

// PRESENT SURVEY DATAENTRY SCREEN
if(isset($_POST['printableexport']))
{
    $pdf = new PDF ($pdforientation,'mm','A4');
    $pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
    $pdf->AddPage();
}
// Set the language of the survey, either from GET parameter of session var
if (isset($_GET['lang']))
{
	$_GET['lang'] = preg_replace("/[^a-zA-Z0-9-]/", "", $_GET['lang']);
	if ($_GET['lang']) $surveyprintlang = $_GET['lang'];
} else
{
	$surveyprintlang=GetbaseLanguageFromSurveyid($surveyid);
}

// Setting the selected language for printout
$clang = new limesurvey_lang($surveyprintlang);

$desquery = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid) WHERE sid=$surveyid and surveyls_language=".$connect->qstr($surveyprintlang); //Getting data for this survey

$desresult = db_execute_assoc($desquery);
while ($desrow = $desresult->FetchRow())
{
	$template = $desrow['template'];
	$welcome = $desrow['surveyls_welcometext'];
	$surveyname = $desrow['surveyls_title'];
	$surveydesc = $desrow['surveyls_description'];
	$surveyactive = $desrow['active'];
	$surveytable = db_table_name("survey_".$desrow['sid']);
	$surveyexpirydate = $desrow['expires'];
	$surveystartdate = $desrow['startdate'];
	$surveyfaxto = $desrow['faxto'];
}
if(isset($_POST['printableexport'])){$pdf->titleintopdf($surveyname,$surveydesc);}


//define('PRINT_TEMPLATE' , '/templates/print/' , true);
if(is_file($templaterootdir.'/'.$template.'/print_survey.pstpl'))
{
	define('PRINT_TEMPLATE_DIR' , $templaterootdir.'/'.$template.'/' , true);
	define('PRINT_TEMPLATE_URL' , $rooturl.'/templates/'.$template.'/' , true);
}
else
{
	define('PRINT_TEMPLATE_DIR' , $templaterootdir.'/default/' , true);
	define('PRINT_TEMPLATE_URL' , $rooturl.'/templates/default/' , true);
}



$fieldmap=createFieldMap($surveyid);

$degquery = "SELECT * FROM ".db_table_name("groups")." WHERE sid='{$surveyid}' AND language='{$surveyprintlang}' ORDER BY ".db_table_name("groups").".group_order";
$degresult = db_execute_assoc($degquery);

if (!isset($surveyfaxto) || !$surveyfaxto and isset($surveyfaxnumber))
{
	$surveyfaxto=$surveyfaxnumber; //Use system fax number if none is set in survey.
}


if(isset($usepdfexport) && $usepdfexport == 1 && !in_array($surveyprintlang,$notsupportlanguages))
{
	$pdf_form = '
<form action="'.$scriptname.'?action=showprintablesurvey&amp;sid='.$surveyid.'&amp;lang='.$surveyprintlang.'" method="post">
	<input type="submit" value="'.$clang->gT('PDF Export').'"/>
	<input type="hidden" name="checksessionbypost" value="'.$_SESSION['checksessionpost'].'"/>
	<input type="hidden" name="printableexport" value="true"/>
</form>
';
}

$headelements = '
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<!--[if lt IE 7]>
		<script defer type="text/javascript" src="'.$rooturl.'/scripts/pngfix.js"></script>
<![endif]-->

		<script type="text/javascript" src="'.$rooturl.'/admin/scripts/tabpane/js/tabpane.js"></script>
		<script type="text/javascript" src="'.$rooturl.'/admin/scripts/tooltips.js"></script>
		<script type="text/javascript" src="'.$rooturl.'/admin/scripts/admin_core.js"></script>

		<link rel="stylesheet" type="text/css" media="all" href="'.$rooturl.'/scripts/calendar/calendar-blue.css" title="win2k-cold-1" />
';

$survey_output = array(
			 'SITENAME' => $sitename
			,'SURVEYNAME' => $surveyname
			,'SURVEYDESCRIPTION' => $surveydesc
			,'WELCOME' => $welcome
			,'THEREAREXQUESTIONS' => 0
			,'SUBMIT_TEXT' => $clang->gT("Submit Your Survey.")
			,'SUBMIT_BY' => $surveyexpirydate
			,'THANKS' => $clang->gT("Thank you for completing this survey.")
			,'PDF_FORM' => $pdf_form
			,'HEADELEMENTS' => $headelements
			,'TEMPLATEURL' => PRINT_TEMPLATE_URL
			,'FAXTO' => $surveyfaxto
			,'PRIVACY' => ''
			,'GROUPS' => ''
		);



if(!empty($surveyfaxto) && $surveyfaxto != '000-00000000') //If no fax number exists, don't display faxing information!
{
	$survey_output['FAX_TO'] = $clang->gT("Please fax your completed survey to:")." $surveyfaxto";
}

if ($surveystartdate!='')
{
    $survey_output['SUBMIT_BY'] = sprintf($clang->gT("Please submit by %s"), $surveyexpirydate);
}

/**
 * Output arrays:
 *	$survey_output  =       final vaiables for whole survey
 *		$survey_output['SITENAME'] = 
 *		$survey_output['SURVEYNAME'] = 
 *		$survey_output['SURVEY_DESCRIPTION'] = 
 *		$survey_output['WELCOME'] = 
 *		$survey_output['THEREAREXQUESTIONS'] = 
 *		$survey_output['PDF_FORM'] = 
 *		$survey_output['HEADELEMENTS'] = 
 *		$survey_output['TEMPLATEURL'] = 
 *		$survey_output['SUBMIT_TEXT'] = 
 *		$survey_output['SUBMIT_BY'] = 
 *		$survey_output['THANKS'] = 
 *		$survey_output['FAX_TO'] = 
 *		$survey_output['SURVEY'] = 	contains an array of all the group arrays
 *
 *	$groups[]       =       an array of all the groups output
 *		$group['GROUPNAME'] = 
 *		$group['GROUPDESCRIPTION'] = 
 *		$group['QUESTIONS'] = 	templated formatted content if $question is appended to this at the end of processing each question.
 *		$group['ODD_EVEN'] = 	class to differentiate alternate groups
 *		$group['SCENARIO'] = 
 *
 *	$questions[]    =       contains an array of all the questions within a group
 *		$question['QUESTION_CODE'] = 		content of the question code field
 *		$question['QUESTION_TEXT'] = 		content of the question field
 *		$question['QUESTION_SCENARIO'] = 		if there are conditions on a question, list the conditions.
 *		$question['QUESTION_MANDATORY'] = 	translated 'mandatory' identifier
 *		$question['QUESTION_CLASS'] = 		classes to be added to wrapping question div
 *		$question['QUESTION_TYPE_HELP'] = 		instructions on how to complete the question
 *		$question['QUESTION_MAN_MESSAGE'] = 	(not sure if this is used) mandatory error
 *		$question['QUESTION_VALID_MESSAGE'] = 	(not sure if this is used) validation error
 *		$question['ANSWER'] =        		contains formatted HTML answer
 *		$question['QUESTIONHELP'] = 		content of the question help field.
 *
 */

function populate_template( $template , $input  , $line = '')
{
	global $rootdir, $debug;
/**
 * A poor mans templating system.
 *
 * 	$template	template filename (path is privided by config.php)
 * 	$input		a key => value array containg all the stuff to be put into the template
 * 	$line	 	for debugging purposes only.
 *
 * Returns a formatted string containing template with
 * keywords replaced by variables.
 *
 * How:
 */
 	$full_path = PRINT_TEMPLATE_DIR.'print_'.$template.'.pstpl';
	$full_constant = 'TEMPLATE'.$template.'.pstpl';
	if(!defined($full_constant))
	{
		if(is_file($full_path))
		{
			define( $full_constant , file_get_contents($full_path));

			$template_content = constant($full_constant);
			$test_empty = trim($template_content);
			if(empty($test_empty))
			{
				return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
			};
		}
		else
		{
			define($full_constant , '');
			return "<!--\n\t$full_path is not a propper file or is missing.\n-->";
		};
	}
	else
	{
		$template_content = constant($full_constant);
		$test_empty = trim($template_content);
		if(empty($test_empty))
		{
			return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
		};
	};

	if(is_array($input))
	{
		foreach($input as $key => $value)
		{
			$find[] = '{'.$key.'}';
			$replace[] = $value;
		};
		return str_replace( $find , $replace , $template_content ); 
	}
	else
	{
		if($debug > 0)
		{
			if(!empty($line))
			{
				$line =  'LINE '.$line.': ';
			}
			return '<!-- '.$line.'There was nothing to put into the template -->'."\n";
		};
	};
};


function input_type_image( $type , $title = '' , $x = 40 , $y = 1 , $line = '' )
{
	global $rooturl, $rootdir;

	if($type == 'other' or $type == 'othercomment')
	{
		$x = 1;
	};
	$tail = substr($x , -1 , 1);
	switch($tail)
	{
		case '%':
		case 'm':
		case 'x':	$x_ = $x;
				break;
		default:	$x_ = $x / 2;
	};

	if($y < 2)
	{
		$y_ = 2;
	}
	else
	{
		$y_ = $y * 2;
	};

	if(!empty($title))
	{
		$div_title = ' title="'.$title.'"';
	}
	else
	{
		$div_title = '';
	}
	switch($type)
	{
		case 'textarea':
		case 'text':	$style = ' style="width:'.$x_.'em; height:'.$y_.'em;"';
				break;
		default:	$style = '';
	};

	switch($type)
	{
		case 'radio':
		case 'checkbox':if(!defined('IMAGE_'.$type.'_SIZE'))
				{
					$image_dimensions = getimagesize(PRINT_TEMPLATE_DIR.'print_img_'.$type.'.png');
					// define('IMAGE_'.$type.'_SIZE' , ' width="'.$image_dimensions[0].'" height="'.$image_dimensions[1].'"');
					define('IMAGE_'.$type.'_SIZE' , ' width="14" height="14"');
				};
				$output = '<img src="'.PRINT_TEMPLATE_URL.'print_img_'.$type.'.png"'.constant('IMAGE_'.$type.'_SIZE').' alt="'.$title.'" class="input-'.$type.'" />';
				break;

		case 'rank':
		case 'other':
		case 'othercomment':
		case 'text':
		case 'textarea':$output = '<div class="input-'.$type.'"'.$style.$div_title.'>{NOTEMPTY}</div>';
				break;

		default:	$output = '';
	};
	return $output;
};

function star_replace($input)
{
	return preg_replace(
			 '/\*(.*)\*/U'
			,'<strong>\1</strong>'
			,$input
		);
}

$total_questions = 0;
$mapquestionsNumbers=Array();

// =========================================================
// START doin the business:
$pdfoutput = '';
while ($degrow = $degresult->FetchRow())
{
// ---------------------------------------------------
// START doing groups

	$deqquery = "SELECT * FROM ".db_table_name("questions")." WHERE sid=$surveyid AND gid={$degrow['gid']} AND language='{$surveyprintlang}' AND TYPE<>'I' ORDER BY question_order";
	$deqresult = db_execute_assoc($deqquery);
	$deqrows = array(); //Create an empty array in case FetchRow does not return any rows
	while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} // Get table output into array

	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($deqrows, 'CompareGroupThenTitle');

	if ($degrow['description'])
	{
		$group_desc = $degrow['description'];
	}
	else
	{
		$group_desc = '';
	};

	$group = array(
			 'GROUPNAME' => $degrow['group_name']
			,'GROUPDESCRIPTION' => $group_desc
			,'QUESTIONS' => '' // templated formatted content if $question is appended to this at the end of processing each question.
		 );


	if(isset($_POST['printableexport'])){$pdf->titleintopdf($degrow['group_name'],$degrow['description']);}

	$gid = $degrow['gid'];
	//Alternate bgcolor for different groups
	if (!isset($group['ODD_EVEN']) || $group['ODD_EVEN'] == ' g-row-even')
	{
		$group['ODD_EVEN'] = ' g-row-odd';}
	else
	{
		$group['ODD_EVEN'] = ' g-row-even';
	};

	foreach ($deqrows as $deqrow)
	{
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// START doing questions

		//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION

		$printablesurveyoutput = '';
		$explanation = ''; //reset conditions explanation
		$s=0;
		$scenarioquery="SELECT DISTINCT ".db_table_name("conditions").".scenario FROM ".db_table_name("conditions")." WHERE ".db_table_name("conditions").".qid={$deqrow['qid']} ORDER BY scenario";
		$scenarioresult=db_execute_assoc($scenarioquery);

		while ($scenariorow=$scenarioresult->FetchRow())
        {
        		if($s == 0 && $scenarioresult->RecordCount() > 1)
			{	
				$explanation .= '<p class="scenario">'.try_debug(__LINE__)." -------- Scenario {$scenariorow['scenario']} --------</p>\n\n";
			}
        		if($s > 0)
			{
				$explanation .= '<p class="scenario">'.try_debug(__LINE__).' -------- '.$clang->gT("or")." Scenario {$scenariorow['scenario']} --------</p>\n\n";
			}

			$x=0;
			$distinctquery="SELECT DISTINCT cqid, method, ".db_table_name("questions").".title, ".db_table_name("questions").".question FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']} AND language='{$surveyprintlang}' ORDER BY cqid";
			$distinctresult=db_execute_assoc($distinctquery);
			while ($distinctrow=$distinctresult->FetchRow())
			{
				if($x > 0)
				{
					$explanation .= ' <em>'.$clang->gT('and').'</em> ';
				}
				if(trim($distinctrow['method'])=='')
				{
					$distinctrow['method']='==';
				}

				if($distinctrow['method']=='==')
				{
					$explanation .= $clang->gT("Answer was")." ";
				}
				elseif($distinctrow['method']=='!=')
				{
					$explanation .= $clang->gT("Answer was NOT")." ";
				}
				elseif($distinctrow['method']=='<')
				{
					$explanation .= $clang->gT("Answer was less than")." ";
				}
				elseif($distinctrow['method']=='<=')
				{
					$explanation .= $clang->gT("Answer was less than or equal to")." ";
				}
				elseif($distinctrow['method']=='>=')
				{
					$explanation .= $clang->gT("Answer was greater than or equal to")." ";
				}
				elseif($distinctrow['method']=='>')
				{
					$explanation .= $clang->gT("Answer was greater than")." ";
				}
				elseif($distinctrow['method']=='RX')
				{
					$explanation .= $clang->gT("Answer matched (regexp)")." ";
				}
				else
				{
					$explanation .= $clang->gT("Answer was")." ";
				}

				$conquery="SELECT cid, cqid, ".db_table_name("questions").".title,\n"
				."".db_table_name("questions").".question, value, ".db_table_name("questions").".type,\n"
				."".db_table_name("questions").".lid, ".db_table_name("questions").".lid1, cfieldname\n"
				."FROM ".db_table_name("conditions").", ".db_table_name("questions")."\n"
				."WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid\n"
				."AND ".db_table_name("conditions").".cqid={$distinctrow['cqid']}\n"
				."AND ".db_table_name("conditions").".qid={$deqrow['qid']} \n"
				."AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']} \n"
				."AND language='{$surveyprintlang}'";
				$conresult=db_execute_assoc($conquery) or safe_die("$conquery<br />".htmlspecialchars($connect->ErrorMsg()));
				$conditions=array();
				while ($conrow=$conresult->FetchRow())
				{

					$postans="";
					$value=$conrow['value'];
					switch($conrow['type'])
					{
						case "Y":
							switch ($conrow['value'])
							{
								case "Y": $conditions[]=$clang->gT("Yes"); break;
								case "N": $conditions[]=$clang->gT("No"); break;
							}
							break;
						case "G":
							switch($conrow['value'])
							{
								case "M": $conditions[]=$clang->gT("Male"); break;
								case "F": $conditions[]=$clang->gT("Female"); break;
							} // switch
							break;
						case "A":
						case "B":
						case ":":
						case ";":
							$conditions[]=$conrow['value'];
							break;
						case "C":
							switch($conrow['value'])
							{
								case "Y": $conditions[]=$clang->gT("Yes"); break;
								case "U": $conditions[]=$clang->gT("Uncertain"); break;
								case "N": $conditions[]=$clang->gT("No"); break;
							} // switch
							break;
						case "E":
							switch($conrow['value'])
							{
								case "I": $conditions[]=$clang->gT("Increase"); break;
								case "D": $conditions[]=$clang->gT("Decrease"); break;
								case "S": $conditions[]=$clang->gT("Same"); break;
							}
						case "1":
							$labelIndex=preg_match("/^[^#]+#([01]{1})$/",$conrow['cfieldname']);
							if ($labelIndex == 0)
							{ // TIBO
								$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
									. "WHERE lid='{$conrow['lid']}'\n"
									. "AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
								$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
								while($frow=$fresult->FetchRow())
								{
									$postans=$frow['title'];
									$conditions[]=$frow['title'];
								} // while
							}
							elseif ($labelIndex == 1)
							{
								$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
									. "WHERE lid='{$conrow['lid1']}'\n"
									. "AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
								$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
								while($frow=$fresult->FetchRow())
								{
									$postans=$frow['title'];
									$conditions[]=$frow['title'];
								} // while
							}
							break;
						case "L":
						case "!":
						case "O":
						case "M":
						case "P":
						case "R":
							$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
							$ansresult=db_execute_assoc($ansquery);
							while ($ansrow=$ansresult->FetchRow())
							{
								$conditions[]=$ansrow['answer'];
							}
							$conditions = array_unique($conditions);
							break;
						case "F":
						case "H":
						case "W":
						default:
							$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
							$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
							. "WHERE lid='{$conrow['lid']}'\n"
							. "AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
							$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
							while($frow=$fresult->FetchRow())
							{
								$postans=$frow['title'];
								$conditions[]=$frow['title'];
							} // while
							break;
					} // switch
					
					// Now let's complete the answer text with the answer_section
					$answer_section="";
					switch($conrow['type'])
					{
						case "A":
						case "B":
						case "C":
						case "E":
						case "F":
						case "H":
						case "K":
							$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
							$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$surveyprintlang}'";
							//$ansquery="SELECT question FROM ".db_table_name("questions")." WHERE qid='{$conrow['cqid']}' AND language='{$surveyprintlang}'";
							$ansresult=db_execute_assoc($ansquery);
							while ($ansrow=$ansresult->FetchRow())
							{
								$answer_section=" (".$ansrow['answer'].")";
							}
							break;

						case "1": // dual: (Label 1), (Label 2)
							$labelIndex=preg_match("/^[^#]+#([01]{1})$/",$conrow['cfieldname']);
							$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
							$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$surveyprintlang}'";
							//$ansquery="SELECT question FROM ".db_table_name("questions")." WHERE qid='{$conrow['cqid']}' AND language='{$surveyprintlang}'";
							$ansresult=db_execute_assoc($ansquery);

							if ($labelIndex == 0)
							{ 
								while ($ansrow=$ansresult->FetchRow())
								{
									$answer_section=" (".$ansrow['answer']." ".$clang->gT("Label")."1)";
								}
							}
							elseif ($labelIndex == 1)
							{
								while ($ansrow=$ansresult->FetchRow())
								{
									$answer_section=" (".$ansrow['answer']." ".$clang->gT("Label")."2)";
								}
							}
							break;
						case ":":
						case ";": //multi flexi: ( answer [label] )
							$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
							$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$surveyprintlang}'";
							$ansresult=db_execute_assoc($ansquery);
							while ($ansrow=$ansresult->FetchRow())
							{
								$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
									. "WHERE lid='{$conrow['lid']}'\n"
									. "AND code='{$conrow['value']}' AND language='{$surveyprintlang}'";
								$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
								while($frow=$fresult->FetchRow())
								{
									//$conditions[]=$frow['title'];
									$answer_section=" (".$ansrow['answer']."[".$frow['title']."])";
								} // while
							}
							break;
						case "R": // (Rank 1), (Rank 2)... TIBO
							$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
							$rankid=$thiscquestion[0]['aid'];
							$answer_section=" (".$clang->gT("RANK")." $rankid)";
							break;
						default: // nothing to add
							break;
					}
				}

				if (count($conditions) > 1)
				{
					$explanation .=  "'".implode("' ".$clang->gT("or")." '", $conditions)."'";
				}
				elseif (count($conditions) == 1)
				{
					$explanation .= "'".$conditions[0]."'";
				}
				unset($conditions);
				// Following line commented out because answer_section  was lost, but is required for some question types
				//$explanation .= " ".$clang->gT("to question")." '".$mapquestionsNumbers[$distinctrow['cqid']]."' $answer_section ";
				$explanation .= " ".$clang->gT("at question")." '".$mapquestionsNumbers[$distinctrow['cqid']]." [".$distinctrow['title']."]' (".strip_tags($distinctrow['question'])."$answer_section)" ;
				//$distinctrow
				$x++;
			}
			$s++;
		}
		if ($explanation)
		{
			$explanation = "<b>".$clang->gT('Only answer this question if the following conditions are met:')."</b>"
			."<br/> ° ".$explanation;//"[".sprintf($clang->gT("Only answer this question %s"), $explanation)."]";
		}
		else
		{
			$explanation = '';
		}

		++$total_questions;

		$question = array(
					 'QUESTION_NUMBER' => $total_questions	// content of the question code field
					,'QUESTION_CODE' => $deqrow['title']
					,'QUESTION_TEXT' => preg_replace('/(?:<br ?\/?>|<\/(?:p|h[1-6])>)$/is' , '' , $deqrow['question'])	// content of the question field
					,'QUESTION_SCENARIO' => $explanation	// if there are conditions on a question, list the conditions.
					,'QUESTION_MANDATORY' => ''		// translated 'mandatory' identifier
					,'QUESTION_CLASS' => question_class( $deqrow['type'])	// classes to be added to wrapping question div
					,'QUESTION_TYPE_HELP' => ''		// instructions on how to complete the question
					,'QUESTION_MAN_MESSAGE' => ''		// (not sure if this is used) mandatory error
					,'QUESTION_VALID_MESSAGE' => ''		// (not sure if this is used) validation error
					,'QUESTIONHELP' => ''			// content of the question help field.
					,'ANSWER' => ''				// contains formatted HTML answer
				);
		//TIBO map question qid to their q number
		$mapquestionsNumbers[$deqrow['qid']]=$total_questions;
		//END OF GETTING CONDITIONS

		$qid = $deqrow['qid'];
		$fieldname = "$surveyid"."X"."$gid"."X"."$qid";

		if ($deqrow['mandatory'] == 'Y')
		{
			$question['QUESTION_MANDATORY'] = $clang->gT('*');
			$question['QUESTION_CLASS'] .= ' mandatory';
			$pdfoutput .= $clang->gT("*");
		}

		$pdfoutput ='';

		//DIFFERENT TYPES OF DATA FIELD HERE

		
		if(isset($_POST['printableexport'])){$pdf->intopdf($deqrow['title']." ".$deqrow['question']);}

		if ($deqrow['help'])
		{
			$hh = $deqrow['help'];
			$question['QUESTIONHELP'] = $hh;
			
			if(isset($_POST['printableexport'])){$pdf->helptextintopdf($hh);}
		}

		$qidattributes=getQAttributes($deqrow['qid']);
		
		if (isset($qidattributes['page_break']))
        {
            $question['QUESTION_CLASS'] .=' breakbefore ';
        }

		switch($deqrow['type'])
		{
// ==================================================================
			case "5":	//5 POINT CHOICE
					$question['QUESTION_TYPE_HELP'] = $clang->gT('Please choose *only one* of the following:');
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
					$pdfoutput ='';
					$question['ANSWER'] .= "\n\t<ul>\n";
					for ($i=1; $i<=5; $i++)
					{
						$pdfoutput .=" o ".$i." ";
//						$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='$i' readonly='readonly' />$i \n";
						$question['ANSWER'] .="\t\t<li>\n\t\t\t".input_type_image('radio',$i)."\n\t\t\t$i\n\t\t</li>\n";
					}
					if(isset($_POST['printableexport'])){$pdf->intopdf($pdfoutput);}
					$question['ANSWER'] .="\t</ul>\n";

					break;

// ==================================================================
			case "D":  //DATE
					$question['QUESTION_TYPE_HELP'] = $clang->gT('Please enter a date:');
					$question['ANSWER'] .= "\t".input_type_image('text',$question['QUESTION_TYPE_HELP'],30,1);
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please enter a date:")." ___________");}

					break;

// ==================================================================
			case "G":  //GENDER
					$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *only one* of the following:");

					$question['ANSWER'] .= "\n\t<ul>\n";
					$question['ANSWER'] .= "\t\t<li>\n\t\t\t".input_type_image('radio',$clang->gT("Female"))."\n\t\t\t".$clang->gT("Female")."\n\t\t</li>\n";
					$question['ANSWER'] .= "\t\t<li>\n\t\t\t".input_type_image('radio',$clang->gT("Male"))."\n\t\t\t".$clang->gT("Male")."\n\t\t</li>\n";
					$question['ANSWER'] .= "\t</ul>\n";

					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"));}
					if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Female")." | o ".$clang->gT("Male"));}

					break;

// ==================================================================
			case "W": //Flexible List

// ==================================================================
			case "Z": //LIST Flexible drop-down/radio-button list
					if (isset($qidattributes["display_columns"]))
					{
						$dcols=$qidattributes['display_columns'];
					}
					else
					{
						$dcols=1;
					}
					$question['QUESTION_TYPE_HELP'] = "\n\t<p class=\"help-first\">".$clang->gT("Please choose *only one* of the following:")."</p>\n";
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
					$deaquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} AND language='{$surveyprintlang}' ORDER BY sortorder";
					$dearesult = db_execute_assoc($deaquery) or safe_die("ERROR: $deaquery<br />\n".$connect->ErrorMsg());
					$deacount=$dearesult->RecordCount();
					if ($deqrow['other'] == "Y")
					{
						$deacount++;
					}
					$wrapper = setup_columns($dcols, $deacount);

					$question['ANSWER'] = $wrapper['whole-start'];

					$rowcounter = 0;
					$colcounter = 1;

					while ($dearow = $dearesult->FetchRow())
					{
						$question['ANSWER'] .= $wrapper['item-start'].input_type_image('radio' , $dearow['code']).' '.$dearow['title'].$wrapper['item-end'];
						if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['title']);}

						++$rowcounter;
						if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
						{
							if($colcounter == $wrapper['cols'] - 1)
							{
								$question['ANSWER'] .= $wrapper['col-devide-last'];
							}
							else
							{
								$question['ANSWER'] .= $wrapper['col-devide'];
							};
							$rowcounter = 0;
							++$colcounter;
						};
					}
					if ($deqrow['other'] == "Y")
					{
						$qAttrib = getQAttributes($deqrow['qid']);
						if(!isset($qAttrib["other_replace_text"]))
						{$qAttrib["other_replace_text"]="Other";}
					
						$question['ANSWER'] .= $wrapper['item-start-other'].input_type_image('radio',$clang->gT($qAttrib["other_replace_text"])).' '.$clang->gT($qAttrib["other_replace_text"])."\n\t\t\t".input_type_image('other','')."\n".$wrapper['item-end'];
						if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT($qAttrib["other_replace_text"]).": ________");}
					}
					$question['ANSWER'] .= $wrapper['whole-end'];
					//Let's break the presentation into columns.
					break;

// ==================================================================
			case 'L': //LIST drop-down/radio-button list

// ==================================================================
			case '!': //List - dropdown
                    if (isset($qidattributes["display_columns"]))
					{
						$dcols=$qidattributes['display_columns'];
					}
					else
					{
						$dcols=0;
					}

                    if (isset($qidattributes["category_separator"]))
					{
						$optCategorySeparator = $qidattributes['category_separator'];
					}
					else
					{
						unset($optCategorySeparator);
					}

					$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *only one* of the following:");

					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"));}
					$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					$deacount=$dearesult->RecordCount();
					if ($deqrow['other'] == "Y") {$deacount++;}

					$wrapper = setup_columns($dcols, $deacount);

					$question['ANSWER'] = $wrapper['whole-start'];

					$rowcounter = 0;
					$colcounter = 1;

					while ($dearow = $dearesult->FetchRow())
					{
						if (isset($optCategorySeparator))
						{
							list ($category, $answer) = explode($optCategorySeparator,$dearow['answer']);
							if ($category != '')
							{
								$dearow['answer'] = "($category) $answer";
							}
							else
							{
								$dearow['answer'] = $answer;
							}
						}
	
						$question['ANSWER'] .= "\t".$wrapper['item-start']."\t\t".input_type_image('radio' , $dearow['answer'])."\n\t\t\t".$dearow['answer']."\n".$wrapper['item-end'];
						if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$dearow['answer']);}

						++$rowcounter;
						if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
						{
							if($colcounter == $wrapper['cols'] - 1)
							{
								$question['ANSWER'] .= $wrapper['col-devide-last'];
							}
							else
							{
								$question['ANSWER']  .= $wrapper['col-devide'];
							};
							$rowcounter = 0;
							++$colcounter;
						}
					}
					if ($deqrow['other'] == 'Y')
					{
						$qAttrib = getQAttributes($deqrow['qid']);
						if(!isset($qAttrib["other_replace_text"]))
						{$qAttrib["other_replace_text"]="Other";}
//					$printablesurveyoutput .="\t".$wrapper['item-start']."\t\t".input_type_image('radio' , $clang->gT("Other"))."\n\t\t\t".$clang->gT("Other")."\n\t\t\t<input type='text' size='30' readonly='readonly' />\n".$wrapper['item-end'];
						$question['ANSWER']  .= $wrapper['item-start-other'].input_type_image('radio',$clang->gT($qAttrib["other_replace_text"])).' '.$clang->gT($qAttrib["other_replace_text"])."\n\t\t\t".input_type_image('other')."\n".$wrapper['item-end'];
					if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT($qAttrib["other_replace_text"]).": ________");}
				}
				$question['ANSWER'] .= $wrapper['whole-end'];
				//Let's break the presentation into columns.
				break;

// ==================================================================
			case "O":  //LIST WITH COMMENT
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *only one* of the following:");
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
				$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer ";
				$dearesult = db_execute_assoc($deaquery);
				$question['ANSWER'] = "\t<ul>\n";
				while ($dearow = $dearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<li>\n\t\t\t".input_type_image('radio',$dearow['answer'])."\n\t\t\t".$dearow['answer']."\n\t\t</li>\n";
					if(isset($_POST['printableexport'])){$pdf->intopdf($dearow['answer']);}
				}
				$question['ANSWER'] .= "\t</ul>\n";

				$question['ANSWER'] .= "\t<p class=\"comment\">\n\t\t".$clang->gT("Make a comment on your choice here:")."\n";
				if(isset($_POST['printableexport'])){$pdf->intopdf("Make a comment on your choice here:");}
				$question['ANSWER'] .= "\t\t".input_type_image('textarea',$clang->gT("Make a comment on your choice here:"),50,8)."\n\t</p>\n";

				for($i=0;$i<9;$i++)
				{
					if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
				}
				break;

// ==================================================================
			case "R":  //RANKING Type Question
				$reaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$rearesult = db_execute_assoc($reaquery) or safe_die ("Couldn't get ranked answers<br />".$connect->ErrorMsg());
				$reacount = $rearesult->RecordCount();
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please number each box in order of preference from 1 to")." $reacount";
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please number each box in order of preference from 1 to ").$reacount,"U");}
				$question['ANSWER'] = "\n<ul>\n";
				while ($rearow = $rearesult->FetchRow())
				{
					$question['ANSWER'] .="\t<li>\n\t".input_type_image('rank','',4,1)."\n\t\t".$rearow['answer']."\n\t</li>\n";
					if(isset($_POST['printableexport'])){$pdf->intopdf("__ ".$rearow['answer']);}
				};
				$question['ANSWER'] .= "\n</ul>\n";
				break;

// ==================================================================
			case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
							
                if (isset($qidattributes["display_columns"]))
				{
					$dcols=$qidattributes['display_columns'];
				}
				else
				{
					$dcols=0;
				}
                if (!isset($qidattributes["max_answers"]))
				{
					$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *all* that apply:");
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *all* that apply:"),"U");}
				}
				else
				{
                    $maxansw=$qidattributes["max_answers"];
					$question['QUESTION_TYPE_HELP'] = $clang->gT('Please choose *at most* ').' <span class="num">'.$maxansw.'</span> '.$clang->gT('answers:');
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *at most* ").$maxansw.$clang->gT("answers:"),"U");}
				}
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$meacount = $mearesult->RecordCount();
				if ($deqrow['other'] == 'Y') {$meacount++;}
				
				$wrapper = setup_columns($dcols, $meacount);
				$question['ANSWER'] = $wrapper['whole-start'];

				$rowcounter = 0;
				$colcounter = 1;
				
				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= $wrapper['item-start'].input_type_image('checkbox',$mearow['answer'])."\n\t\t".$mearow['answer'].$wrapper['item-end'];
					if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$mearow['answer']);}
//						$upto++;
					
					++$rowcounter;
					if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
					{
						if($colcounter == $wrapper['cols'] - 1)
						{
							$question['ANSWER'] .= $wrapper['col-devide-last'];
						}
						else
						{
							$question['ANSWER'] .= $wrapper['col-devide'];
						};
						$rowcounter = 0;
						++$colcounter;
					};
				}
				if ($deqrow['other'] == "Y")
				{
					if(!isset($qidattributes["other_replace_text"]))
					{
                        $qidattributes["other_replace_text"]="Other";
                    }
					$question['ANSWER'] .= $wrapper['item-start-other']."<div class=\"other-replacetext\">".$clang->gT($qidattributes["other_replace_text"]).":</div>\n\t\t".input_type_image('other').$wrapper['item-end'];
					if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT($qidattributes["other_replace_text"]).": ________");}
				}
				$question['ANSWER'] .= $wrapper['whole-end'];
//				};
				break;
				
/*
// ==================================================================
			case "I": //Language Switch  in a printable survey does not make sense
				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose *only one* of the following:")."</u><br />\n";
				$answerlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
				$answerlangs [] = GetBaseLanguageFromSurveyID($surveyid);
				
				foreach ($answerlangs as $ansrow)
				{
					$printablesurveyoutput .="\t\t\t<input type='checkbox' name='$fieldname' value='{$ansrow}' />".getLanguageNameFromCode($ansrow, true)."<br />\n";
				}
				break;
*/


// ==================================================================
			case "P":  //MULTIPLE OPTIONS WITH COMMENTS
				if (!isset($qidattributes['max_answers']))
				{
					$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose all that apply and provide a comment:");
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose all that apply and provide a comment:"),"U");}
				}
				else
				{
                    $maxansw=$qidattributes['max_answers'];
					$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *at most* ").'<span class="num">'.$maxansw.'</span> '.$clang->gT("answers and provide a comment:");
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *at most* ").$maxansw.$clang->gT("answers and provide a comment:"),"U");}
				}
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
//				$printablesurveyoutput .="\t\t\t<u>".$clang->gT("Please choose all that apply and provide a comment:")."</u><br />\n";
				$pdfoutput=array();
				$j=0;
				$longest_string = 0;
				while ($mearow = $mearesult->FetchRow())
				{
					$longest_string = longest_string($mearow['answer'] , $longest_string );
					$question['ANSWER'] .= "\t<li>\n\t\t".input_type_image('checkbox',$mearow['answer']).$mearow['answer']."\n\t\t".input_type_image('text','comment box',60)."\n\t</li>\n";
					$pdfoutput[$j]=array(" o ".$mearow['code']," __________");
					$j++;
				}
				if ($deqrow['other'] == "Y")
				{ 
					$question['ANSWER'] .= "\t<li class=\"other\">\n\t\t<div class=\"other-replacetext\">".input_type_image('other','',1)."</div>".input_type_image('othercomment','comment box',50)."\n\t</li>\n";
					// lemeur: PDFOUTPUT HAS NOT BEEN IMPLEMENTED for these fields
					// not sure who did implement this.
					$pdfoutput[$j][0]=array(" o "."Other"," __________");
					$pdfoutput[$j][1]=array(" o "."OtherComment"," __________");
					$j++;
				}
				
				$question['ANSWER'] = "\n<ul class=\"".label_class_width($longest_string , 'checkbox')."\">\n".$question['ANSWER']."</ul>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;


// ==================================================================
			case "Q":  //MULTIPLE SHORT TEXT
				$width=60;

// ==================================================================
			case "K":  //MULTIPLE NUMERICAL
				$width=(isset($width))?$width:16;
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer(s) here:"),"U");}

				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write your answer(s) here:");

				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$longest_string = 0;
				while ($mearow = $mearesult->FetchRow())
				{
					$longest_string = longest_string($mearow['answer'] , $longest_string );
					$question['ANSWER'] .=  "\t<li>\n\t\t".$mearow['answer']."\n\t\t".input_type_image('text',$mearow['answer'],$width)."\n\t</li>\n";
					if(isset($_POST['printableexport'])){$pdf->intopdf($mearow['answer'].": ____________________");}
				}
				$question['ANSWER'] =  "\n<ul class=\"".label_class_width($longest_string , 'numeric')."\">\n".$question['ANSWER']."</ul>\n";
				break;


// ==================================================================
			case "S":  //SHORT TEXT
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write your answer here:");
				$question['ANSWER'] = input_type_image('text',$question['QUESTION_TYPE_HELP'], 50);
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
				if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
				break;


// ==================================================================
			case "T":  //LONG TEXT
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write your answer here:");
				$question['ANSWER'] = input_type_image('textarea',$question['QUESTION_TYPE_HELP'], '100%' , 8);

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
				for($i=0;$i<9;$i++)
				{
					if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
				}
				break;


// ==================================================================
			case "U":  //HUGE TEXT
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write your answer here:");
				$question['ANSWER'] = input_type_image('textarea',$question['QUESTION_TYPE_HELP'], '100%' , 30);

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
				for($i=0;$i<20;$i++)
				{
					if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
				}
				break;


// ==================================================================
			case "N":  //NUMERICAL
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write your answer here:");
				$question['ANSWER'] = input_type_image('text',$question['QUESTION_TYPE_HELP'],20);

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write your answer here:"),"U");}
				if(isset($_POST['printableexport'])){$pdf->intopdf("____________________");}
				
				break;

// ==================================================================
			case "Y":  //YES/NO
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose *only one* of the following:");
				$question['ANSWER'] = "\n<ul>\n\t<li>\n\t\t".input_type_image('radio',$clang->gT('Yes'))."\n\t\t".$clang->gT('Yes')."\n\t</li>\n";
				$question['ANSWER'] .= "\n\t<li>\n\t\t".input_type_image('radio',$clang->gT('No'))."\n\t\t".$clang->gT('No')."\n\t</li>\n</ul>\n";

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose *only one* of the following:"),"U");}
				if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("Yes"));}
				if(isset($_POST['printableexport'])){$pdf->intopdf(" o ".$clang->gT("No"));}
				break;


// ==================================================================
			case "A":  //ARRAY (5 POINT CHOICE)
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}'  ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				$question['ANSWER'] = '
<table>
	<thead>
		<tr>
			<td>&nbsp;</td>
			<th>1</th>
			<th>2</th>
			<th>3</th>
			<th>4</th>
			<th>5</th>
		</tr>
	</thead>
	<tbody>
';

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$pdfoutput = array();
				$j=0;
				$rowclass = 'array1';
				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
					$rowclass = alternation($rowclass,'row');
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";

					$pdfoutput[$j][0]=$answertext;
					for ($i=1; $i<=5; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$i)."</td>\n";
						$pdfoutput[$j][$i]=" o ".$i;
					}

					$answertext=$mearow['answer'];
					if (strpos($answertext,'|'))
					{
						$answertext=substr($answertext,strpos($answertext,'|')+1);
						$question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</td>\n";
					}
					$question['ANSWER'] .= "\t\t</tr>\n";
					$j++;
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case "B":  //ARRAY (10 POINT CHOICE)
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				
				$mearesult = db_execute_assoc($meaquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				$question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
				for ($i=1; $i<=10; $i++)
				{
					$question['ANSWER'] .= "\t\t\t<th>$i</th>\n";
				}
				$question['ANSWER'] .= "\t</thead>\n\n\t<tbody>\n";
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$pdfoutput=array();
				$j=0;
				$rowclass = 'array1';
				while ($mearow = $mearesult->FetchRow())
				{

					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n\t\t\t<th class=\"answertext\">{$mearow['answer']}</th>\n";
					$rowclass = alternation($rowclass,'row');

					$pdfoutput[$j][0]=$mearow['answer'];
					for ($i=1; $i<=10; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$i)."</td>\n";
						$pdfoutput[$j][$i]=" o ".$i;
					};
					$question['ANSWER'] .= "\t\t</tr>\n";
					$j++;
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case "C":  //ARRAY (YES/UNCERTAIN/NO)
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				$question['ANSWER'] = '
<table>
	<thead>
		<tr>
			<td>&nbsp;</td>
			<th>'.$clang->gT("Yes").'</th>
			<th>'.$clang->gT("Uncertain").'</th>
			<th>'.$clang->gT("No").'</th>
		</tr>
	</thead>
	<tbody>
';
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$pdfoutput = array();
				$j=0;

				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['answer']}</th>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("Yes"))."</td>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("Uncertain"))."</td>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("No"))."</td>\n";
					$question['ANSWER'] .= "\t\t</tr>\n";
					
					$pdfoutput[$j]=array($mearow['answer']," o ".$clang->gT("Yes")," o ".$clang->gT("Uncertain")," o ".$clang->gT("No"));
					$j++;
					$rowclass = alternation($rowclass,'row');
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

			case "E":  //ARRAY (Increase/Same/Decrease)
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$surveyprintlang}'  ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				$question['ANSWER'] = '
<table>
	<thead>
		<tr>
			<td>&nbsp;</td>
			<th>'.$clang->gT("Increase").'</th>
			<th>'.$clang->gT("Same").'</th>
			<th>'.$clang->gT("Decrease").'</th>
		</tr>
	</thead>
	<tbody>
';
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$pdfoutput = array();
				$j=0;
				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['answer']}</th>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("Increase"))."</td>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("Same"))."</td>\n";
					$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio',$clang->gT("Decrease"))."</td>\n";
					$question['ANSWER'] .= "\t\t</tr>\n";
					$pdfoutput[$j]=array($mearow['answer'].":"," o ".$clang->gT("Increase")," o ".$clang->gT("Same")," o ".$clang->gT("Decrease"));
					$j++;
					$rowclass = alternation($rowclass,'row');
			}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case ":": //ARRAY (Multi Flexible) (Numbers)
				$headstyle="style='padding-left: 20px; padding-right: 7px'";
				if (isset($qidattributes['multiflexible_max'])) {
					$maxvalue=$qidattributes['multiflexible_max'];
				}
				else
				{
					$maxvalue=10;
				}
                if (isset($qidattributes['multiflexible_min'])) {
					$minvalue=$qidattributes['multiflexible_min'];
				} else {
					$minvalue=1;
				}
                if (isset($qidattributes['multiflexible_step'])) {
					$stepvalue=$qidattributes['multiflexible_step'];
				}
				else
				{
					$stepvalue=1;
				}
                if (isset($qidattributes['multiflexible_checkbox'])) {
					$checkboxlayout=true;
				} else {
					$checkboxlayout=false;
				}
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				if ($checkboxlayout === false)
				{
					if ($stepvalue > 1)
					{
						$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write a multiple of $stepvalue between $minvalue and $maxvalue for each item:");
						if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write a multiple of $stepvalue between $minvalue and $maxvalue for each item:"),"U");}
					}
					else {
						$question['QUESTION_TYPE_HELP'] = $clang->gT("Please write a number between $minvalue and $maxvalue for each item:");
						if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please write a number between $minvalue and $maxvalue for each item:"),"U");}
					}
				}
				else
				{
					$question['QUESTION_TYPE_HELP'] = $clang->gT("Check any that apply").":";
					if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Check any that apply"),"U");}
				}

				$question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=0;
				$pdfoutput = array();
				$pdfoutput[0][0]=' ';
				while ($frow = $fresult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t\t<th>{$frow['title']}</th>\n";
					$i++;
					$pdfoutput[0][$i]=$frow['title'];
				}
				$question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
				$a=1; //Counter for pdfoutput
				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t<tr class=\"$rowclass\">\n";
					$rowclass = alternation($rowclass,'row');

					$answertext=$mearow['answer'];
					if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
					$question['ANSWER'] .= "\t\t\t\t\t<th class=\"answertext\">$answertext</th>\n";
					//$printablesurveyoutput .="\t\t\t\t\t<td>";
					$pdfoutput[$a][0]=$answertext;
					for ($i=1; $i<=$fcount; $i++)
					{

						$question['ANSWER'] .= "\t\t\t<td>\n";
						if ($checkboxlayout === false)
						{
							$question['ANSWER'] .= "\t\t\t\t".input_type_image('text','',4)."\n";
							$pdfoutput[$a][$i]="__";
						}
						else
						{
							$question['ANSWER'] .= "\t\t\t\t".input_type_image('checkbox')."\n";
							$pdfoutput[$a][$i]="o";
						}
						$question['ANSWER'] .= "\t\t\t</td>\n";
					}
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|'))
					{
						$answertext=substr($answertext,strpos($answertext,'|')+1);
						$question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
						//$pdfoutput[$a][$i]=$answertext;
					}
					$question['ANSWER'] .= "\t\t</tr>\n";
					$a++;
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case ";": //ARRAY (Multi Flexible) (text)
				$headstyle="style='padding-left: 20px; padding-right: 7px'";
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);

				$question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=0;
				$pdfoutput=array();
				$pdfoutput[0][0]='';
				while ($frow = $fresult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t\t<th>{$frow['title']}</th>\n";
					$i++;
					$pdfoutput[0][$i]=$frow['title'];
				}
				$question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n<tbody>\n";
				$a=1;
				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
				$rowclass = alternation($rowclass,'row');
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";
					$pdfoutput[$a][0]=$answertext;
					//$printablesurveyoutput .="\t\t\t\t\t<td>";
					for ($i=1; $i<=$fcount; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>\n";
						$question['ANSWER'] .= "\t\t\t\t".input_type_image('text','',23)."\n";
						$question['ANSWER'] .= "\t\t\t</td>\n";
						$pdfoutput[$a][$i]="_____________";
					}
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|'))
					{
						$answertext=substr($answertext,strpos($answertext,'|')+1);
						$question['ANSWER'] .= "\t\t\t\t<th class=\"answertextright\">$answertext</th>\n";
					}
					$question['ANSWER'] .= "\t\t</tr>\n";
					$a++;
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case "F": //ARRAY (Flexible Labels)

				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);

				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=1;
				$pdfoutput = array();
				$pdfoutput[0][0]='';
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$column_headings = array();
				while ($frow = $fresult->FetchRow())
				{
					$column_headings[] = $frow['title'];
				}
				$col_width = round(80 / count($column_headings));
				
				$question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n";
				$question['ANSWER'] .= "\t\t\t<td>&nbsp;</td>\n";
				foreach($column_headings as $heading)
					$question['ANSWER'] .= "\t\t\t<th style=\"width:$col_width%;\">$heading</th>\n";
					$pdfoutput[0][$i] = $heading;
					$i++;
				$question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
				$counter = 1;
				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
				$rowclass = alternation($rowclass,'row');
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";
					//$printablesurveyoutput .="\t\t\t\t\t<td>";
					$pdfoutput[$counter][0]=$answertext;
					for ($i=1; $i<=$fcount; $i++)
					{

						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio')."</td>\n";
						$pdfoutput[$counter][$i] = "o";

					}
					$counter++;

					$answertext=$mearow['answer'];
					if (strpos($answertext,'|'))
					{
						$answertext=substr($answertext,strpos($answertext,'|')+1);
						$question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";

					}
					$question['ANSWER'] .= "\t\t</tr>\n";
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case "1": //ARRAY (Flexible Labels) multi scale
				if (isset($qidattributes['dualscale_headerA']))
				{
					$leftheader= $qidattributes['dualscale_headerA'];
				}
				else
				{
					$leftheader ='';
				}
                if (isset($qidattributes['dualscale_headerB']))
				{
					$rightheader= $qidattributes['dualscale_headerB'];
				}
				else
				{
					$rightheader ='';
				}

				$headstyle = 'style="padding-left: 20px; padding-right: 7px"';
				$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$mearesult = db_execute_assoc($meaquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");

				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$question['ANSWER'] .= "\n<table>\n\t<thead>\n";

				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$l1=0;
				$printablesurveyoutput2 = "\t\t\t<td>&nbsp;</td>\n";
				$myheader2 = '';
				$pdfoutput = array();
				$pdfoutput[0][0]='';
				while ($frow = $fresult->FetchRow())
				{
					$printablesurveyoutput2 .="\t\t\t<th>{$frow['title']}</th>\n";
					$myheader2 .= "<td></td>";
					$pdfoutput[0][$l1+1]=$frow['title'];
					$l1++;
				}
				// second scale
				$printablesurveyoutput2 .="\t\t\t<td>&nbsp;</td>\n";
				$fquery1 = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid1']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$fresult1 = db_execute_assoc($fquery1);
				$fcount1 = $fresult1->RecordCount();
				$fwidth = "120";
				$l2=0;
				while ($frow1 = $fresult1->FetchRow())
				{
					$printablesurveyoutput2 .="\t\t\t<th>{$frow1['title']}</th>\n";
					$pdfoutput[1][$l2]=$frow['title'];
					$l2++;
				}
				// build header if needed
				if ($leftheader != '' || $rightheader !='')
				{
					$myheader = "\t\t\t<td>&nbsp;</td>";
					$myheader .= "\t\t\t<th colspan=\"".$l1."\">$leftheader</th>\n";

					if ($rightheader !='')
					{
						// $myheader .= "\t\t\t\t\t" .$myheader2;
						$myheader .= "\t\t\t<td>&nbsp;</td>";
						$myheader .= "\t\t\t<th colspan=\"".$l2."\">$rightheader</td>\n";
					}

					$myheader .= "\t\t\t\t</tr>\n";
				}
				else
				{
					$myheader = '';
				}
				$question['ANSWER'] .= $myheader . "\t\t</tr>\n\n\t\t<tr>\n";
				$question['ANSWER'] .= $printablesurveyoutput2;
				$question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
				
				$rowclass = 'array1';

				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
					$rowclass = alternation($rowclass,'row');
					$answertext=$mearow['answer'];
					if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">$answertext</th>\n";
					for ($i=1; $i<=$fcount; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio')."</td>\n";
					}
					$question['ANSWER'] .= "\t\t\t<td>&nbsp;</td>\n";
					for ($i=1; $i<=$fcount1; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio')."</td>\n";
					}

					$answertext=$mearow['answer'];
					if (strpos($answertext,'|'))
					{
						$answertext=substr($answertext,strpos($answertext,'|')+1);
						$question['ANSWER'] .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
					}
					$question['ANSWER'] .= "\t\t</tr>\n";
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";
				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;

// ==================================================================
			case "H": //ARRAY (Flexible Labels) by Column
				//$headstyle="style='border-left-style: solid; border-left-width: 1px; border-left-color: #AAAAAA'";
				$headstyle="style='padding-left: 20px; padding-right: 7px'";
				$fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']}  AND language='{$surveyprintlang}' ORDER BY sortorder, answer";
				$fresult = db_execute_assoc($fquery);
				$question['QUESTION_TYPE_HELP'] = $clang->gT("Please choose the appropriate response for each item:");
				if(isset($_POST['printableexport'])){$pdf->intopdf($clang->gT("Please choose the appropriate response for each item:"),"U");}
				$question['ANSWER'] .= "\n<table>\n\t<thead>\n\t\t<tr>\n\t\t\t<td>&nbsp;</td>\n";
				$meaquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$deqrow['lid']}'  AND language='{$surveyprintlang}' ORDER BY sortorder, code";
				$mearesult = db_execute_assoc($meaquery);
				$fcount = $fresult->RecordCount();
				$fwidth = "120";
				$i=0;
				$pdfoutput = array();
				$pdfoutput[0][0]='';
				while ($frow = $fresult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t\t<th>{$frow['answer']}</th>\n";
					$i++;
					$pdfoutput[0][$i]=$frow['answer'];
				}
				$question['ANSWER'] .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
				$a=1;
				$rowclass = 'array1';

				
				while ($mearow = $mearesult->FetchRow())
				{
					$question['ANSWER'] .= "\t\t<tr class=\"$rowclass\">\n";
					$rowclass = alternation($rowclass,'row');
					$question['ANSWER'] .= "\t\t\t<th class=\"answertext\">{$mearow['title']}</th>\n";
					//$printablesurveyoutput .="\t\t\t\t\t<td>";
					$pdfoutput[$a][0]=$mearow['title'];
					for ($i=1; $i<=$fcount; $i++)
					{
						$question['ANSWER'] .= "\t\t\t<td>".input_type_image('radio')."</td>\n";
						$pdfoutput[$a][$i]="o";
					}
					//$printablesurveyoutput .="\t\t\t\t\t</tr></table></td>\n";
					$question['ANSWER'] .= "\t\t</tr>\n";
					$a++;
				}
				$question['ANSWER'] .= "\t</tbody>\n</table>\n";

				if(isset($_POST['printableexport'])){$pdf->tableintopdf($pdfoutput);}
				break;
// === END SWITCH ===================================================
		}
		if(isset($_POST['printableexport'])){$pdf->ln(5);}

		$question['QUESTION_TYPE_HELP'] = star_replace($question['QUESTION_TYPE_HELP']);
		$group['QUESTIONS'] .= populate_template( 'question' , $question);
	
	}
	$survey_output['GROUPS'] .= populate_template( 'group' , $group );
}

$survey_output['THEREAREXQUESTIONS'] =  str_replace( '{NUMBEROFQUESTIONS}' , $total_questions , $clang->gT('There are {NUMBEROFQUESTIONS} questions in this survey'));

// START recursive tag stripping.

$server_is_newer = version_compare(PHP_VERSION , '5.1.0' , '>'); // PHP 5.1.0 introduced the count peramater for preg_replace() and thus allows this procedure to run with only one regular expression. Previous version of PHP need two regular expressions to do the same thing and thus will run a bit slower.
$rounds = 0;
while($rounds < 1)
{
	$replace_count = 0;
	if($server_is_newer) // Server version of PHP is at least 5.1.0 or newer
	{
		$survey_output['GROUPS'] = preg_replace(
							 array(
								 '/<td>(?:&nbsp;|&#160;| )?<\/td>/isU'
								,'/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
							 )
							,array(
								 '[[EMPTY-TABLE-CELL]]'
								,''
							 )
							,$survey_output['GROUPS']
							,-1
							,$replace_count
						);
	}
	else // Server version of PHP is older than 5.1.0
	{
		$survey_output['GROUPS'] = preg_replace(
							 array(
								 '/<td>(?:&nbsp;|&#160;| )?<\/td>/isU'
								,'/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
							 )
							,array(
								 '[[EMPTY-TABLE-CELL]]'
								,''
							 )
							,$survey_output['GROUPS']
						);
		$replace_count = preg_match(
						 '/<([^ >]+)[^>]*>(?:&nbsp;|&#160;|\r\n|\n\r|\n|\r|\t| )*<\/\1>/isU'
						, $survey_output['GROUPS']
					);
	};

	if($replace_count == 0)
	{
		++$rounds;
		$survey_output['GROUPS'] = preg_replace(
					 array(
						 '/\[\[EMPTY-TABLE-CELL\]\]/'
						,'/\n(?:\t*\n)+/'
					 )
					,array(
						 '<td>&nbsp;</td>'
						,"\n"
					 )
					,$survey_output['GROUPS']
				);

	};
};

$survey_output['GROUPS'] = preg_replace( '/(<div[^>]*>){NOTEMPTY}(<\/div>)/' , '\1&nbsp;\2' , $survey_output['GROUPS']);

// END recursive empty tag stripping.

if(isset($_POST['printableexport']))
{
    if ($surveystartdate!='')  
	{
    		if(isset($_POST['printableexport'])){$pdf->intopdf(sprintf($clang->gT("Please submit by %s"), $surveyexpirydate));}
	};
	if(!empty($surveyfaxto) && $surveyfaxto != '000-00000000') //If no fax number exists, don't display faxing information!
	{
		if(isset($_POST['printableexport'])){$pdf->intopdf(sprintf($clang->gT("Please fax your completed survey to: %s"),$surveyfaxto),'B');}
	};
	$pdf->titleintopdf($clang->gT("Submit Your Survey."),$clang->gT("Thank you for completing this survey."));
	$pdf->write_out($clang->gT($surveyname)." ".$surveyid.".pdf");
}

echo populate_template( 'survey' , $survey_output ); 

exit;
?>
