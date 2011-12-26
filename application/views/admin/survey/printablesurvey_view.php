<?
$welcome=$_POST['welcome'];
$surveydesc=$_POST['surveydesc'];

echo $surveydesc."<br />";
echo $welcome."<br /><br />";

$numques=$_POST['numques']; //displays # of questions
echo $numques;

?>

<?
	echo '<link rel="stylesheet" type="text/css" href="/yii8/templates/default/print_template.css" />';
	$survey_output=$_POST['survey_output'];
	if (isset($_POST['qidattributes'])){
	$qidattributes=$_POST['qidattributes'];
	}
	while (list ($key, $val) = each ($survey_output)) {
	
	
	if($key=="GROUPS"){
	
	echo "$val<br>";
	
	}
	} 
	if(!empty($qidattributes['max_answers'])) {
?>
			<br /><p class='extrahelp'>
<?
sprintf($clang->gT("Please choose no more than %d items"),$qidattributes['max_answers']);
?>
</p><br />
<?
		}
	if(!empty($qidattributes['min_answers'])) {
?>
			<br /><p class='extrahelp'>
<?
			sprintf($clang->gT("Please choose at least %d items"), $qidattributes['min_answers']);
?>
			</p><br />
<?
		}
?>
<?
		if(isset($_POST['type']) && isset($_POST['type']) && isset($_POST['div_title'])){
		
		$type=$_POST['type'];
		$style=$_POST['style'];
		$div_title=$_POST['div_title'];
	    switch($type)
	    {
	        case 'radio':
	        case 'checkbox':if(!defined('IMAGE_'.$type.'_SIZE'))
	        {
				
	            $image_dimensions = getimagesize(PRINT_TEMPLATE_DIR.'print_img_'.$type.'.png');
	            // define('IMAGE_'.$type.'_SIZE' , ' width="'.$image_dimensions[0].'" height="'.$image_dimensions[1].'"');
	            define('IMAGE_'.$type.'_SIZE' , ' width="14" height="14"');
	        }
	        $output = '<img src="'.PRINT_TEMPLATE_URL.'print_img_'.$type.'.png"'.constant('IMAGE_'.$type.'_SIZE').' alt="'.htmlspecialchars($title).'" class="input-'.$type.'" />';
	        break;

	        case 'rank':
	        case 'other':
	        case 'othercomment':
	        case 'text':
	        case 'textarea':$output = '<div class="input-'.$type.'"'.$style.$div_title.'>{NOTEMPTY}</div>';

	        break;

	        default:	$output = '';
	    }
	    return $output;
		}
		
?>

<?
		if(isset($_POST['qidattributes']) && isset($_POST['surveyprintlang']) && isset($_POST['surveyid'])){
		$qidattributes=$_POST['qidattributes'];
		$surveyprintlang=$_POST['surveyprintlang'];
		$surveyid=$_POST['surveyid'];
	    if(!empty($qidattributes['array_filter']))
	    {
	        $newquery="SELECT question FROM {{questions}} WHERE title='{$qidattributes['array_filter']}' AND language='{$surveyprintlang}' AND sid = '$surveyid'";
	        $newresult=Yii::app()->db->createCommand($newquery)->query();
	        $newquestiontext=$newresult->read();
			?>
	        <br /><p class='extrahelp'>
			    <?sprintf($clang->gT("Only answer this question for the items you selected in question %d ('%s')"),$qidattributes['array_filter'], br2nl($newquestiontext['question']));?>
			</p><br />
			<?
	    }
	    if(!empty($qidattributes['array_filter_exclude']))
	    {
	        $newquery="SELECT question FROM {{questions}} WHERE title='{$qidattributes['array_filter_exclude']}' AND language='{$surveyprintlang}' AND sid = '$surveyid'";
	        $newresult=Yii::app()->db->createCommand($newquery)->query();
	        $newquestiontext=$newresult->read();
			?>
	        <br /><p class='extrahelp'>
			    <?sprintf($clang->gT("Only answer this question for the items you did not select in question %d ('%s')"),$qidattributes['array_filter_exclude'], br2nl($newquestiontext['question'])); ?>
			</p><br />
			<?
	    }
	    
		}
?>

<?

function _input_type_image( $type , $title = '' , $x = 40 , $y = 1 , $line = '' )
	{
	    global $rooturl, $rootdir;

	    if($type == 'other' or $type == 'othercomment')
	    {
	        $x = 1;
	    }
	    $tail = substr($x , -1 , 1);
	    switch($tail)
	    {
	        case '%':
	        case 'm':
	        case 'x':	$x_ = $x;
	        break;
	        default:	$x_ = $x / 2;
	    }

	    if($y < 2)
	    {
	        $y_ = 2;
	    }
	    else
	    {
	        $y_ = $y * 2;
	    }

	    if(!empty($title))
	    {
	        $div_title = ' title="'.htmlspecialchars($title).'"';
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
	    }

	$_POST['type']=$type;
	$_POST['style']=$style;
	$_POST['div_title']=$div_title;
	}

?>

<?
echo $survey_output['END']."<br />";
echo $survey_output['SUBMIT_BY']."<br /><br />";
echo $survey_output['SUBMIT_TEXT'];
echo "<br />";
echo $survey_output['THANKS'];

?>
